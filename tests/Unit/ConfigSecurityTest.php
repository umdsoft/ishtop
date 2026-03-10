<?php

namespace Tests\Unit;

use Tests\TestCase;

class ConfigSecurityTest extends TestCase
{
    public function test_sanctum_token_has_expiration(): void
    {
        $expiration = config('sanctum.expiration');

        $this->assertNotNull($expiration, 'Sanctum token expiration null bo\'lmasligi kerak');
        $this->assertIsNumeric($expiration);
        $this->assertGreaterThan(0, $expiration);
    }

    public function test_nutgram_cache_is_not_array(): void
    {
        $cache = config('nutgram.cache');

        $this->assertNotEquals('array', $cache, 'Nutgram cache "array" bo\'lmasligi kerak — webhook rejimida conversation state yo\'qoladi');
    }

    public function test_click_secret_key_config_exists(): void
    {
        $this->assertArrayHasKey('secret_key', config('services.click'));
    }
}
