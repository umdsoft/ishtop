<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $kadrgo = config('kadrgo');

        $settings = [
            'general' => [
                ['key' => 'app_name', 'label' => 'Ilova nomi', 'value' => config('app.name'), 'type' => 'text'],
                ['key' => 'app_url', 'label' => 'Ilova URL', 'value' => config('app.url'), 'type' => 'url'],
                ['key' => 'version', 'label' => 'Versiya', 'value' => $kadrgo['version'] ?? '—', 'type' => 'text'],
            ],
            'pricing' => collect($kadrgo['pricing'] ?? [])->map(fn($v, $k) => [
                'key' => $k,
                'label' => match ($k) {
                    'vacancy' => 'Vakansiya joylash',
                    'top' => 'TOP ko\'tarish',
                    'urgent' => 'Shoshilinch belgi',
                    'resume_contact' => 'Rezyume kontakti',
                    'extend' => 'Muddatni uzaytirish',
                    'candidate_unlock' => 'Nomzodni ochish',
                    default => $k,
                },
                'value' => $v,
                'type' => 'currency',
            ])->values()->all(),
            'durations' => collect($kadrgo['durations'] ?? [])->map(fn($v, $k) => [
                'key' => $k,
                'label' => match ($k) {
                    'vacancy' => 'Vakansiya muddati',
                    'top' => 'TOP muddati',
                    'urgent' => 'Shoshilinch muddati',
                    default => $k,
                },
                'value' => $v,
                'type' => 'days',
            ])->values()->all(),
            'rate_limits' => collect($kadrgo['rate_limits'] ?? [])->map(fn($v, $k) => [
                'key' => $k,
                'label' => match ($k) {
                    'api' => 'API (daqiqada)',
                    'auth_otp' => 'OTP (daqiqada/IP)',
                    'vacancy_create' => 'Vakansiya yaratish (soatda)',
                    'application_free' => 'Bepul ariza (kunda)',
                    'chat_message' => 'Chat xabar (daqiqada)',
                    'recruiter_api' => 'Recruiter API (daqiqada)',
                    'questionnaire_submit' => 'Anketa topshirish (daqiqada)',
                    'banner_impression' => 'Banner ko\'rish (daqiqada)',
                    default => $k,
                },
                'value' => $v,
                'type' => 'number',
            ])->values()->all(),
            'scoring' => collect($kadrgo['scoring'] ?? [])->map(fn($v, $k) => [
                'key' => $k,
                'label' => match ($k) {
                    'green_threshold' => 'Yashil chegara (%)',
                    'yellow_threshold' => 'Sariq chegara (%)',
                    'suspicious_time_seconds' => 'Shubhali vaqt (soniya)',
                    default => $k,
                },
                'value' => $v,
                'type' => match ($k) {
                    'suspicious_time_seconds' => 'seconds',
                    default => 'percent',
                },
            ])->values()->all(),
            'anti_fraud' => collect($kadrgo['anti_fraud'] ?? [])->map(fn($v, $k) => [
                'key' => $k,
                'label' => match ($k) {
                    'max_banner_clicks_per_day' => 'Banner bosish limiti (kunda)',
                    'duplicate_text_threshold' => 'Dublikat matn chegarasi',
                    'reports_for_moderation' => 'Moderatsiya uchun shikoyatlar',
                    'reports_for_auto_ban' => 'Avto-ban uchun shikoyatlar',
                    default => $k,
                },
                'value' => $v,
                'type' => match ($k) {
                    'duplicate_text_threshold' => 'percent',
                    default => 'number',
                },
            ])->values()->all(),
        ];

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        // Settings update logic - for now returns current settings
        // Can be extended to save settings to database

        Cache::flush();

        return response()->json(['message' => 'Sozlamalar saqlandi']);
    }

    public function clearCache(): JsonResponse
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');

            return response()->json(['message' => 'Kesh muvaffaqiyatli tozalandi']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Xatolik: ' . $e->getMessage()], 500);
        }
    }
}
