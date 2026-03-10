<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Services\TelegramAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class AuthController extends Controller
{
    public function __construct(private TelegramAuthService $telegramAuth) {}

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Login yoki parol noto\'g\'ri'], 401);
        }

        if (!$user->employerProfiles()->exists()) {
            return response()->json(['message' => 'Recruiter profili topilmadi'], 403);
        }

        $this->ensureActiveEmployer($user);

        $token = $user->createToken('recruiter-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['employerProfile', 'employerProfiles']),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'login' => 'required|string|min:3|max:50|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'password' => 'required|string|min:8',
            'phone' => 'required|string|regex:/^\+998\d{9}$/',
        ]);

        $user = User::create([
            'telegram_id' => null,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->login,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_verified' => true,
            'referral_code' => User::generateReferralCode(),
        ]);

        $this->ensureEmployerProfile($user);

        $token = $user->createToken('recruiter-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['employerProfile', 'employerProfiles']),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['employerProfile', 'employerProfiles', 'subscriptions' => function ($q) {
            $q->where('status', 'active')->where('expires_at', '>', now())->latest()->limit(1);
        }]);

        $this->ensureActiveEmployer($user);

        return response()->json([
            'user' => $user,
            'employer' => $user->employerProfile,
            'companies' => $user->employerProfiles,
            'active_subscription' => $user->activeSubscription(),
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+998\d{9}$/',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $ip = $request->ip();

        // IP bo'yicha OTP yuborish limiti — soatiga 10 ta
        $ipKey = "otp_send_ip:{$ip}";
        $ipSendCount = (int) Cache::get($ipKey, 0);
        if ($ipSendCount >= 10) {
            return response()->json(['message' => 'Juda ko\'p so\'rov. 1 soatdan keyin urinib ko\'ring'], 429);
        }

        // Telefon bo'yicha OTP yuborish limiti — 5 daqiqada 1 ta
        $phoneCooldown = Cache::get("otp_cooldown:{$phone}");
        if ($phoneCooldown) {
            return response()->json(['message' => 'Kod allaqachon yuborilgan. 2 daqiqadan keyin qayta so\'rang'], 429);
        }

        $user = User::where('phone', $request->phone)
            ->orWhere('phone', $phone)
            ->orWhere('phone', '+' . $phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Bu raqam tizimda ro\'yxatdan o\'tmagan'], 404);
        }

        if (!$user->telegram_id || $user->telegram_id == 0) {
            return response()->json(['message' => 'Telegram bot ga ulanmagan. Avval @' . config('nutgram.username', 'kadrgobot') . ' botga /start yuboring'], 422);
        }

        // Generate OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("otp:{$phone}", $code, now()->addMinutes(5));
        Cache::put("otp_attempts:{$phone}", 0, now()->addMinutes(5));
        Cache::put("otp_cooldown:{$phone}", true, now()->addMinutes(2));
        Cache::increment($ipKey);
        if ($ipSendCount === 0) {
            Cache::put($ipKey, 1, now()->addHour());
        }

        // Send OTP via Telegram bot
        try {
            $bot = app(Nutgram::class);
            $bot->sendMessage(
                text: "🔐 *KadrGo Recruiter Panel*\n\nKirish kodi: `{$code}`\n\n⏱ Kod 5 daqiqa ichida amal qiladi.\n⚠️ Bu kodni hech kimga bermang!",
                chat_id: $user->telegram_id,
                parse_mode: 'Markdown',
            );
        } catch (\Exception $e) {
            Log::error('Telegram OTP send failed: ' . $e->getMessage());
            return response()->json(['message' => 'Telegram orqali kod yuborib bo\'lmadi. Botga /start yuboring va qayta urinib ko\'ring'], 500);
        }

        // In dev mode, also return code
        $response = ['message' => 'Kod Telegram botga yuborildi'];
        if (!app()->environment('production')) {
            $response['code'] = $code;
        }

        return response()->json($response);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|regex:/^\+998\d{9}$/',
            'code' => 'required|string|size:6',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $request->phone);
        $ip = $request->ip();

        // IP bo'yicha tekshirish limiti — soatiga 20 ta
        $ipVerifyKey = "otp_verify_ip:{$ip}";
        $ipVerifyCount = (int) Cache::get($ipVerifyKey, 0);
        if ($ipVerifyCount >= 20) {
            return response()->json(['message' => 'Juda ko\'p urinish. 1 soatdan keyin urinib ko\'ring'], 429);
        }
        Cache::increment($ipVerifyKey);
        if ($ipVerifyCount === 0) {
            Cache::put($ipVerifyKey, 1, now()->addHour());
        }

        // Telefon bo'yicha urinishlar soni
        $attempts = Cache::get("otp_attempts:{$phone}", 0);
        if ($attempts >= 5) {
            Cache::forget("otp:{$phone}");
            return response()->json(['message' => 'Urinishlar soni tugadi. Qaytadan kod so\'rang'], 429);
        }

        Cache::increment("otp_attempts:{$phone}");

        $storedCode = Cache::get("otp:{$phone}");
        if (!$storedCode || $storedCode !== $request->code) {
            $remaining = 4 - $attempts;
            return response()->json(['message' => "Kod noto'g'ri. {$remaining} ta urinish qoldi"], 422);
        }

        // Clear OTP
        Cache::forget("otp:{$phone}");
        Cache::forget("otp_attempts:{$phone}");

        // Find user and create token
        $user = User::where('phone', $request->phone)
            ->orWhere('phone', $phone)
            ->orWhere('phone', '+' . $phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Foydalanuvchi topilmadi'], 404);
        }

        $this->ensureEmployerProfile($user);
        $this->ensureActiveEmployer($user);

        $token = $user->createToken('recruiter-otp')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['employerProfile', 'employerProfiles']),
        ]);
    }

    public function telegramBotInfo(): JsonResponse
    {
        return response()->json([
            'bot_username' => config('nutgram.username', ''),
        ]);
    }

    public function telegramLogin(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|integer',
            'first_name' => 'required|string',
            'auth_date' => 'required|integer',
            'hash' => 'required|string',
        ]);

        if (!$this->telegramAuth->validateWidgetData($request->all())) {
            return response()->json(['message' => 'Telegram ma\'lumotlari noto\'g\'ri'], 401);
        }

        // Check auth_date is not too old (1 day)
        if (time() - $request->auth_date > 86400) {
            return response()->json(['message' => 'Telegram sessiya muddati o\'tgan'], 401);
        }

        $user = User::where('telegram_id', $request->id)->first();

        if (!$user) {
            $user = User::create([
                'telegram_id' => $request->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $request->username,
                'is_verified' => true,
            ]);
        }

        $this->ensureEmployerProfile($user);
        $this->ensureActiveEmployer($user);

        $token = $user->createToken('recruiter-telegram')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['employerProfile', 'employerProfiles']),
        ]);
    }

    /**
     * Auto-create employer profile if user doesn't have one.
     */
    private function ensureEmployerProfile(User $user): void
    {
        if ($user->employerProfiles()->exists()) {
            return;
        }

        $employer = EmployerProfile::create([
            'user_id' => $user->id,
            'company_name' => ($user->first_name ?: 'User') . ' Company',
            'phone' => $user->phone ?? '',
        ]);

        $user->update(['active_employer_id' => $employer->id]);
        $user->refresh();
    }

    /**
     * Ensure active_employer_id is set if profiles exist.
     */
    private function ensureActiveEmployer(User $user): void
    {
        if ($user->active_employer_id) {
            return;
        }

        $firstEmployer = $user->employerProfiles()->first();
        if ($firstEmployer) {
            $user->update(['active_employer_id' => $firstEmployer->id]);
            $user->refresh();
            $user->load(['employerProfile', 'employerProfiles']);
        }
    }
}
