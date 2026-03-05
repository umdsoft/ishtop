<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private SmsService $smsService) {}

    public function telegram(Request $request): JsonResponse
    {
        $request->validate(['init_data' => 'required|string']);

        $initData = $request->input('init_data');

        if (!$this->verifyTelegramData($initData)) {
            return response()->json(['message' => 'Noto\'g\'ri ma\'lumot'], 401);
        }

        parse_str($initData, $parsed);

        $userData = json_decode($parsed['user'] ?? '{}', true);

        if (empty($userData['id'])) {
            return response()->json(['message' => 'Foydalanuvchi ma\'lumoti topilmadi'], 422);
        }

        $user = User::updateOrCreate(
            ['telegram_id' => $userData['id']],
            [
                'first_name' => $userData['first_name'] ?? 'User',
                'last_name' => $userData['last_name'] ?? null,
                'username' => $userData['username'] ?? null,
                'language' => $userData['language_code'] ?? 'uz',
                'last_active_at' => now(),
            ]
        );

        if (!$user->referral_code) {
            $user->update(['referral_code' => User::generateReferralCode()]);
        }

        $token = $user->createToken('telegram-mini-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'telegram_id' => $user->telegram_id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'username' => $user->username,
                'language' => $user->language,
                'is_verified' => $user->is_verified,
                'has_worker_profile' => $user->workerProfile()->exists(),
                'has_employer_profile' => $user->employerProfile()->exists(),
                'balance' => $user->balance,
            ],
        ]);
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string|regex:/^\+998\d{9}$/']);

        $code = $this->smsService->sendOtp($request->phone);

        $response = ['message' => 'OTP yuborildi'];

        if (!app()->environment('production')) {
            $response['code'] = $code;
        }

        return response()->json($response);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        if (!$this->smsService->verifyOtp($request->phone, $request->code)) {
            return response()->json(['message' => 'Kod noto\'g\'ri'], 422);
        }

        $user = $request->user();
        if ($user) {
            $user->update([
                'phone' => $request->phone,
                'is_verified' => true,
            ]);
        }

        return response()->json([
            'message' => 'Telefon tasdiqlandi',
            'verified' => true,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['workerProfile', 'employerProfile', 'subscriptions' => function ($q) {
            $q->where('status', 'active')->where('expires_at', '>', now())->latest()->limit(1);
        }]);

        return response()->json([
            'user' => $user,
            'active_subscription' => $user->activeSubscription(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'language' => 'sometimes|in:uz,ru',
        ]);

        $user = $request->user();
        $user->update($request->only(['first_name', 'last_name', 'language']));

        return response()->json(['user' => $user->fresh()]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Chiqildi']);
    }

    protected function verifyTelegramData(string $initData): bool
    {
        $botToken = config('nutgram.token');

        if (empty($botToken)) {
            return false;
        }

        parse_str($initData, $parsed);

        if (!isset($parsed['hash'])) {
            return false;
        }

        $hash = $parsed['hash'];
        unset($parsed['hash']);

        ksort($parsed);

        $dataCheckString = collect($parsed)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode("\n");

        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($calculatedHash, $hash);
    }
}
