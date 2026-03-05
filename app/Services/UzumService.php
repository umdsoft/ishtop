<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Http\Request;

class UzumService
{
    private string $serviceId;
    private string $secretKey;

    public function __construct()
    {
        $this->serviceId = config('services.uzum.service_id', '');
        $this->secretKey = config('services.uzum.secret_key', '');
    }

    public function handleWebhook(Request $request): array
    {
        $method = $request->input('method');
        $params = $request->input('params', []);

        return match ($method) {
            'check' => $this->check($params),
            'create' => $this->create($params),
            'confirm' => $this->confirm($params),
            'reverse' => $this->reverse($params),
            'status' => $this->status($params),
            default => $this->errorResponse(-32601, 'Method not found'),
        };
    }

    public function verifySignature(Request $request): bool
    {
        $sign = $request->header('X-Auth-Sign', '');
        $body = $request->getContent();
        $calculated = hash_hmac('sha256', $body, $this->secretKey);

        return hash_equals($calculated, $sign);
    }

    private function check(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->errorResponse(-31050, 'Order not found');
        }

        if ($payment->status->value !== 'pending') {
            return $this->errorResponse(-31051, 'Invalid order state');
        }

        return [
            'result' => [
                'allow' => true,
                'additional' => [
                    'order_id' => $payment->id,
                    'amount' => (int) ($payment->amount * 100),
                ],
            ],
        ];
    }

    private function create(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->errorResponse(-31050, 'Order not found');
        }

        $amount = $params['amount'] ?? 0;
        if ($amount !== (int) ($payment->amount * 100)) {
            return $this->errorResponse(-31001, 'Invalid amount');
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

    private function confirm(array $params): array
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
                'confirm_time' => now()->getTimestampMs(),
                'state' => 2,
            ],
        ];
    }

    private function reverse(array $params): array
    {
        $transactionId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return $this->errorResponse(-31003, 'Transaction not found');
        }

        $payment->update(['status' => 'refunded']);

        return [
            'result' => [
                'transaction' => (string) $payment->id,
                'reverse_time' => now()->getTimestampMs(),
                'state' => -1,
            ],
        ];
    }

    private function status(array $params): array
    {
        $transactionId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $transactionId)->first();

        if (!$payment) {
            return $this->errorResponse(-31003, 'Transaction not found');
        }

        $state = match ($payment->status->value) {
            'pending' => 0,
            'processing' => 1,
            'completed' => 2,
            'cancelled', 'refunded' => -1,
            default => -2,
        };

        return [
            'result' => [
                'create_time' => $payment->created_at->getTimestampMs(),
                'confirm_time' => $state === 2 ? $payment->updated_at->getTimestampMs() : 0,
                'transaction' => (string) $payment->id,
                'state' => $state,
            ],
        ];
    }

    private function errorResponse(int $code, string $message): array
    {
        return ['error' => ['code' => $code, 'message' => $message]];
    }
}
