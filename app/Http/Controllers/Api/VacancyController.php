<?php

namespace App\Http\Controllers\Api;

use App\Enums\VacancyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVacancyRequest;
use App\Http\Requests\UpdateVacancyRequest;
use App\Models\Payment;
use App\Models\Vacancy;
use App\Services\AiService;
use App\Services\GeoService;
use App\Services\MatchingService;
use App\Services\PaymentService;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function __construct(
        private VacancyService $vacancyService,
        private PaymentService $paymentService,
        private GeoService $geoService,
        private MatchingService $matchingService,
        private AiService $aiService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->when($request->category, fn($q, $v) => is_array($v) ? $q->whereIn('category', $v) : $q->where('category', $v))
            ->when($request->city, fn($q, $v) => is_array($v) ? $q->whereIn('city', $v) : $q->where('city', $v))
            ->when($request->work_type, fn($q, $v) => $q->where('work_type', $v))
            ->when($request->salary_min, fn($q, $v) => $q->where('salary_max', '>=', $v))
            ->when($request->salary_max, fn($q, $v) => $q->where('salary_min', '<=', $v))
            ->when($request->q, fn($q, $v) => $q->search($v));

        // Optional geo filter: lat + lng + radius (km) → viloyat bo'yicha filtrlash
        if ($request->filled(['lat', 'lng'])) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = min((int) ($request->radius ?? 100), 200);
            $haversine = GeoService::haversineFormula();

            $query->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radius])
                ->selectRaw("vacancies.*, {$haversine} as distance_km", [$lat, $lng, $lat]);
        }

        $vacancies = $query
            ->orderByDesc('is_top')
            ->orderByDesc('published_at')
            ->paginate(min($request->per_page ?? 20, 100));

        return response()->json($vacancies);
    }

    public function show(Vacancy $vacancy): JsonResponse
    {
        $this->vacancyService->incrementViews($vacancy);
        $vacancy->load(['employer.user:id,first_name', 'questionnaire:id,vacancy_id,questions_count']);

        return response()->json(['vacancy' => $vacancy]);
    }

    public function my(Request $request): JsonResponse
    {
        $user = $request->user();
        $employer = $user->employerProfile;

        if (!$employer) {
            return response()->json(['vacancies' => []]);
        }

        $vacancies = Vacancy::where('employer_id', $employer->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['vacancies' => $vacancies]);
    }

    public function store(StoreVacancyRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $employer = $request->user()->employerProfile;
        if (!$employer) {
            $employer = $request->user()->employerProfiles()->create([
                'company_name' => $validated['company_name'] ?? $request->user()->first_name,
            ]);
            $request->user()->update(['active_employer_id' => $employer->id]);
        }

        // Auto-translate missing language fields
        $validated = $this->autoTranslate($validated);

        $vacancy = $this->vacancyService->create(array_merge($validated, [
            'employer_id' => $employer->id,
        ]));

        return response()->json(['vacancy' => $vacancy], 201);
    }

    public function update(UpdateVacancyRequest $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('update', $vacancy);

        $validated = $request->validated();

        $vacancy->update($validated);

        return response()->json(['vacancy' => $vacancy->fresh()]);
    }

    public function destroy(Request $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('delete', $vacancy);

        $vacancy->delete();
        return response()->json(['message' => 'Deleted']);
    }

    /**
     * Auto-translate vacancy fields if only one language is provided.
     */
    private function autoTranslate(array $data): array
    {
        $from = $data['language'] ?? null;

        // Determine source language from filled fields
        if (!$from) {
            $hasUz = !empty($data['title_uz']) || !empty($data['description_uz']);
            $hasRu = !empty($data['title_ru']) || !empty($data['description_ru']);
            $from = $hasRu && !$hasUz ? 'ru' : 'uz';
        }

        $to = $from === 'uz' ? 'ru' : 'uz';

        // Collect fields that need translation
        $fieldsToTranslate = [];
        foreach (['title', 'description', 'requirements'] as $field) {
            $srcKey = "{$field}_{$from}";
            $dstKey = "{$field}_{$to}";
            if (!empty($data[$srcKey]) && empty($data[$dstKey])) {
                $fieldsToTranslate[$field] = $data[$srcKey];
            }
        }

        if (empty($fieldsToTranslate)) {
            return $data;
        }

        try {
            $translated = $this->aiService->translateVacancy($fieldsToTranslate, $from, $to);
            foreach ($translated as $field => $value) {
                $dstKey = "{$field}_{$to}";
                if (!empty($value)) {
                    $data[$dstKey] = $value;
                }
            }
        } catch (\Throwable $e) {
            // Translation failed — proceed without it
            \Log::warning('Auto-translate failed: ' . $e->getMessage());
        }

        return $data;
    }

    public function activate(Request $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('activate', $vacancy);

        $user = $request->user();
        $employer = $user->employerProfile;

        if ($vacancy->status !== VacancyStatus::PENDING) {
            return response()->json(['message' => 'Bu e\'lon allaqachon faollashtirilgan'], 422);
        }

        // 1 ta bepul e'lon — faollashtirilgan e'lonlar soni 0 bo'lsa
        $activatedCount = Vacancy::where('employer_id', $employer->id)
            ->whereNotIn('status', [VacancyStatus::PENDING, VacancyStatus::DRAFT])
            ->count();

        if ($activatedCount === 0) {
            // Bepul faollashtirish
            $this->vacancyService->approve($vacancy);
            return response()->json([
                'vacancy' => $vacancy->fresh(),
                'message' => 'Birinchi e\'loningiz bepul faollashtirildi!',
                'free' => true,
            ]);
        }

        // Pullik faollashtirish
        $request->validate([
            'method' => 'required|string|in:payme,click,uzum,balance',
        ]);

        $method = $request->input('method');
        $amount = config('kadrgo.pricing.vacancy');

        $payment = $this->paymentService->create($user, [
            'type' => 'vacancy_post',
            'amount' => $amount,
            'method' => $method,
            'payable_type' => Vacancy::class,
            'payable_id' => $vacancy->id,
        ]);

        // Balance payment — instant activation
        if ($method === 'balance') {
            $success = $this->paymentService->payWithBalance($user, $payment);
            if (!$success) {
                return response()->json(['message' => 'Balans yetarli emas'], 422);
            }
            return response()->json([
                'payment' => $payment->fresh(),
                'vacancy' => $vacancy->fresh(),
                'message' => 'E\'lon muvaffaqiyatli faollashtirildi!',
            ]);
        }

        // External payment — return checkout URL
        $checkoutUrl = null;
        if ($method === 'click') {
            $merchantId = config('services.click.merchant_id');
            $serviceId = config('services.click.service_id');
            $checkoutUrl = "https://my.click.uz/services/pay?service_id={$serviceId}&merchant_id={$merchantId}&amount={$amount}&transaction_param={$payment->id}";
        } elseif ($method === 'payme') {
            $checkoutUrl = app(\App\Services\PaymeService::class)->generateCheckoutUrl($payment);
        }

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ], 201);
    }

    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:50',
        ]);

        $vacancies = $this->geoService
            ->nearbyVacancies($request->lat, $request->lng, $request->radius ?? 10)
            ->when($request->category, fn($q, $v) => is_array($v) ? $q->whereIn('category', $v) : $q->where('category', $v))
            ->with('employer:id,company_name,logo_url')
            ->paginate(min($request->per_page ?? 20, 100));

        // Add match score if worker profile exists
        $worker = $request->user()?->workerProfile;
        if ($worker) {
            $vacancies->getCollection()->transform(function ($vacancy) use ($worker) {
                $vacancy->match_score = $this->matchingService->calculateMatchScore($worker, $vacancy);
                return $vacancy;
            });
        }

        return response()->json($vacancies);
    }

    public function pricing(Request $request): JsonResponse
    {
        $freePostsLeft = 0;
        $user = $request->user();
        if ($user) {
            $employer = $user->employerProfile;
            if ($employer) {
                $activatedCount = Vacancy::where('employer_id', $employer->id)
                    ->whereNotIn('status', [VacancyStatus::PENDING, VacancyStatus::DRAFT])
                    ->count();
                $freePostsLeft = $activatedCount === 0 ? 1 : 0;
            } else {
                $freePostsLeft = 1; // Hali employer profili ham yo'q — birinchi e'lon bepul
            }
        }

        return response()->json([
            'pricing' => config('kadrgo.pricing'),
            'free_posts_left' => $freePostsLeft,
        ]);
    }

    public function candidates(Request $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('viewCandidates', $vacancy);

        $user = $request->user();

        if (!$vacancy->isActive()) {
            return response()->json(['candidates' => [], 'total_count' => 0, 'locked' => true]);
        }

        $isUnlocked = Payment::where('type', 'candidate_unlock')
            ->where('payable_type', Vacancy::class)
            ->where('payable_id', $vacancy->id)
            ->where('user_id', $user->id)
            ->completed()
            ->exists();

        $limit = $isUnlocked ? 20 : 3;
        $paginator = $this->matchingService->getRecommendedCandidates($vacancy, $limit);
        $totalCount = $paginator->total();

        $candidateData = $paginator->getCollection()->map(fn($worker) => [
            'id' => $worker->id,
            'full_name' => $worker->full_name,
            'specialty' => $worker->specialty,
            'city' => $worker->city,
            'experience_years' => $worker->experience_years,
            'photo_url' => $worker->photo_url,
            'match_score' => $worker->match_score,
        ]);

        $response = [
            'candidates' => $candidateData,
            'total_count' => $totalCount,
            'locked' => !$isUnlocked,
        ];

        if (!$isUnlocked) {
            $response['unlock_price'] = config('kadrgo.pricing.candidate_unlock');
        }

        return response()->json($response);
    }

    public function unlockCandidates(Request $request, Vacancy $vacancy): JsonResponse
    {
        $this->authorize('viewCandidates', $vacancy);

        $user = $request->user();

        $alreadyUnlocked = Payment::where('type', 'candidate_unlock')
            ->where('payable_type', Vacancy::class)
            ->where('payable_id', $vacancy->id)
            ->where('user_id', $user->id)
            ->completed()
            ->exists();

        if ($alreadyUnlocked) {
            return response()->json(['message' => 'Nomzodlar allaqachon ochilgan'], 422);
        }

        $amount = config('kadrgo.pricing.candidate_unlock');

        if ($user->balance < $amount) {
            return response()->json([
                'message' => 'Balans yetarli emas. Iltimos balansni to\'ldiring.',
                'balance' => $user->balance,
                'required' => $amount,
            ], 422);
        }

        $payment = $this->paymentService->create($user, [
            'type' => 'candidate_unlock',
            'amount' => $amount,
            'method' => 'balance',
            'payable_type' => Vacancy::class,
            'payable_id' => $vacancy->id,
        ]);

        $this->paymentService->payWithBalance($user, $payment);

        return response()->json([
            'payment' => $payment->fresh(),
            'vacancy' => $vacancy->fresh(),
            'message' => 'Nomzodlar muvaffaqiyatli ochildi!',
        ]);
    }

    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();
        $worker = $user->workerProfile;

        // Profile must have at least one meaningful field to enable matching
        $isProfileFilled = $worker && ($worker->city || $worker->specialty || !empty($worker->preferred_categories));

        if (!$isProfileFilled) {
            return response()->json([
                'vacancies' => [],
                'profile_incomplete' => true,
            ]);
        }

        // Cache recommended for 1 min — short TTL to pick up profile changes quickly
        $cacheKey = "recommended_worker_{$worker->id}";
        $vacancies = cache()->remember($cacheKey, 60, function () use ($worker) {
            return $this->matchingService->findMatchesForWorker($worker, 10);
        });

        return response()->json(['vacancies' => $vacancies]);
    }
}
