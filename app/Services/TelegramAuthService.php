<?php

namespace App\Services;

class TelegramAuthService
{
    /**
     * Validate Telegram WebApp initData (used in both middleware and AuthController).
     */
    public function validateInitData(string $initData): bool
    {
        $botToken = config('nutgram.token');
        if (!$botToken) {
            return false;
        }

        parse_str($initData, $data);

        $hash = $data['hash'] ?? '';
        unset($data['hash']);

        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn($v, $k) => "{$k}={$v}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = bin2hex(hash_hmac('sha256', $dataCheckString, $secretKey, true));

        return hash_equals($calculatedHash, $hash);
    }

    /**
     * Validate Telegram Login Widget data (used in Recruiter auth).
     */
    public function validateWidgetData(array $data): bool
    {
        $botToken = config('nutgram.token');
        if (empty($botToken)) {
            return false;
        }

        $hash = $data['hash'] ?? '';
        unset($data['hash']);

        ksort($data);
        $dataCheckString = collect($data)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash('sha256', $botToken, true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }
}
