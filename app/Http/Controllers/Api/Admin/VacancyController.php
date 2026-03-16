<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Jobs\NotifyMatchingWorkersJob;
use App\Models\Vacancy;
use App\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VacancyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Vacancy::with('employer:id,company_name')
            ->withCount('applications');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('is_top')) {
            $query->where('is_top', $request->boolean('is_top'));
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
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
        $vacancy->load(['employer:id,company_name,phone', 'applications']);
        $vacancy->loadCount('applications');

        return response()->json(['vacancy' => $vacancy]);
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
            'salary_from' => 'sometimes|nullable|integer',
            'salary_to' => 'sometimes|nullable|integer',
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
