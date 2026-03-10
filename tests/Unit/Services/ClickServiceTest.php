<?php

namespace Tests\Unit\Services;

use App\Models\Payment;
use App\Services\ClickService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClickServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_prepare_rejects_invalid_signature(): void
    {
        config(['services.click.secret_key' => 'test_secret_key']);

        $service = new ClickService();

        $request = new Request([
            'click_trans_id' => '12345',
            'service_id' => '1',
            'merchant_trans_id' => 'fake-id',
            'amount' => '100000',
            'action' => '0',
            'sign_time' => '2026-03-08 12:00:00',
            'sign_string' => 'invalid_sign_string',
        ]);

        $result = $service->handlePrepare($request);

        $this->assertEquals(-1, $result['error']);
        $this->assertEquals('Invalid signature', $result['error_note']);
    }

    public function test_complete_rejects_invalid_signature(): void
    {
        config(['services.click.secret_key' => 'test_secret_key']);

        $service = new ClickService();

        $request = new Request([
            'click_trans_id' => '12345',
            'service_id' => '1',
            'merchant_prepare_id' => 'fake-id',
            'amount' => '100000',
            'action' => '1',
            'sign_time' => '2026-03-08 12:00:00',
            'sign_string' => 'invalid_sign_string',
            'error' => 0,
        ]);

        $result = $service->handleComplete($request);

        $this->assertEquals(-1, $result['error']);
    }

    public function test_prepare_accepts_valid_signature(): void
    {
        config(['services.click.secret_key' => 'test_secret_key']);

        $payment = Payment::factory()->create([
            'amount' => 35000,
            'status' => 'pending',
        ]);

        $service = new ClickService();

        $clickTransId = '12345';
        $serviceId = '1';
        $merchantTransId = $payment->id;
        $amount = '35000';
        $action = '0';
        $signTime = '2026-03-08 12:00:00';

        $signString = md5(
            $clickTransId . $serviceId . 'test_secret_key' .
            $merchantTransId . $amount . $action . $signTime
        );

        $request = new Request([
            'click_trans_id' => $clickTransId,
            'service_id' => $serviceId,
            'merchant_trans_id' => $merchantTransId,
            'amount' => $amount,
            'action' => $action,
            'sign_time' => $signTime,
            'sign_string' => $signString,
        ]);

        $result = $service->handlePrepare($request);

        $this->assertEquals(0, $result['error']);
        $this->assertEquals('Success', $result['error_note']);
    }

    public function test_prepare_rejects_when_no_secret_key_configured(): void
    {
        config(['services.click.secret_key' => '']);

        $service = new ClickService();

        $request = new Request([
            'click_trans_id' => '12345',
            'sign_string' => 'anything',
        ]);

        $result = $service->handlePrepare($request);

        $this->assertEquals(-1, $result['error']);
    }
}
