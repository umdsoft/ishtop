<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Viloyat nomlarini standartlashtirish:
     * DB: "Xorazm" → "Xorazm viloyati" (regions.js bilan bir xil)
     *
     * cities.region, vacancies.city, worker_profiles.city — hammasi yangilanadi
     */
    public function up(): void
    {
        $map = [
            'Toshkent'        => 'Toshkent shahri',
            'Andijon'         => 'Andijon viloyati',
            'Buxoro'          => 'Buxoro viloyati',
            "Farg'ona"        => "Farg'ona viloyati",
            'Namangan'        => 'Namangan viloyati',
            'Samarqand'       => 'Samarqand viloyati',
            'Qashqadaryo'     => 'Qashqadaryo viloyati',
            'Surxondaryo'     => 'Surxondaryo viloyati',
            'Jizzax'          => 'Jizzax viloyati',
            'Navoiy'          => 'Navoiy viloyati',
            'Sirdaryo'        => 'Sirdaryo viloyati',
            'Xorazm'          => 'Xorazm viloyati',
            "Qoraqalpog'iston" => "Qoraqalpog'iston Respublikasi",
            // Toshkent viloyati → o'zgarmaydi
        ];

        foreach ($map as $old => $new) {
            // cities.region
            DB::table('cities')
                ->where('region', $old)
                ->update(['region' => $new]);

            // vacancies.city (ayrim vakansiyalar region nomi saqlagan)
            DB::table('vacancies')
                ->where('city', $old)
                ->update(['city' => $new]);

            // worker_profiles.city
            DB::table('worker_profiles')
                ->where('city', $old)
                ->update(['city' => $new]);
        }
    }

    public function down(): void
    {
        $map = [
            'Toshkent shahri'              => 'Toshkent',
            'Andijon viloyati'             => 'Andijon',
            'Buxoro viloyati'              => 'Buxoro',
            "Farg'ona viloyati"            => "Farg'ona",
            'Namangan viloyati'            => 'Namangan',
            'Samarqand viloyati'           => 'Samarqand',
            'Qashqadaryo viloyati'         => 'Qashqadaryo',
            'Surxondaryo viloyati'         => 'Surxondaryo',
            'Jizzax viloyati'              => 'Jizzax',
            'Navoiy viloyati'              => 'Navoiy',
            'Sirdaryo viloyati'            => 'Sirdaryo',
            'Xorazm viloyati'              => 'Xorazm',
            "Qoraqalpog'iston Respublikasi" => "Qoraqalpog'iston",
        ];

        foreach ($map as $old => $new) {
            DB::table('cities')->where('region', $old)->update(['region' => $new]);
            DB::table('vacancies')->where('city', $old)->update(['city' => $new]);
            DB::table('worker_profiles')->where('city', $old)->update(['city' => $new]);
        }
    }
};
