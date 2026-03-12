<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\PaymeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private PaymeService $paymeService,
    ) {}

    public function create(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string|in:subscription,vacancy_top,vacancy_urgent,vacancy_post,balance_topup,candidate_unlock',
            'amount' => 'required|numeric|min:1000',
            'method' => 'required|string|in:payme,click,uzum,balance',
            'payable_type' => 'nullable|string',
            'payable_id' => 'nullable|uuid',
            'meta' => 'nullable|array',
        ]);

        $user = $request->user();

        $payment = $this->paymentService->create($user, [
            'type' => $request->type,
            'amount' => $request->amount,
            'method' => $request->method,
            'payable_type' => $request->payable_type,
            'payable_id' => $request->payable_id,
            'meta' => $request->meta,
        ]);

        if ($request->method === 'balance') {
            $success = $this->paymentService->payWithBalance($user, $payment);
            if (!$success) {
                return response()->json(['message' => 'Balans yetarli emas'], 422);
            }
            return response()->json([
                'payment' => $payment->fresh(),
                'message' => "Balansdan muvaffaqiyatli amalga oshirildi",
            ]);
        }

        $checkoutUrl = $this->generateCheckoutUrl($payment, $request->method);

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ], 201);
    }

    private function generateCheckoutUrl(Payment $payment, string $method): ?string
    {
        return match ($method) {
            'payme' => $this->paymeService->generateCheckoutUrl($payment),
            default => null,
        };
    }

    public function history(Request $request): JsonResponse
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->completed()
            ->orderByDesc('created_at')
            ->paginate(min($request->per_page ?? 20, 100));

        $typeLabels = [
            'candidate_unlock' => 'Nomzodlarni ochish',
            'vacancy_post' => "E'lon joylashtirish",
            'balance_topup' => "Balansni to'ldirish",
            'subscription' => 'Obuna',
            'vacancy_top' => "TOP e'lon",
            'vacancy_urgent' => "Shoshilinch e'lon",
        ];

        // N+1 oldini olish: barcha vacancy ID larni yig'ib, bitta queryda olish
        $vacancyPayments = $payments->getCollection()->filter(
            fn($p) => $p->payable_type === \App\Models\Vacancy::class && $p->payable_id
        );
        $vacancyMap = $vacancyPayments->isNotEmpty()
            ? \App\Models\Vacancy::whereIn('id', $vacancyPayments->pluck('payable_id'))
                ->pluck('title_uz', 'id')
            : collect();

        $payments->getCollection()->transform(function ($payment) use ($typeLabels, $vacancyMap) {
            $payment->type_label = $typeLabels[$payment->type] ?? $payment->type;
            $payment->description = $vacancyMap->get($payment->payable_id);
            return $payment;
        });

        return response()->json($payments);
    }

    public function topup(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1000|max:10000000',
            'method' => 'required|string|in:payme,click,uzum',
        ]);

        $user = $request->user();

        $payment = $this->paymentService->create($user, [
            'type' => 'balance_topup',
            'amount' => $request->amount,
            'method' => $request->method,
        ]);

        $checkoutUrl = $this->generateCheckoutUrl($payment, $request->method);

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ], 201);
    }
}
