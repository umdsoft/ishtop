<?php

namespace App\Http\Controllers\Api;

use App\Enums\SubscriptionPlan;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Services\PaymentService;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private SubscriptionLimitService $limitService,
    ) {}

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
            'limits' => $plan->limits(),
            'features' => $plan->features(),
            'starts_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        return response()->json(['subscription' => $subscription], 201);
    }

    public function current(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json($this->limitService->getSubscriptionInfo($user));
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
}
