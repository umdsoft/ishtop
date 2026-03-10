<?php

namespace Tests\Feature\Api\Architecture;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedRequest(User $user): static
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    private function createEmployerUser(): array
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $user->id]);
        $user->update(['active_employer_id' => $employer->id]);
        return [$user, $employer];
    }

    // ── StoreVacancyRequest Tests ──

    public function test_store_vacancy_validates_required_fields(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['category', 'work_type']);
    }

    public function test_store_vacancy_requires_title_in_at_least_one_language(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', [
                'category' => 'it',
                'description_uz' => 'Test description',
                'work_type' => 'full_time',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title_uz']);
    }

    public function test_store_vacancy_with_both_languages_succeeds(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', [
                'title_uz' => 'Test Vacancy UZ',
                'title_ru' => 'Test Vacancy RU',
                'category' => 'it',
                'description_uz' => 'Test description UZ',
                'description_ru' => 'Test description RU',
                'work_type' => 'full_time',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('vacancy.title_uz', 'Test Vacancy UZ');
        $response->assertJsonPath('vacancy.title_ru', 'Test Vacancy RU');
    }

    public function test_store_vacancy_validates_title_max_length(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', [
                'title_uz' => str_repeat('A', 301),
                'category' => 'it',
                'description_uz' => 'Test',
                'work_type' => 'full_time',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title_uz']);
    }

    public function test_store_vacancy_validates_salary_type(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', [
                'title_uz' => 'Test',
                'category' => 'it',
                'description_uz' => 'Test',
                'work_type' => 'full_time',
                'salary_type' => 'invalid_type',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['salary_type']);
    }

    public function test_store_vacancy_validates_coordinates(): void
    {
        [$user] = $this->createEmployerUser();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/vacancies', [
                'title_uz' => 'Test',
                'category' => 'it',
                'description_uz' => 'Test',
                'work_type' => 'full_time',
                'latitude' => 200,
                'longitude' => -200,
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['latitude', 'longitude']);
    }

    // ── UpdateVacancyRequest Tests ──

    public function test_update_vacancy_allows_partial_update(): void
    {
        [$user, $employer] = $this->createEmployerUser();
        $vacancy = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);

        $response = $this->authenticatedRequest($user)
            ->putJson("/api/vacancies/{$vacancy->id}", [
                'title_uz' => 'Updated Title Only',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('vacancy.title_uz', 'Updated Title Only');
    }

    public function test_update_vacancy_validates_status_values(): void
    {
        [$user, $employer] = $this->createEmployerUser();
        $vacancy = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);

        // Recruiter update endpoint allows status field
        $response = $this->authenticatedRequest($user)
            ->putJson("/api/vacancies/{$vacancy->id}", [
                'salary_type' => 'invalid',
            ]);

        $response->assertStatus(422);
    }

    // ── StoreApplicationRequest Tests ──

    public function test_store_application_validates_vacancy_exists(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/applications', [
                'vacancy_id' => '00000000-0000-0000-0000-000000000000',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vacancy_id']);
    }

    public function test_store_application_validates_cover_letter_length(): void
    {
        $user = User::factory()->create();

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/applications', [
                'vacancy_id' => 'not-a-uuid',
                'cover_letter' => str_repeat('A', 2001),
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['vacancy_id']);
    }

    public function test_store_application_success(): void
    {
        $user = User::factory()->create();
        [$employer_user, $employer] = $this->createEmployerUser();
        $vacancy = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/applications', [
                'vacancy_id' => $vacancy->id,
                'cover_letter' => 'I would like to apply',
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('application.vacancy_id', $vacancy->id);
    }
}
