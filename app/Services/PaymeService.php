<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymeService
{
    private string $merchantId;
    private string $key;

    private const TIMEOUT_MS = 43_200_000; // 12 soat millisekund

    public function __construct()
    {
        $this->merchantId = config('services.payme.merchant_id') ?? '';
        $this->key = app()->environment('production')
            ? (config('services.payme.secret_key') ?? '')
            : (config('services.payme.test_key') ?? config('services.payme.secret_key') ?? '');
    }

    /**
     * Payme webhook autentifikatsiya — Basic Auth tekshirish.
     * Payme "Paycom:{KEY}" formatda yuboradi.
     */
    public function authenticate(Request $request): bool
    {
        $auth = $request->header('Authorization', '');

        if (!str_starts_with($auth, 'Basic ')) {
            return false;
        }

        $decoded = base64_decode(substr($auth, 6));
        $parts = explode(':', $decoded, 2);

        if (count($parts) !== 2) {
            return false;
        }

        // Payme "Paycom" login bilan yuboradi, parol = merchant key
        return $parts[1] === $this->key;
    }

    /**
     * Checkout URL yaratish — foydalanuvchini to'lov sahifasiga yo'naltirish uchun.
     */
    public function generateCheckoutUrl(Payment $payment): string
    {
        $params = sprintf(
            'm=%s;ac.order_id=%s;a=%d;c=%s',
            $this->merchantId,
            $payment->id,
            (int) ($payment->amount * 100), // so'm → tiyin
            url('/miniapp')
        );

        return 'https://checkout.paycom.uz/' . base64_encode($params);
    }

    /**
     * Webhook handler — Payme JSON-RPC so'rovlarini qayta ishlaydi.
     */
    public function handleWebhook(Request $request): array
    {
        $method = $request->input('method');
        $params = $request->input('params', []);
        $rpcId = $request->input('id');

        $result = match ($method) {
            'CheckPerformTransaction' => $this->checkPerformTransaction($params),
            'CreateTransaction' => $this->createTransaction($params),
            'PerformTransaction' => $this->performTransaction($params),
            'CancelTransaction' => $this->cancelTransaction($params),
            'CheckTransaction' => $this->checkTransaction($params),
            'GetStatement' => $this->getStatement($params),
            default => $this->error(-32601, 'Metod topilmadi', 'Method not found', 'Метод не найден'),
        };

        $result['id'] = $rpcId;

        return $result;
    }

    // ─── CheckPerformTransaction ─────────────────────────────────────

    private function checkPerformTransaction(array $params): array
    {
        $orderId = $params['account']['order_id'] ?? null;
        $amount = $params['amount'] ?? 0;

        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->error(-31050, 'Buyurtma topilmadi', 'Order not found', 'Заказ не найден', 'order_id');
        }

        if (!in_array($payment->status, [PaymentStatus::PENDING, PaymentStatus::PROCESSING])) {
            return $this->error(-31051, "Buyurtma to'lov qabul qila olmaydi", 'Order cannot accept payment', 'Заказ не может принять оплату', 'order_id');
        }

        if ($amount !== (int) ($payment->amount * 100)) {
            return $this->error(-31001, "Noto'g'ri summa", 'Invalid amount', 'Неверная сумма');
        }

        return ['result' => ['allow' => true]];
    }

    // ─── CreateTransaction ───────────────────────────────────────────

    private function createTransaction(array $params): array
    {
        $paymeId = $params['id'] ?? null;
        $paymeTime = $params['time'] ?? 0;
        $amount = $params['amount'] ?? 0;
        $orderId = $params['account']['order_id'] ?? null;

        $payment = Payment::find($orderId);

        if (!$payment) {
            return $this->error(-31050, 'Buyurtma topilmadi', 'Order not found', 'Заказ не найден', 'order_id');
        }

        if ($amount !== (int) ($payment->amount * 100)) {
            return $this->error(-31001, "Noto'g'ri summa", 'Invalid amount', 'Неверная сумма');
        }

        // Idempotentlik: agar bu payme_id allaqachon bo'lsa — mavjud tranzaktsiyani qaytarish
        if ($payment->external_id === $paymeId) {
            return $this->transactionResult($payment, 1);
        }

        // Agar boshqa payme tranzaktsiyasi bilan bog'langan bo'lsa
        if ($payment->external_id && $payment->external_id !== $paymeId) {
            return $this->error(-31008, "Buyurtma boshqa tranzaktsiya bilan band", 'Order is linked to another transaction', 'Заказ привязан к другой транзакции');
        }

        // Faqat pending holatdagi payment qabul qilinadi
        if ($payment->status !== PaymentStatus::PENDING) {
            return $this->error(-31008, "Buyurtma holati tranzaktsiya yaratishga ruxsat bermaydi", 'Order state does not allow transaction creation', 'Состояние заказа не позволяет создать транзакцию');
        }

        // Tranzaktsiya yaratish
        $now = now()->getTimestampMs();
        $payment->update([
            'status' => PaymentStatus::PROCESSING,
            'external_id' => $paymeId,
            'meta' => array_merge($payment->meta ?? [], [
                'payme_create_time' => $paymeTime,
                'create_time' => $now,
            ]),
        ]);

        return $this->transactionResult($payment->fresh(), 1);
    }

    // ─── PerformTransaction ──────────────────────────────────────────

    private function performTransaction(array $params): array
    {
        $paymeId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            return $this->error(-31003, 'Tranzaktsiya topilmadi', 'Transaction not found', 'Транзакция не найдена');
        }

        // Idempotentlik: allaqachon completed bo'lsa
        if ($payment->status === PaymentStatus::COMPLETED) {
            return [
                'result' => [
                    'transaction' => (string) $payment->id,
                    'perform_time' => (int) ($payment->meta['perform_time'] ?? $payment->updated_at->getTimestampMs()),
                    'state' => 2,
                ],
            ];
        }

        if ($payment->status !== PaymentStatus::PROCESSING) {
            return $this->error(-31008, "Tranzaktsiya holati bajarishga ruxsat bermaydi", 'Transaction state does not allow perform', 'Состояние транзакции не позволяет выполнить');
        }

        // 12 soat timeout tekshirish
        $createTime = $payment->meta['create_time'] ?? $payment->created_at->getTimestampMs();
        if ((now()->getTimestampMs() - $createTime) > self::TIMEOUT_MS) {
            return $this->error(-31008, "Tranzaktsiya muddati tugagan", 'Transaction timed out', 'Транзакция просрочена');
        }

        $performTime = now()->getTimestampMs();

        $payment->update([
            'meta' => array_merge($payment->meta ?? [], [
                'perform_time' => $performTime,
            ]),
        ]);

        app(PaymentService::class)->complete($payment, $paymeId);

        return [
            'result' => [
                'transaction' => (string) $payment->id,
                'perform_time' => $performTime,
                'state' => 2,
            ],
        ];
    }

    // ─── CancelTransaction ───────────────────────────────────────────

    private function cancelTransaction(array $params): array
    {
        $paymeId = $params['id'] ?? null;
        $reason = $params['reason'] ?? null;

        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            return $this->error(-31003, 'Tranzaktsiya topilmadi', 'Transaction not found', 'Транзакция не найдена');
        }

        // Allaqachon bekor qilingan bo'lsa — idempotent javob
        if (in_array($payment->status, [PaymentStatus::CANCELLED, PaymentStatus::REFUNDED])) {
            $state = $payment->status === PaymentStatus::REFUNDED ? -2 : -1;
            return [
                'result' => [
                    'transaction' => (string) $payment->id,
                    'cancel_time' => (int) ($payment->meta['cancel_time'] ?? $payment->updated_at->getTimestampMs()),
                    'state' => $state,
                ],
            ];
        }

        $cancelTime = now()->getTimestampMs();
        $meta = array_merge($payment->meta ?? [], [
            'cancel_time' => $cancelTime,
            'cancel_reason' => $reason,
        ]);

        if ($payment->status === PaymentStatus::PROCESSING) {
            // Hali bajarilmagan — oddiy bekor qilish (state -1)
            $payment->update([
                'status' => PaymentStatus::CANCELLED,
                'meta' => $meta,
            ]);
            $state = -1;
        } elseif ($payment->status === PaymentStatus::COMPLETED) {
            // Bajarilgan — refund (state -2)
            $payment->update([
                'status' => PaymentStatus::REFUNDED,
                'meta' => $meta,
            ]);
            // TODO: Agar balans to'ldirilgan bo'lsa, qaytarish logikasi
            $state = -2;
        } else {
            return $this->error(-31008, "Tranzaktsiyani bekor qilib bo'lmaydi", 'Cannot cancel transaction', 'Невозможно отменить транзакцию');
        }

        return [
            'result' => [
                'transaction' => (string) $payment->id,
                'cancel_time' => $cancelTime,
                'state' => $state,
            ],
        ];
    }

    // ─── CheckTransaction ────────────────────────────────────────────

    private function checkTransaction(array $params): array
    {
        $paymeId = $params['id'] ?? null;
        $payment = Payment::where('external_id', $paymeId)->first();

        if (!$payment) {
            return $this->error(-31003, 'Tranzaktsiya topilmadi', 'Transaction not found', 'Транзакция не найдена');
        }

        $meta = $payment->meta ?? [];
        $state = $this->paymentToState($payment);

        return [
            'result' => [
                'create_time' => (int) ($meta['create_time'] ?? $payment->created_at->getTimestampMs()),
                'perform_time' => (int) ($meta['perform_time'] ?? 0),
                'cancel_time' => (int) ($meta['cancel_time'] ?? 0),
                'transaction' => (string) $payment->id,
                'state' => $state,
                'reason' => isset($meta['cancel_reason']) ? (int) $meta['cancel_reason'] : null,
            ],
        ];
    }

    // ─── GetStatement ────────────────────────────────────────────────

    private function getStatement(array $params): array
    {
        $from = $params['from'] ?? 0;
        $to = $params['to'] ?? 0;

        $payments = Payment::whereNotNull('external_id')
            ->where('method', 'payme')
            ->whereBetween('created_at', [
                \Carbon\Carbon::createFromTimestampMs($from),
                \Carbon\Carbon::createFromTimestampMs($to),
            ])
            ->orderBy('created_at')
            ->get();

        $transactions = $payments->map(function (Payment $payment) {
            $meta = $payment->meta ?? [];
            $state = $this->paymentToState($payment);

            return [
                'id' => $payment->external_id,
                'time' => (int) ($meta['payme_create_time'] ?? $payment->created_at->getTimestampMs()),
                'amount' => (int) ($payment->amount * 100),
                'account' => ['order_id' => (string) $payment->id],
                'create_time' => (int) ($meta['create_time'] ?? $payment->created_at->getTimestampMs()),
                'perform_time' => (int) ($meta['perform_time'] ?? 0),
                'cancel_time' => (int) ($meta['cancel_time'] ?? 0),
                'transaction' => (string) $payment->id,
                'state' => $state,
                'reason' => isset($meta['cancel_reason']) ? (int) $meta['cancel_reason'] : null,
            ];
        })->values()->toArray();

        return ['result' => ['transactions' => $transactions]];
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private function paymentToState(Payment $payment): int
    {
        return match ($payment->status) {
            PaymentStatus::PENDING, PaymentStatus::PROCESSING => 1,
            PaymentStatus::COMPLETED => 2,
            PaymentStatus::CANCELLED, PaymentStatus::FAILED => -1,
            PaymentStatus::REFUNDED => -2,
        };
    }

    private function transactionResult(Payment $payment, int $state): array
    {
        $meta = $payment->meta ?? [];

        return [
            'result' => [
                'create_time' => (int) ($meta['create_time'] ?? $payment->created_at->getTimestampMs()),
                'transaction' => (string) $payment->id,
                'state' => $state,
                'receivers' => null,
            ],
        ];
    }

    private function error(int $code, string $uz, string $en, string $ru, ?string $data = null): array
    {
        $error = [
            'code' => $code,
            'message' => [
                'uz' => $uz,
                'ru' => $ru,
                'en' => $en,
            ],
        ];

        if ($data !== null) {
            $error['data'] = $data;
        }

        return ['error' => $error];
    }
}
