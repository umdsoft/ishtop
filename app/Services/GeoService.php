<?php

namespace App\Services;

use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Database\Eloquent\Builder;

class GeoService
{
    /**
     * Haversine formula for MySQL (PostGIS ST_DWithin replacement).
     * Public static so models can reuse without duplicating the formula.
     */
    public static function haversineFormula(string $latCol = 'latitude', string $lngCol = 'longitude'): string
    {
        return "(6371 * acos(cos(radians(?)) * cos(radians({$latCol})) * cos(radians({$lngCol}) - radians(?)) + sin(radians(?)) * sin(radians({$latCol}))))";
    }

    public function nearbyVacancies(float $lat, float $lng, int $radiusKm = 10): Builder
    {
        $haversine = self::haversineFormula();

        return Vacancy::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm])
            ->selectRaw("vacancies.*, {$haversine} as distance_km", [$lat, $lng, $lat])
            ->orderBy('distance_km');
    }

    public function nearbyWorkers(float $lat, float $lng, int $radiusKm = 10): Builder
    {
        $haversine = self::haversineFormula();

        return WorkerProfile::active()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm])
            ->selectRaw("worker_profiles.*, {$haversine} as distance_km", [$lat, $lng, $lat])
            ->orderBy('distance_km');
    }

    public function distanceBetween(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
