<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $regionNames = DB::table('cities')
            ->whereNotNull('region')
            ->pluck('region')
            ->unique()
            ->values()
            ->all();

        if (empty($regionNames)) {
            return;
        }

        // Case 1: city = shahar nomi, district = viloyat nomi → swap
        // Example: city="Urganch", district="Xorazm viloyati" → city="Xorazm viloyati", district="Urganch"
        DB::table('worker_profiles')
            ->whereNotNull('city')
            ->whereNotNull('district')
            ->whereNotIn('city', $regionNames)
            ->whereIn('district', $regionNames)
            ->orderBy('id')
            ->chunk(200, function ($profiles) {
                foreach ($profiles as $profile) {
                    DB::table('worker_profiles')
                        ->where('id', $profile->id)
                        ->update([
                            'city' => $profile->district,
                            'district' => $profile->city,
                        ]);
                }
            });

        // Case 2: city = shahar nomi, district = null → look up region, set city=region, district=shahar
        // Example: city="Urganch", district=null → city="Xorazm viloyati", district="Urganch"
        $cityToRegion = DB::table('cities')
            ->whereNotNull('region')
            ->pluck('region', 'name_uz')
            ->all();

        $cityToRegionRu = DB::table('cities')
            ->whereNotNull('region')
            ->pluck('region', 'name_ru')
            ->all();

        DB::table('worker_profiles')
            ->whereNotNull('city')
            ->whereNotIn('city', $regionNames)
            ->where(function ($q) {
                $q->whereNull('district')
                  ->orWhere('district', '');
            })
            ->orderBy('id')
            ->chunk(200, function ($profiles) use ($cityToRegion, $cityToRegionRu) {
                foreach ($profiles as $profile) {
                    $region = $cityToRegion[$profile->city]
                           ?? $cityToRegionRu[$profile->city]
                           ?? null;

                    if ($region) {
                        DB::table('worker_profiles')
                            ->where('id', $profile->id)
                            ->update([
                                'city' => $region,
                                'district' => $profile->city,
                            ]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Reverse swap is the same operation — swap city and district back
        // Only for profiles where city is now a region and district is a city name
    }
};
