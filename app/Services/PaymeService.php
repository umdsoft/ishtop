<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymeService
{
    private string $merchantId;
    private string $secretKey;

    public function __construct()
    {
        $this->merchantId = config('services.payme.merchant_id', '');
        $this->secretKey = config('services.payme.secret_key', '');
    }

    public function generateCheckoutUrl(Payment $payment): string
    {
        $params = base64_encode(sprintf(
            'm=%s;ac.order_id=%s;a=%d;c=%s',
            $this->merchantId,
            $payment->id,
            $payment->amount * 100, // tiyin
            config('app.url') . '/api/payments/callback'
        ));

        return "https://checkout.paycom.uz/{$params}";
    }

    public function handleWebhook(Request $request): array
    {
        $method = $request->input('method');
        $params = $request->input('params', []);

        return match ($method) {
            'CheckPerformTransaction' => $this->checkPerformTransaction($params),
            'CreateTransaction' => $this->createTransaction($params),
            'PerformTransaction' => $this->performTransaction($params),
            'CancelTransaction' => $this->cancelTransaction($params),
            'CheckTransaction' => $this->checkTransaction($params),
            default => $this->errorResponse(-32601, 'Method not found'),
        };
    }

    private function checkPerformTransaction(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->errorResponse(-31050, 'Order not found');
        }

        if ($payment->status->value !== 'pending') {
            return $this->errorResponse(-31051, 'Invalid order state');
        }

        $amount = $params['amount'] ?? 0;
        if ($amount !== (int)($payment->amount * 100)) {
            return $this->errorResponse(-31001, 'Invalid amount');
        }

        return ['result' => ['allow' => true]];
    }

    private function createTransaction(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->errorResponse(-31050, 'Order not found');
        }

        $payment->update([
            'status' => 'processing',
            'external_id' => $params['id'] ?? null,
        ]);

        return [
            'result' => [
                'create_time' => now()->getTimestampMs(),
                'transaction' => (string) $payment->id,
                'state' => 1,
            ],
        ];
    }

    private function performTransaction(array $params): array
    {
        $transactionId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return $this->errorResponse(-31003, 'Transaction not found');
        }

        app(PaymentService::class)->complete($payment, $transactionId);

        return [
            'result' => [
                'transaction' => (string) $payment->id,
                'perform_time' => now()->getTimestampMs(),
                'state' => 2,
            ],
        ];
    }

    private function cancelTransaction(array $params): array
    {
        $transactionId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return $this->errorResponse(-31003, 'Transaction not found');
        }

        $payment->update(['status' => 'cancelled']);

        return [
            'result' => [
                'transaction' => (string) $payment->id,
                'cancel_time' => now()->getTimestampMs(),
                'state' => -1,
            ],
        ];
    }

    private function checkTransaction(array $params): array
    {
        $transactionId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return $this->errorResponse(-31003, 'Transaction not found');
        }

        $state = match ($payment->status->value) {
            'pending' => 1,
            'processing' => 1,
            'completed' => 2,
            'cancelled', 'refunded' => -1,
            default => -2,
        };

        return [
            'result' => [
                'create_time' => $payment->created_at->getTimestampMs(),
                'perform_time' => $state === 2 ? $payment->updated_at->getTimestampMs() : 0,
                'cancel_time' => $state === -1 ? $payment->updated_at->getTimestampMs() : 0,
                'transaction' => (string) $payment->id,
                'state' => $state,
            ],
        ];
    }

    private function errorResponse(int $code, string $message): array
    {
        return ['error' => ['code' => $code, 'message' => ['uz' => $message]]];
    }
}
