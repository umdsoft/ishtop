<?php

namespace Tests\Unit\Services;

use App\Services\GeoService;
use Tests\TestCase;

class GeoServiceTest extends TestCase
{
    public function test_haversine_formula_returns_valid_sql(): void
    {
        $formula = GeoService::haversineFormula();

        $this->assertStringContainsString('6371', $formula);
        $this->assertStringContainsString('acos', $formula);
        $this->assertStringContainsString('latitude', $formula);
        $this->assertStringContainsString('longitude', $formula);
    }

    public function test_haversine_formula_uses_custom_columns(): void
    {
        $formula = GeoService::haversineFormula('lat', 'lng');

        $this->assertStringContainsString('lat', $formula);
        $this->assertStringContainsString('lng', $formula);
        $this->assertStringNotContainsString('latitude', $formula);
    }

    public function test_distance_between_same_point_is_zero(): void
    {
        $service = new GeoService();

        $distance = $service->distanceBetween(41.3111, 69.2797, 41.3111, 69.2797);

        $this->assertEquals(0.0, $distance);
    }

    public function test_distance_between_tashkent_and_samarkand(): void
    {
        $service = new GeoService();

        // Tashkent to Samarkand (~270 km)
        $distance = $service->distanceBetween(41.3111, 69.2797, 39.6547, 66.9597);

        $this->assertGreaterThan(200, $distance);
        $this->assertLessThan(350, $distance);
    }
}
