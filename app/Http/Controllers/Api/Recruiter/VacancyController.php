<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Vacancy;
use App\Services\AiService;
use App\Services\MatchingService;
use App\Services\SubscriptionLimitService;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function __construct(
        private VacancyService $vacancyService,
        private AiService $aiService,
        private SubscriptionLimitService $limitService,
        private MatchingService $matchingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $query = Vacancy::where('employer_id', $employer->id);

        // Stats across all vacancies — single GROUP BY query instead of 3 separate COUNT queries
        $statusCounts = (clone $query)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $stats = [
            'active' => (int) ($statusCounts['active'] ?? 0),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'closed' => (int) (($statusCounts['closed'] ?? 0) + ($statusCounts['expired'] ?? 0)),
            'total_applications' => \App\Models\Application::whereIn(
                'vacancy_id', (clone $query)->select('id')
            )->count(),
        ];

        // Apply filters
        $vacancies = (clone $query)
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $v);
                $q->where('title_uz', 'like', "%{$escaped}%")
                  ->orWhere('title_ru', 'like', "%{$escaped}%");
            }))
            ->when($request->work_type, fn($q, $v) => $q->where('work_type', $v))
            ->when($request->category, fn($q, $v) => $q->where('category', $v))
            ->withCount(['applications', 'applications as new_applications_count' => function ($q) {
                $q->where('stage', 'new');
            }])
            ->with('employer:id,company_name')
            ->orderByDesc('created_at')
            ->paginate(min($request->per_page ?? 20, 100));

        // Add recommended candidates count — batch query (N+1 → 4 queries)
        $recommendedCounts = $this->matchingService->countRecommendedCandidatesBatch($vacancies->getCollection());
        $vacancies->getCollection()->transform(function ($vacancy) use ($recommendedCounts) {
            $vacancy->recommended_count = $recommendedCounts[$vacancy->id] ?? 0;
            return $vacancy;
        });

        // Subscription limits info
        $user = $request->user();
        $subscriptionInfo = [
            'plan' => $this->limitService->currentPlan($user)->value,
            'can_create_vacancy' => $this->limitService->canCreateVacancy($user),
            'remaining_vacancies' => $this->limitService->remainingVacancies($user),
            'limits' => $this->limitService->limits($user),
        ];

        return response()->json([
            'vacancies' => $vacancies,
            'stats' => $stats,
            'subscription' => $subscriptionInfo,
        ]);
    }

    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = $request->user();
        $employer = $user->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        // Check subscription limit
        if (!$this->limitService->canCreateVacancy($user)) {
            $plan = $this->limitService->currentPlan($user);
            $max = $plan->limits()['max_vacancies'];
            return response()->json([
                'message' => "Vakansiya limiti tugadi. {$plan->label()} rejada maksimum {$max} ta vakansiya yaratish mumkin.",
                'limit_reached' => true,
                'current_plan' => $plan->value,
                'max_vacancies' => $max,
            ], 403);
        }

        $vacancy = $this->vacancyService->create(array_merge($validated, [
            'employer_id' => $employer->id,
        ]));

        return response()->json(['vacancy' => $vacancy], 201);
    }

    public function show(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->withCount(['applications', 'applications as new_applications_count' => function ($q) {
                $q->where('stage', 'new');
            }])
            ->with(['questionnaire.questions.options', 'employer:id,company_name'])
            ->firstOrFail();

        // Stage counts for pipeline
        $stageCounts = \App\Models\Application::where('vacancy_id', $vacancyModel->id)
            ->selectRaw('stage, COUNT(*) as count')
            ->groupBy('stage')
            ->pluck('count', 'stage');

        return response()->json([
            'vacancy' => $vacancyModel,
            'stage_counts' => $stageCounts,
        ]);
    }

    public function update(UpdateVacancyRequest $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $validated = $request->validated();

        $vacancyModel->update($validated);

        return response()->json(['vacancy' => $vacancyModel->fresh()]);
    }

    public function toggleStatus(Request $request, string $vacancy): JsonResponse
    {
        $user = $request->user();
        $employer = $user->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        if ($vacancyModel->status->value === 'active') {
            $vacancyModel->update(['status' => 'paused']);
        } else {
            // Only check limit if reactivating from closed/expired (would increase count)
            // Vacancies in pending/draft/paused are already counted in the limit
            $isAlreadyCounted = !in_array($vacancyModel->status->value, ['closed', 'expired']);

            if (!$isAlreadyCounted && !$this->limitService->canCreateVacancy($user)) {
                $plan = $this->limitService->currentPlan($user);
                return response()->json([
                    'message' => "Faol vakansiya limiti tugadi. {$plan->label()} rejada ko'proq vakansiya faollashtirish uchun obunangizni yangilang.",
                    'limit_reached' => true,
                ], 403);
            }

            $vacancyModel->update([
                'status' => 'active',
                'published_at' => $vacancyModel->published_at ?? now(),
            ]);
        }

        return response()->json(['vacancy' => $vacancyModel->fresh()]);
    }

    public function destroy(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $vacancyModel->delete();

        return response()->json(['message' => 'Vakansiya o\'chirildi']);
    }

    public function translate(Request $request): JsonResponse
    {
        // Check AI translation feature access
        if (!$this->limitService->hasFeature($request->user(), 'ai_translation')) {
            return response()->json([
                'message' => 'AI tarjima funksiyasi faqat Biznes va undan yuqori rejalarda mavjud.',
                'limit_reached' => true,
            ], 403);
        }

        $validated = $request->validate([
            'from' => 'required|string|in:uz,ru',
            'to' => 'required|string|in:uz,ru',
            'title' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
        ]);

        $from = $validated['from'];
        $to = $validated['to'];

        if ($from === $to) {
            return response()->json(['message' => 'Manba va maqsad til bir xil bo\'lmasligi kerak'], 422);
        }

        // Collect non-empty fields to translate
        $fields = [];
        foreach (['title', 'description', 'requirements', 'responsibilities'] as $field) {
            if (!empty($validated[$field])) {
                $fields[$field] = $validated[$field];
            }
        }

        if (empty($fields)) {
            return response()->json(['message' => 'Tarjima qilish uchun matn kerak'], 422);
        }

        $translated = $this->aiService->translateVacancy($fields, $from, $to);

        if (empty($translated)) {
            return response()->json(['message' => 'Tarjima xatoligi. Qaytadan urinib ko\'ring.'], 500);
        }

        return response()->json(['translated' => $translated]);
    }
}
