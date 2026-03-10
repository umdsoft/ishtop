<?php

namespace Tests\Feature\Api;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test')->plainTextToken;
        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_can_list_vacancies(): void
    {
        $user = User::factory()->create();
        Vacancy::factory()->count(5)->create(['status' => 'active', 'published_at' => now()]);

        $response = $this->getJson('/api/vacancies', $this->authHeaders($user));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title_uz', 'employer_id'],
                ],
            ]);
    }

    public function test_can_view_vacancy_detail(): void
    {
        $user = User::factory()->create();
        $vacancy = Vacancy::factory()->create(['status' => 'active', 'published_at' => now()]);

        $response = $this->getJson("/api/vacancies/{$vacancy->id}", $this->authHeaders($user));

        $response->assertStatus(200)
            ->assertJsonPath('vacancy.id', $vacancy->id);
    }

    public function test_can_create_vacancy(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $user->id]);
        $user->update(['active_employer_id' => $employer->id]);

        $response = $this->postJson('/api/vacancies', [
            'title_uz' => 'Test Vacancy',
            'category' => 'it',
            'description_uz' => 'Test description for the vacancy posting',
            'work_type' => 'full_time',
        ], $this->authHeaders($user));

        $response->assertStatus(201)
            ->assertJsonStructure(['vacancy' => ['id', 'title_uz']]);
    }

    public function test_can_search_vacancies(): void
    {
        // FULLTEXT MATCH...AGAINST is MySQL-only, skip on SQLite
        if (config('database.default') === 'sqlite') {
            $this->markTestSkipped('FULLTEXT search requires MySQL');
        }

        $user = User::factory()->create();
        Vacancy::factory()->create([
            'title_uz' => 'PHP Developer',
            'status' => 'active',
            'published_at' => now(),
        ]);

        $response = $this->getJson('/api/search/vacancies?q=PHP', $this->authHeaders($user));

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
