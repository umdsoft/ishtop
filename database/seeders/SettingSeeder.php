<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Narxlar
            ['key' => 'vacancy_price', 'value' => '35000', 'group' => 'pricing'],
            ['key' => 'top_price', 'value' => '15000', 'group' => 'pricing'],
            ['key' => 'urgent_price', 'value' => '10000', 'group' => 'pricing'],
            ['key' => 'resume_contact_price', 'value' => '7000', 'group' => 'pricing'],
            ['key' => 'extend_price', 'value' => '15000', 'group' => 'pricing'],
            // E'lon sozlamalari
            ['key' => 'vacancy_duration_days', 'value' => '15', 'group' => 'vacancy'],
            ['key' => 'top_duration_days', 'value' => '7', 'group' => 'vacancy'],
            ['key' => 'urgent_duration_days', 'value' => '3', 'group' => 'vacancy'],
            ['key' => 'max_vacancies_new_employer', 'value' => '1', 'group' => 'vacancy'],
            // Rate limiting
            ['key' => 'max_applications_free', 'value' => '30', 'group' => 'limits'],
            ['key' => 'max_daily_notifications', 'value' => '5', 'group' => 'limits'],
            // Tizim
            ['key' => 'maintenance_mode', 'value' => 'false', 'group' => 'system'],
            ['key' => 'min_topup_amount', 'value' => '10000', 'group' => 'system'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
