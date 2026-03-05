<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\Notification as NotificationModel;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification
{
    public function __construct(
        public Payment $payment,
        public string $status
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'payment_' . $this->status,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_type' => $this->payment->type,
            'title' => $this->getTitle(),
            'message' => $this->getMessage(),
        ];
    }

    private function getTitle(): string
    {
        return match ($this->status) {
            'completed' => 'To\'lov muvaffaqiyatli',
            'failed' => 'To\'lov amalga oshmadi',
            'pending' => 'To\'lov kutilmoqda',
            default => 'To\'lov holati',
        };
    }

    private function getMessage(): string
    {
        $amount = number_format($this->payment->amount, 0, '.', ' ');

        return match ($this->status) {
            'completed' => "{$amount} so'm to'lov muvaffaqiyatli amalga oshirildi",
            'failed' => "{$amount} so'm to'lov amalga oshmadi. Qayta urinib ko'ring",
            'pending' => "{$amount} so'm to'lov kutilmoqda",
            default => "To'lov holati o'zgarди: {$this->status}",
        };
    }

    /**
     * Create custom notification in notifications table
     */
    public static function createNotification(Payment $payment, string $status): void
    {
        $amount = number_format($payment->amount, 0, '.', ' ');

        $titles = [
            'completed' => 'To\'lov muvaffaqiyatli',
            'failed' => 'To\'lov amalga oshmadi',
            'pending' => 'To\'lov kutilmoqda',
        ];

        $messages = [
            'completed' => "{$amount} so'm to'lov muvaffaqiyatli amalga oshirildi",
            'failed' => "{$amount} so'm to'lov amalga oshmadi. Qayta urinib ko'ring",
            'pending' => "{$amount} so'm to'lov kutilmoqda",
        ];

        NotificationModel::create([
            'user_id' => $payment->user_id,
            'type' => 'payment_' . $status,
            'title' => $titles[$status] ?? 'To\'lov holati',
            'message' => $messages[$status] ?? "To'lov holati o'zgarди",
            'data' => [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'type' => $payment->type,
                'status' => $status,
            ],
        ]);
    }
}
