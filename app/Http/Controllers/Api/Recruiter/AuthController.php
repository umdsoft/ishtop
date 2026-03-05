<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class AuthController extends Controller
{
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

        // Ensure active employer is set
        if (!$user->active_employer_id) {
            $user->update(['active_employer_id' => $user->employerProfiles()->first()->id]);
            $user->refresh();
        }

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
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|regex:/^\+998\d{9}$/',
            'company_name' => 'required|string|max:300',
            'industry' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'telegram_id' => 0,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_verified' => true,
            'referral_code' => User::generateReferralCode(),
        ]);

        $employer = EmployerProfile::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'industry' => $request->industry,
            'phone' => $request->phone,
        ]);

        $user->update(['active_employer_id' => $employer->id]);

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

        // Auto-fix: ensure active_employer_id is set if profiles exist
        if (!$user->active_employer_id && $user->employerProfiles->isNotEmpty()) {
            $user->update(['active_employer_id' => $user->employerProfiles->first()->id]);
            $user->refresh();
            $user->load(['employerProfile', 'employerProfiles']);
        }

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

        $user = User::where('phone', $request->phone)
            ->orWhere('phone', $phone)
            ->orWhere('phone', '+' . $phone)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Bu raqam tizimda ro\'yxatdan o\'tmagan'], 404);
        }

        if (!$user->telegram_id || $user->telegram_id == 0) {
            return response()->json(['message' => 'Telegram bot ga ulanmagan. Avval @' . config('nutgram.username', 'ishtop_bot') . ' botga /start yuboring'], 422);
        }

        // Generate OTP
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("otp:{$phone}", $code, now()->addMinutes(5));
        Cache::put("otp_attempts:{$phone}", 0, now()->addMinutes(5));

        // Send OTP via Telegram bot
        try {
            $bot = app(Nutgram::class);
            $bot->sendMessage(
                text: "🔐 *IshTop Recruiter Panel*\n\nKirish kodi: `{$code}`\n\n⏱ Kod 5 daqiqa ichida amal qiladi.\n⚠️ Bu kodni hech kimga bermang!",
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

        // Check attempts
        $attempts = Cache::get("otp_attempts:{$phone}", 0);
        if ($attempts >= 5) {
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

        // Auto-create employer profile if missing
        if (!$user->employerProfiles()->exists()) {
            $employer = EmployerProfile::create([
                'user_id' => $user->id,
                'company_name' => $user->full_name . ' Company',
                'phone' => $user->phone,
            ]);
            $user->update(['active_employer_id' => $employer->id]);
            $user->refresh();
        }

        // Ensure active employer is set
        if (!$user->active_employer_id) {
            $user->update(['active_employer_id' => $user->employerProfiles()->first()->id]);
            $user->refresh();
        }

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

        if (!$this->verifyTelegramWidget($request->all())) {
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

            $employer = EmployerProfile::create([
                'user_id' => $user->id,
                'company_name' => $request->first_name . ' Company',
                'phone' => '',
            ]);

            $user->update(['active_employer_id' => $employer->id]);
            $user->refresh();
        }

        if (!$user->employerProfiles()->exists()) {
            $employer = EmployerProfile::create([
                'user_id' => $user->id,
                'company_name' => $user->first_name . ' Company',
                'phone' => '',
            ]);
            $user->update(['active_employer_id' => $employer->id]);
            $user->refresh();
        }

        // Ensure active employer is set
        if (!$user->active_employer_id) {
            $user->update(['active_employer_id' => $user->employerProfiles()->first()->id]);
            $user->refresh();
        }

        $token = $user->createToken('recruiter-telegram')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load(['employerProfile', 'employerProfiles']),
        ]);
    }

    protected function verifyTelegramWidget(array $data): bool
    {
        $botToken = config('nutgram.token');

        if (empty($botToken)) {
            return false;
        }

        $hash = $data['hash'];
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
