<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'plan' => 'required|string|in:worker_premium,business,recruiter_pro,agency,corporate',
        ]);

        $user = $request->user();
        $plan = SubscriptionPlan::from($request->plan);

        $existing = Subscription::where('user_id', $user->id)
            ->active()
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Sizda faol obuna mavjud', 'subscription' => $existing], 422);
        }

        $price = $plan->price();

        if ($user->balance < $price) {
            return response()->json([
                'message' => 'Balans yetarli emas',
                'required' => $price,
                'balance' => $user->balance,
            ], 422);
        }

        $user->decrement('balance', $price);

        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'status' => 'active',
            'price' => $price,
            'limits' => ['max_vacancies' => $plan->maxVacancies()],
            'features' => $this->planFeatures($plan),
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json(['subscription' => $subscription], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $subscription = Subscription::where('user_id', $request->user()->id)
            ->active()
            ->first();

        if (!$subscription) {
            return response()->json([
                'subscription' => null,
                'plan' => 'free',
                'plans' => collect(SubscriptionPlan::cases())->map(fn($p) => [
                    'value' => $p->value,
                    'label' => $p->label(),
                    'price' => $p->price(),
                    'max_vacancies' => $p->maxVacancies(),
                ]),
            ]);
        }

        return response()->json([
            'subscription' => $subscription,
            'days_left' => $subscription->daysLeft(),
        ]);
    }

    public function cancel(Request $request, string $subscription): JsonResponse
    {
        $sub = Subscription::where('id', $subscription)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$sub->isActive()) {
            return response()->json(['message' => 'Obuna faol emas'], 422);
        }

        $sub->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Obuna bekor qilindi', 'subscription' => $sub->fresh()]);
    }

    private function planFeatures(SubscriptionPlan $plan): array
    {
        return match ($plan) {
            SubscriptionPlan::WORKER_PREMIUM => ['priority_search', 'resume_highlight', 'no_ads'],
            SubscriptionPlan::BUSINESS => ['10_vacancies', 'basic_analytics', 'questionnaires'],
            SubscriptionPlan::RECRUITER_PRO => ['unlimited_vacancies', 'advanced_analytics', 'talent_pool', 'ai_scoring'],
            SubscriptionPlan::AGENCY => ['team_access', 'api_access', 'white_label', 'unlimited_all'],
            SubscriptionPlan::CORPORATE => ['custom_branding', 'dedicated_support', 'bulk_posting'],
            default => [],
        };
    }
}
