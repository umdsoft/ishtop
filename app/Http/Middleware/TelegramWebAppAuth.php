<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebAppAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $initData = $request->header('X-Telegram-Init-Data');

        if (!$initData) {
            return response()->json(['message' => 'Telegram auth kerak'], 401);
        }

        if (!$this->validateInitData($initData)) {
            return response()->json(['message' => 'Noto\'g\'ri auth ma\'lumotlar'], 401);
        }

        return $next($request);
    }

    private function validateInitData(string $initData): bool
    {
        $botToken = config('nutgram.token');
        if (!$botToken) return false;

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
}
