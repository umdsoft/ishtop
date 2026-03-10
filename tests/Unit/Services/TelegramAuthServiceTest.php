<?php

namespace Tests\Unit\Services;

use App\Services\TelegramAuthService;
use Tests\TestCase;

class TelegramAuthServiceTest extends TestCase
{
    private TelegramAuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TelegramAuthService();
    }

    public function test_validate_init_data_returns_false_without_bot_token(): void
    {
        config(['nutgram.token' => null]);
        $this->assertFalse($this->service->validateInitData('hash=abc&user=test'));
    }

    public function test_validate_init_data_returns_false_for_invalid_hash(): void
    {
        config(['nutgram.token' => 'test-bot-token-123']);
        $this->assertFalse($this->service->validateInitData('hash=invalid_hash&user={"id":123}'));
    }

    public function test_validate_init_data_returns_true_for_valid_hash(): void
    {
        $botToken = 'test-bot-token-for-validation';
        config(['nutgram.token' => $botToken]);

        // Build valid initData
        $data = [
            'user' => '{"id":123,"first_name":"Test"}',
            'auth_date' => '1678886400',
        ];

        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        $data['hash'] = $hash;
        $initData = http_build_query($data);

        $this->assertTrue($this->service->validateInitData($initData));
    }

    public function test_validate_widget_data_returns_false_without_bot_token(): void
    {
        config(['nutgram.token' => null]);
        $this->assertFalse($this->service->validateWidgetData([
            'id' => 123,
            'hash' => 'abc',
        ]));
    }

    public function test_validate_widget_data_returns_false_for_invalid_hash(): void
    {
        config(['nutgram.token' => 'test-bot-token-123']);
        $this->assertFalse($this->service->validateWidgetData([
            'id' => 123,
            'first_name' => 'Test',
            'auth_date' => 1678886400,
            'hash' => 'invalid_hash',
        ]));
    }

    public function test_validate_widget_data_returns_true_for_valid_hash(): void
    {
        $botToken = 'test-bot-token-for-widget';
        config(['nutgram.token' => $botToken]);

        $data = [
            'id' => 123,
            'first_name' => 'Test',
            'auth_date' => 1678886400,
        ];

        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        $data['hash'] = $hash;

        $this->assertTrue($this->service->validateWidgetData($data));
    }
}
