<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service - Eskiz.uz integration
 * Handles OTP sending and SMS operations
 */
class SmsService
{
    protected string $apiUrl;
    protected string $email;
    protected string $password;
    protected string $from;

    public function __construct()
    {
        $this->apiUrl = 'https://notify.eskiz.uz/api';
        $this->email = config('services.sms.email') ?? '';
        $this->password = config('services.sms.password') ?? '';
        $this->from = config('services.sms.from') ?? '4546';
    }

    /**
     * Generate and send OTP code
     */
    public function sendOtp(string $phone): string
    {
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Store OTP with attempts counter
        $normalizedPhone = $this->normalizePhone($phone);
        Cache::put("otp:{$normalizedPhone}", $code, now()->addMinutes(5));
        Cache::put("otp_attempts:{$normalizedPhone}", 0, now()->addMinutes(5));

        // Send SMS
        $message = "KadrGo tasdiqlash kodi: {$code}\n\nBu kodni hech kimga bermang!";

        if (app()->environment('production')) {
            $this->send($phone, $message);
        } else {
            Log::info("SMS OTP (dev mode): {$phone} => {$code}");
        }

        return $code;
    }

    /**
     * Verify OTP code with attempt limiting
     */
    public function verifyOtp(string $phone, string $code): bool
    {
        $normalizedPhone = $this->normalizePhone($phone);

        // Check attempts limit (5 max)
        $attempts = Cache::get("otp_attempts:{$normalizedPhone}", 0);
        if ($attempts >= 5) {
            return false;
        }

        // Increment attempts
        Cache::increment("otp_attempts:{$normalizedPhone}");

        // Verify code
        $storedCode = Cache::get("otp:{$normalizedPhone}");
        if ($storedCode && $storedCode === $code) {
            // Clear OTP data after successful verification
            Cache::forget("otp:{$normalizedPhone}");
            Cache::forget("otp_attempts:{$normalizedPhone}");
            return true;
        }

        return false;
    }

    /**
     * Send SMS message via Eskiz.uz
     */
    public function send(string $phone, string $message): bool
    {
        try {
            $normalizedPhone = $this->normalizePhone($phone);

            // Get auth token (cached for 29 days)
            $token = $this->getAuthToken();

            if (!$token) {
                Log::error('SMS Service: Failed to get auth token');
                return false;
            }

            // Send SMS request
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
            ])->post("{$this->apiUrl}/message/sms/send", [
                'mobile_phone' => $normalizedPhone,
                'message' => $message,
                'from' => $this->from,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['status']) && $data['status'] === 'success') {
                    Log::info("SMS sent successfully to {$normalizedPhone}");
                    return true;
                }
            }

            Log::error('SMS Service: Failed to send SMS', [
                'phone' => $normalizedPhone,
                'response' => $response->json(),
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('SMS Service Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send application status notification
     */
    public function sendApplicationStatus(string $phone, string $vacancyTitle, string $status): bool
    {
        $messages = [
            'new' => "KadrGo: Arizangiz qabul qilindi!\nVakansiya: {$vacancyTitle}",
            'under_review' => "KadrGo: Arizangiz ko'rib chiqilmoqda.\nVakansiya: {$vacancyTitle}",
            'shortlisted' => "KadrGo: Siz qisqa ro'yxatga kirgansiz! 🎉\nVakansiya: {$vacancyTitle}",
            'interview' => "KadrGo: Siz intervyuga taklif qilindingiz! 🎯\nVakansiya: {$vacancyTitle}",
            'rejected' => "KadrGo: Afsuski, bu safar mos kelmadingiz.\nVakansiya: {$vacancyTitle}",
            'accepted' => "KadrGo: Tabriklaymiz! Siz ishga qabul qilindingiz! 🎊\nVakansiya: {$vacancyTitle}",
        ];

        $message = $messages[$status] ?? "KadrGo: Ariza holati o'zgartirildi.\nVakansiya: {$vacancyTitle}";

        return $this->send($phone, $message);
    }

    /**
     * Send payment confirmation SMS
     */
    public function sendPaymentConfirmation(string $phone, float $amount, string $type): bool
    {
        $types = [
            'balance_topup' => 'Balans to\'ldirildi',
            'subscription' => 'Obuna faollashtirildi',
            'vacancy_top' => 'Vakansiya TOP qilindi',
            'vacancy_urgent' => 'Vakansiya СРОЧНО qilindi',
        ];

        $typeLabel = $types[$type] ?? 'To\'lov';
        $message = "KadrGo: {$typeLabel}\nSumma: " . number_format($amount, 0, ',', ' ') . " so'm\n\nRahmat!";

        return $this->send($phone, $message);
    }

    /**
     * Get authentication token from Eskiz API
     */
    protected function getAuthToken(): ?string
    {
        // Check cached token
        $cached = Cache::get('eskiz_auth_token');
        if ($cached) {
            return $cached;
        }

        // Return null if credentials not configured
        if (empty($this->email) || empty($this->password)) {
            return null;
        }

        try {
            $response = Http::post("{$this->apiUrl}/auth/login", [
                'email' => $this->email,
                'password' => $this->password,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['data']['token'])) {
                    $token = $data['data']['token'];

                    // Cache for 29 days (Eskiz tokens expire in 30 days)
                    Cache::put('eskiz_auth_token', $token, now()->addDays(29));

                    return $token;
                }
            }

            Log::error('SMS Auth failed', ['response' => $response->json()]);
            return null;

        } catch (\Exception $e) {
            Log::error('SMS Auth Exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Normalize phone number to 998XXXXXXXXX format
     */
    protected function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add country code if not present
        if (!str_starts_with($phone, '998')) {
            // Remove leading 0 if present
            $phone = ltrim($phone, '0');
            $phone = '998' . $phone;
        }

        return $phone;
    }
}
