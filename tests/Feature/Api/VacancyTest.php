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

    public function test_can_list_vacancies(): void
    {
        Vacancy::factory()->count(5)->create(['status' => 'active']);

        $response = $this->getJson('/api/vacancies');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'employer', 'city', 'salary_min'],
                ],
            ]);
    }

    public function test_can_view_vacancy_detail(): void
    {
        $vacancy = Vacancy::factory()->create(['status' => 'active']);

        $response = $this->getJson("/api/vacancies/{$vacancy->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $vacancy->id,
                'title' => $vacancy->title,
            ]);
    }

    public function test_can_create_vacancy(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/vacancies', [
                'title' => 'Test Vacancy',
                'category' => 'it',
                'description' => 'Test description',
                'work_type' => 'full_time',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['vacancy' => ['id', 'title']]);
    }

    public function test_can_search_vacancies(): void
    {
        Vacancy::factory()->create([
            'title' => 'PHP Developer',
            'status' => 'active',
        ]);

        $response = $this->getJson('/api/search/vacancies?q=PHP');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'vacancies' => [
                    '*' => ['id', 'title'],
                ],
            ]);
    }
}
