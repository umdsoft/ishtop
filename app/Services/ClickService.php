<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClickService
{
    private string $serviceId;
    private string $merchantId;
    private string $secretKey;
    private string $merchantUserId;

    public function __construct()
    {
        $this->serviceId = config('services.click.service_id') ?? '';
        $this->merchantId = config('services.click.merchant_id') ?? '';
        $this->secretKey = config('services.click.secret_key') ?? '';
        $this->merchantUserId = config('services.click.merchant_user_id') ?? '';
    }

    /**
     * Checkout URL yaratish — foydalanuvchini Click to'lov sahifasiga yo'naltirish.
     */
    public function generateCheckoutUrl(Payment $payment): string
    {
        $params = http_build_query([
            'service_id' => $this->serviceId,
            'merchant_id' => $this->merchantUserId,
            'amount' => (int) $payment->amount,
            'transaction_param' => $payment->id,
            'return_url' => url('/miniapp'),
        ]);

        return 'https://my.click.uz/services/pay?' . $params;
    }

    // ─── Prepare (action = 0) ─────────────────────────────────────────

    public function handlePrepare(Request $request): array
    {
        Log::channel('daily')->info('Click prepare', $request->only(['merchant_trans_id', 'amount', 'action', 'sign_time']));

        if (!$this->verifySignature($request)) {
            return $this->response($request, -1, 'SIGN CHECK FAILED!');
        }

        $merchantTransId = $request->input('merchant_trans_id');
        $amount = (float) $request->input('amount');

        $payment = Payment::find($merchantTransId);

        if (!$payment) {
            return $this->response($request, -5, 'Order not found');
        }

        if ((float) $payment->amount !== $amount) {
            return $this->response($request, -2, 'Incorrect parameter amount');
        }

        // Allaqachon to'langan
        if ($payment->status === PaymentStatus::COMPLETED) {
            return $this->response($request, -4, 'Already paid');
        }

        // Faqat pending qabul qilinadi
        if ($payment->status !== PaymentStatus::PENDING) {
            return $this->response($request, -3, 'Action not found');
        }

        $payment->update([
            'status' => PaymentStatus::PROCESSING,
            'external_id' => (string) $request->input('click_trans_id'),
        ]);

        return [
            'click_trans_id' => (int) $request->input('click_trans_id'),
            'merchant_trans_id' => $merchantTransId,
            'merchant_prepare_id' => $payment->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }

    // ─── Complete (action = 1) ────────────────────────────────────────

    public function handleComplete(Request $request): array
    {
        Log::channel('daily')->info('Click complete', $request->only(['merchant_trans_id', 'merchant_prepare_id', 'amount', 'action', 'error']));

        if (!$this->verifySignature($request)) {
            return $this->response($request, -1, 'SIGN CHECK FAILED!');
        }

        $merchantPrepareId = $request->input('merchant_prepare_id');
        $payment = Payment::find($merchantPrepareId);

        if (!$payment) {
            return $this->response($request, -6, 'Transaction does not exist');
        }

        // Click xatolik yuborgan — tranzaktsiya bekor qilingan
        if ((int) $request->input('error') < 0) {
            $payment->update(['status' => PaymentStatus::CANCELLED]);
            return $this->response($request, -9, 'Transaction cancelled');
        }

        // Allaqachon to'langan — idempotent javob
        if ($payment->status === PaymentStatus::COMPLETED) {
            return [
                'click_trans_id' => (int) $request->input('click_trans_id'),
                'merchant_trans_id' => $request->input('merchant_trans_id'),
                'merchant_confirm_id' => $payment->id,
                'error' => 0,
                'error_note' => 'Success',
            ];
        }

        if ($payment->status !== PaymentStatus::PROCESSING) {
            return $this->response($request, -3, 'Action not found');
        }

        app(PaymentService::class)->complete($payment, (string) $request->input('click_trans_id'));

        return [
            'click_trans_id' => (int) $request->input('click_trans_id'),
            'merchant_trans_id' => $request->input('merchant_trans_id'),
            'merchant_confirm_id' => $payment->id,
            'error' => 0,
            'error_note' => 'Success',
        ];
    }

    // ─── Signature verification ───────────────────────────────────────

    /**
     * Click sign_string tekshiruvi.
     *
     * Prepare (action=0):
     *   md5(click_trans_id + service_id + SECRET_KEY + merchant_trans_id + amount + action + sign_time)
     *
     * Complete (action=1):
     *   md5(click_trans_id + service_id + SECRET_KEY + merchant_trans_id + merchant_prepare_id + amount + action + sign_time)
     */
    private function verifySignature(Request $request): bool
    {
        if (empty($this->secretKey)) {
            return false;
        }

        $action = (int) $request->input('action');

        $data = $request->input('click_trans_id')
            . $request->input('service_id')
            . $this->secretKey
            . $request->input('merchant_trans_id');

        // Complete action uchun merchant_prepare_id ham qo'shiladi
        if ($action === 1) {
            $data .= $request->input('merchant_prepare_id');
        }

        $data .= $request->input('amount')
            . $request->input('action')
            . $request->input('sign_time');

        return md5($data) === $request->input('sign_string');
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    private function response(Request $request, int $error, string $note): array
    {
        return [
            'click_trans_id' => (int) $request->input('click_trans_id'),
            'merchant_trans_id' => $request->input('merchant_trans_id', $request->input('merchant_prepare_id')),
            'error' => $error,
            'error_note' => $note,
        ];
    }
}
