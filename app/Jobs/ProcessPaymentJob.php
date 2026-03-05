<?php

namespace App\Jobs;

use App\Events\PaymentCompleted;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Payment $payment,
        private ?string $externalId = null,
    ) {}

    public function handle(PaymentService $paymentService): void
    {
        $paymentService->complete($this->payment, $this->externalId);

        event(new PaymentCompleted($this->payment));

        SendNotificationJob::dispatch(
            $this->payment->user,
            'payment',
            'To\'lov muvaffaqiyatli!',
            "Sizning {$this->payment->amountFormatted()} miqdordagi to'lovingiz qabul qilindi.",
            ['payment_id' => $this->payment->id],
        );
    }
}
