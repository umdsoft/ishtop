<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\TelegramAuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class TelegramWebAppAuth
{
    public function __construct(private TelegramAuthService $telegramAuth) {}

    public function handle(Request $request, Closure $next): Response
    {
        // 1) Bearer token — PersonalAccessToken orqali to'g'ridan-to'g'ri tekshirish
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $accessToken = PersonalAccessToken::findToken($bearerToken);
            if ($accessToken) {
                $user = $accessToken->tokenable;
                if ($user) {
                    $user->withAccessToken($accessToken);
                    auth()->setUser($user);
                    return $next($request);
                }
            }
        }

        // 2) Fallback: X-Telegram-Init-Data header orqali auth
        $initData = $request->header('X-Telegram-Init-Data');
        if ($initData) {
            $user = $this->resolveUserFromInitData($initData);
            if ($user) {
                auth()->setUser($user);
                return $next($request);
            }
        }

        Log::warning('TelegramWebAppAuth: authentication failed', [
            'has_bearer' => !empty($bearerToken),
            'has_init_data' => !empty($initData),
            'init_data_length' => strlen($initData ?? ''),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
        ]);

        return response()->json(['message' => 'Unauthenticated.'], 401);
    }

    private function resolveUserFromInitData(string $initData): ?User
    {
        // Barcha muhitlarda hash tekshirish (dev bypass xavfli!)
        if (!$this->telegramAuth->validateInitData($initData)) {
            Log::warning('TelegramWebAppAuth: initData validation failed');
            return null;
        }

        parse_str($initData, $parsed);

        // auth_date vaqtini tekshirish (maks 24 soat)
        $authDate = (int) ($parsed['auth_date'] ?? 0);
        if ($authDate > 0 && (time() - $authDate) > 86400) {
            Log::warning('TelegramWebAppAuth: initData expired', ['auth_date' => $authDate]);
            return null;
        }

        $userData = json_decode($parsed['user'] ?? '{}', true);

        if (empty($userData['id'])) {
            Log::warning('TelegramWebAppAuth: no user id in initData');
            return null;
        }

        $user = User::where('telegram_id', $userData['id'])->first();

        if (!$user) {
            $user = User::create([
                'telegram_id' => $userData['id'],
                'first_name' => $userData['first_name'] ?? 'User',
                'last_name' => $userData['last_name'] ?? null,
                'username' => $userData['username'] ?? null,
                'language' => 'uz',
                'last_active_at' => now(),
            ]);
        }

        return $user;
    }
}
