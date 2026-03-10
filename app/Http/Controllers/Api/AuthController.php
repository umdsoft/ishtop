<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserAuthResource;
use App\Models\Category;
use App\Models\User;
use App\Models\Vacancy;
use App\Services\SmsService;
use App\Services\TelegramAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private SmsService $smsService,
        private TelegramAuthService $telegramAuth,
    ) {}

    /**
     * URL token orqali autentifikatsiya — initData ishlamagan holatlarda
     * Bot keyboard da encrypted token yuboradi
     */
    public function telegramToken(Request $request): JsonResponse
    {
        $request->validate(['token' => 'required|string']);

        try {
            $decrypted = decrypt($request->input('token'));

            // encrypt(telegram_id) formatda keladi
            $telegramId = $decrypted;

            $user = User::where('telegram_id', $telegramId)->first();
            if (!$user) {
                return response()->json(['message' => 'Foydalanuvchi topilmadi'], 404);
            }

            $user->update(['last_active_at' => now()]);

            if (!$user->referral_code) {
                $user->update(['referral_code' => User::generateReferralCode()]);
            }

            $token = $user->createToken('telegram-mini-app')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => new UserAuthResource($user),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Noto\'g\'ri token'], 401);
        }
    }

    public function telegram(Request $request): JsonResponse
    {
        $request->validate(['init_data' => 'required|string']);

        $initData = $request->input('init_data');

        if (app()->environment('production') && !$this->telegramAuth->validateInitData($initData)) {
            return response()->json(['message' => 'Noto\'g\'ri ma\'lumot'], 401);
        }

        parse_str($initData, $parsed);

        $userData = json_decode($parsed['user'] ?? '{}', true);

        if (empty($userData['id'])) {
            return response()->json(['message' => 'Foydalanuvchi ma\'lumoti topilmadi'], 422);
        }

        $user = User::where('telegram_id', $userData['id'])->first();

        if ($user) {
            // Existing user — update only name/username, NOT language
            $user->update([
                'first_name' => $userData['first_name'] ?? $user->first_name,
                'last_name' => $userData['last_name'] ?? $user->last_name,
                'username' => $userData['username'] ?? $user->username,
                'last_active_at' => now(),
            ]);
        } else {
            // New user — set language to 'uz' by default
            $user = User::create([
                'telegram_id' => $userData['id'],
                'first_name' => $userData['first_name'] ?? 'User',
                'last_name' => $userData['last_name'] ?? null,
                'username' => $userData['username'] ?? null,
                'language' => 'uz',
                'last_active_at' => now(),
            ]);
        }

        if (!$user->referral_code) {
            $user->update(['referral_code' => User::generateReferralCode()]);
        }

        $token = $user->createToken('telegram-mini-app')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserAuthResource($user),
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

    /**
     * Combined init endpoint — auth + bootstrap data in ONE request.
     * Saves 3-4 round trips on first load.
     */
    public function telegramInit(Request $request): JsonResponse
    {
        $request->validate(['init_data' => 'required|string']);

        $initData = $request->input('init_data');

        if (app()->environment('production') && !$this->telegramAuth->validateInitData($initData)) {
            return response()->json(['message' => 'Noto\'g\'ri ma\'lumot'], 401);
        }

        parse_str($initData, $parsed);
        $userData = json_decode($parsed['user'] ?? '{}', true);

        if (empty($userData['id'])) {
            return response()->json(['message' => 'Foydalanuvchi ma\'lumoti topilmadi'], 422);
        }

        $user = User::where('telegram_id', $userData['id'])->first();

        if ($user) {
            $user->update([
                'first_name' => $userData['first_name'] ?? $user->first_name,
                'last_name' => $userData['last_name'] ?? $user->last_name,
                'username' => $userData['username'] ?? $user->username,
                'last_active_at' => now(),
            ]);
        } else {
            $user = User::create([
                'telegram_id' => $userData['id'],
                'first_name' => $userData['first_name'] ?? 'User',
                'last_name' => $userData['last_name'] ?? null,
                'username' => $userData['username'] ?? null,
                'language' => 'uz',
                'last_active_at' => now(),
            ]);
        }

        if (!$user->referral_code) {
            $user->update(['referral_code' => User::generateReferralCode()]);
        }

        $token = $user->createToken('telegram-mini-app')->plainTextToken;

        // Bootstrap data — categories + latest vacancies in same response
        $categories = Category::active()
            ->root()
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'parent_id', 'name_uz', 'name_ru', 'icon', 'sort_order']);

        $vacancies = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('is_top')
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        // Load worker profile for client-side match score calculation
        $user->load(['workerProfile' => fn($q) => $q->select(
            'id', 'user_id', 'city', 'specialty',
            'expected_salary_min', 'expected_salary_max',
            'work_types', 'preferred_categories'
        )]);

        return response()->json([
            'token' => $token,
            'user' => new UserAuthResource($user),
            'categories' => $categories,
            'vacancies' => $vacancies,
        ]);
    }
}
