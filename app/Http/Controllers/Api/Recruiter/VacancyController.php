<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use App\Services\AiService;
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
    ) {}

    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $query = Vacancy::where('employer_id', $employer->id);

        // Stats across all vacancies (unfiltered)
        $stats = [
            'active' => (clone $query)->where('status', 'active')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'closed' => (clone $query)->whereIn('status', ['closed', 'expired'])->count(),
            'total_applications' => (clone $query)->withCount('applications')
                ->get()->sum('applications_count'),
        ];

        // Apply filters
        $vacancies = (clone $query)
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('title_uz', 'like', "%{$v}%")
                  ->orWhere('title_ru', 'like', "%{$v}%");
            }))
            ->when($request->work_type, fn($q, $v) => $q->where('work_type', $v))
            ->when($request->category, fn($q, $v) => $q->where('category', $v))
            ->withCount(['applications', 'applications as new_applications_count' => function ($q) {
                $q->where('stage', 'new');
            }])
            ->with('employer:id,company_name')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'required_without:title_ru|nullable|string|max:300',
            'title_ru' => 'required_without:title_uz|nullable|string|max:300',
            'category' => 'required|string|max:50',
            'description_uz' => 'required_without:description_ru|nullable|string',
            'description_ru' => 'required_without:description_uz|nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
            'work_type' => 'required|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'experience_required' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
        ]);

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

    public function update(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $validated = $request->validate([
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'sometimes|string|max:300',
            'title_ru' => 'nullable|string|max:300',
            'category' => 'sometimes|string|max:50',
            'description_uz' => 'sometimes|string',
            'description_ru' => 'nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
            'work_type' => 'sometimes|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'experience_required' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
            'status' => 'nullable|string|in:draft,paused,closed',
        ]);

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
