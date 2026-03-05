<?php

namespace Tests\Unit\Services;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    public function test_can_create_payment(): void
    {
        $user = User::factory()->create();

        $payment = $this->paymentService->create($user, [
            'type' => 'balance_topup',
            'amount' => 10000,
            'method' => 'payme',
        ]);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(PaymentStatus::PENDING, $payment->status);
        $this->assertEquals(10000, $payment->amount);
    }

    public function test_can_complete_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::PENDING,
            'type' => 'balance_topup',
            'amount' => 10000,
        ]);

        $initialBalance = $user->balance;

        $this->paymentService->complete($payment, 'external_123');

        $this->assertEquals(PaymentStatus::COMPLETED, $payment->fresh()->status);
        $this->assertEquals('external_123', $payment->fresh()->external_id);

        // Check balance updated for balance_topup
        $this->assertEquals($initialBalance + 10000, $user->fresh()->balance);
    }

    public function test_can_fail_payment(): void
    {
        $user = User::factory()->create();
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::PENDING,
        ]);

        $this->paymentService->fail($payment);

        $this->assertEquals(PaymentStatus::FAILED, $payment->fresh()->status);
    }

    public function test_can_pay_with_balance(): void
    {
        $user = User::factory()->create(['balance' => 50000]);
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::PENDING,
            'amount' => 10000,
        ]);

        $result = $this->paymentService->payWithBalance($user, $payment);

        $this->assertTrue($result);
        $this->assertEquals(40000, $user->fresh()->balance);
        $this->assertEquals(PaymentStatus::COMPLETED, $payment->fresh()->status);
    }

    public function test_cannot_pay_with_insufficient_balance(): void
    {
        $user = User::factory()->create(['balance' => 5000]);
        $payment = Payment::factory()->create([
            'user_id' => $user->id,
            'status' => PaymentStatus::PENDING,
            'amount' => 10000,
        ]);

        $result = $this->paymentService->payWithBalance($user, $payment);

        $this->assertFalse($result);
        $this->assertEquals(5000, $user->fresh()->balance);
        $this->assertEquals(PaymentStatus::PENDING, $payment->fresh()->status);
    }
}
