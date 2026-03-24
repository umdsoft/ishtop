<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyMatchingWorkersJob;
use App\Models\Category;
use App\Models\Vacancy;
use App\Services\MatchingService;
use App\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VacancyController extends Controller
{
    public function __construct(
        private MatchingService $matchingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Vacancy::with([
                'employer:id,company_name',
                'categoryRelation:id,name_uz,name_ru,emoji',
            ])
            ->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_type')) {
            $query->ofWorkType($request->work_type);
        }

        if ($request->filled('is_top')) {
            $query->where('is_top', $request->boolean('is_top'));
        }

        if ($request->filled('city')) {
            $query->inCity($request->city);
        }

        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title_uz', 'like', "%{$search}%")
                    ->orWhere('title_ru', 'like', "%{$search}%")
                    ->orWhereHas('employer', function ($q) use ($search) {
                        $q->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        $sortField = $request->input('sort', 'created_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['created_at', 'title_uz', 'views_count', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $vacancies = $query->paginate($request->input('per_page', 15));

        return response()->json($vacancies);
    }

    public function show(Vacancy $vacancy): JsonResponse
    {
        $vacancy->load([
            'employer:id,company_name,phone',
            'employer.user:id,first_name,last_name,username,telegram_id',
            'categoryRelation:id,name_uz,name_ru,emoji',
            'applications' => fn($q) => $q
                ->with([
                    'worker:id,user_id,full_name,city,specialty,experience_years,expected_salary_min,expected_salary_max,photo_url,skills',
                    'worker.user:id,first_name,last_name,phone,username,telegram_id',
                ])
                ->latest(),
        ]);
        $vacancy->loadCount('applications');

        // Stage counts for pipeline
        $stageCounts = $vacancy->applications
            ->groupBy(fn($a) => $a->stage instanceof \App\Enums\ApplicationStage ? $a->stage->value : $a->stage)
            ->map->count();

        // Append computed fields
        $data = $vacancy->toArray();
        $data['work_type_label'] = $vacancy->work_type?->label();

        // Resolve category name: from relation, or fallback to slug lookup
        if ($vacancy->categoryRelation) {
            $data['category_name'] = $vacancy->categoryRelation->name_uz . ' / ' . $vacancy->categoryRelation->name_ru;
        } elseif ($vacancy->category) {
            $cat = Category::where('slug', $vacancy->category)->first();
            if ($cat) {
                $data['category_name'] = $cat->name_uz . ' / ' . $cat->name_ru;
                // Auto-fix: set category_id for future queries
                $vacancy->update(['category_id' => $cat->id]);
            } else {
                $data['category_name'] = $vacancy->category;
            }
        }

        $data['stage_counts'] = $stageCounts;

        return response()->json(['vacancy' => $data]);
    }

    public function candidates(Request $request, Vacancy $vacancy): JsonResponse
    {
        $candidates = $this->matchingService->getRecommendedCandidates(
            $vacancy,
            $request->integer('per_page', 20)
        );

        return response()->json([
            'candidates' => $candidates,
            'total_count' => $candidates->total(),
        ]);
    }

    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        $validated = $request->validate([
            'title_uz' => 'sometimes|string|max:255',
            'title_ru' => 'sometimes|string|max:255',
            'description_uz' => 'sometimes|string',
            'description_ru' => 'sometimes|string',
            'category' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'salary_min' => 'sometimes|nullable|integer|min:0|max:2000000000',
            'salary_max' => 'sometimes|nullable|integer|min:0|max:2000000000',
            'work_type' => 'sometimes|string',
            'is_top' => 'sometimes|boolean',
            'status' => 'sometimes|string',
            'close_reason' => 'sometimes|nullable|string|max:500',
        ]);

        // When reactivating, clear close_reason
        if (isset($validated['status']) && $validated['status'] === 'active') {
            $validated['close_reason'] = null;
        }

        $vacancy->update($validated);
        Cache::forget('admin:dashboard:stats');

        return response()->json(['vacancy' => $vacancy->fresh()]);
    }

    public function destroy(Vacancy $vacancy): JsonResponse
    {
        $vacancy->delete();
        Cache::forget('admin:dashboard:stats');

        return response()->json(['message' => 'Vakansiya o\'chirildi']);
    }

    public function approve(Vacancy $vacancy): JsonResponse
    {
        $vacancy->update([
            'status' => VacancyStatus::ACTIVE,
            'published_at' => now(),
        ]);

        Cache::forget('admin:pending_count');
        Cache::forget('admin:dashboard:stats');

        app(TelegramNotificationService::class)->notifyVacancyModerated($vacancy, true);
        NotifyMatchingWorkersJob::dispatch($vacancy);

        return response()->json(['message' => 'Vakansiya tasdiqlandi', 'vacancy' => $vacancy->fresh()]);
    }

    public function reject(Vacancy $vacancy): JsonResponse
    {
        $vacancy->update(['status' => VacancyStatus::CLOSED]);

        Cache::forget('admin:pending_count');
        Cache::forget('admin:dashboard:stats');

        app(TelegramNotificationService::class)->notifyVacancyModerated($vacancy, false);

        return response()->json(['message' => 'Vakansiya rad etildi', 'vacancy' => $vacancy->fresh()]);
    }
}
