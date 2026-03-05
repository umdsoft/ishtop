<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_telegram(): void
    {
        $botToken = 'test_bot_token';
        config(['nutgram.token' => $botToken]);

        $userData = json_encode([
            'id' => 123456789,
            'first_name' => 'Test',
            'last_name' => 'User',
            'username' => 'testuser',
            'language_code' => 'uz',
        ]);

        $params = ['user' => $userData, 'auth_date' => (string) time()];
        ksort($params);
        $dataCheckString = collect($params)
            ->map(fn($value, $key) => "{$key}={$value}")
            ->implode("\n");
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
        $params['hash'] = $hash;

        $initData = http_build_query($params);

        $response = $this->postJson('/api/auth/telegram', [
            'init_data' => $initData,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'first_name',
                    'telegram_id',
                ],
            ]);
    }

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'first_name'],
                'active_subscription',
            ]);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/me', [
                'first_name' => 'Updated Name',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $user->fresh()->first_name);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Chiqildi']);
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
