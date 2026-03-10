<?php

namespace Tests\Feature\Api\Security;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileValidationTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedRequest(User $user): static
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    public function test_worker_update_validates_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/worker', [
                'full_name' => str_repeat('a', 300),
                'experience_years' => -5,
                'latitude' => 999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['full_name', 'experience_years', 'latitude']);
    }

    public function test_worker_update_accepts_valid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/worker', [
                'full_name' => 'Alisher Navoiy',
                'experience_years' => 5,
                'city' => 'Toshkent',
                'skills' => ['PHP', 'Laravel', 'Vue.js'],
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('profile.full_name', 'Alisher Navoiy');
    }

    public function test_employer_update_validates_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/employer', [
                'company_name' => str_repeat('a', 300),
                'website' => 'not-a-url',
                'latitude' => 999,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['company_name', 'website', 'latitude']);
    }

    public function test_search_status_update_requires_worker_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/search-status', [
                'status' => 'open',
            ]);

        $response->assertStatus(404);
    }

    public function test_search_status_validates_values(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/search-status', [
                'status' => 'invalid_status',
            ]);

        $response->assertStatus(422);
    }

    public function test_search_status_update_works_with_profile(): void
    {
        $user = User::factory()->create();
        WorkerProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->authenticatedRequest($user)
            ->putJson('/api/profile/search-status', [
                'status' => 'passive',
            ]);

        $response->assertStatus(200);
    }
}
