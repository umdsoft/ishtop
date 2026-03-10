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
        ]);

        $user = $request->user();

        $payment = $this->paymentService->create($user, [
            'type' => $request->type,
            'amount' => $request->amount,
            'method' => $request->method,
            'payable_type' => $request->payable_type,
            'payable_id' => $request->payable_id,
        ]);

        if ($request->method === 'balance') {
            $success = $this->paymentService->payWithBalance($user, $payment);
            if (!$success) {
                return response()->json(['message' => 'Balans yetarli emas'], 422);
            }
            return response()->json([
                'payment' => $payment->fresh(),
                'message' => 'Balansdan muvaffaqiyatli amalga oshirildi',
            ]);
        }

        $checkoutUrl = null;
        if ($request->method === 'payme') {
            $checkoutUrl = $this->paymeService->generateCheckoutUrl($payment);
        }

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ], 201);
    }

    public function history(Request $request): JsonResponse
    {
        $payments = Payment::where('user_id', $request->user()->id)
            ->completed()
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $typeLabels = [
            'candidate_unlock' => 'Nomzodlarni ochish',
            'vacancy_post' => "E'lon joylashtirish",
            'balance_topup' => "Balansni to'ldirish",
            'subscription' => 'Obuna',
            'vacancy_top' => "TOP e'lon",
            'vacancy_urgent' => "Shoshilinch e'lon",
        ];

        $payments->getCollection()->transform(function ($payment) use ($typeLabels) {
            $payment->type_label = $typeLabels[$payment->type] ?? $payment->type;

            $payment->description = null;
            if ($payment->payable_type === \App\Models\Vacancy::class && $payment->payable_id) {
                $vacancy = \App\Models\Vacancy::select('id', 'title_uz', 'title_ru')->find($payment->payable_id);
                if ($vacancy) {
                    $payment->description = $vacancy->title_uz ?: $vacancy->title_ru;
                }
            }

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

        $checkoutUrl = null;
        if ($request->method === 'payme') {
            $checkoutUrl = $this->paymeService->generateCheckoutUrl($payment);
        }

        return response()->json([
            'payment' => $payment,
            'checkout_url' => $checkoutUrl,
        ], 201);
    }
}
