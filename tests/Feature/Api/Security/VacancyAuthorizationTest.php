<?php

namespace Tests\Feature\Api\Security;

use App\Models\Application;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacancyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedRequest(User $user): static
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    private function createEmployerWithVacancy(): array
    {
        $owner = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $owner->id]);
        $owner->update(['active_employer_id' => $employer->id]);

        $vacancy = Vacancy::factory()->create([
            'employer_id' => $employer->id,
            'status' => 'active',
            'published_at' => now(),
        ]);

        return [$owner, $employer, $vacancy];
    }

    // ── Vacancy Update Authorization ──

    public function test_owner_can_update_own_vacancy(): void
    {
        [$owner, $employer, $vacancy] = $this->createEmployerWithVacancy();

        $response = $this->authenticatedRequest($owner)
            ->putJson("/api/vacancies/{$vacancy->id}", [
                'title_uz' => 'Updated Title',
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'title_uz' => 'Updated Title',
        ]);
    }

    public function test_other_user_cannot_update_vacancy(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $otherUser = User::factory()->create();
        $otherEmployer = EmployerProfile::factory()->create(['user_id' => $otherUser->id]);
        $otherUser->update(['active_employer_id' => $otherEmployer->id]);

        $response = $this->authenticatedRequest($otherUser)
            ->putJson("/api/vacancies/{$vacancy->id}", [
                'title_uz' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    public function test_user_without_employer_profile_cannot_update_vacancy(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $randomUser = User::factory()->create();

        $response = $this->authenticatedRequest($randomUser)
            ->putJson("/api/vacancies/{$vacancy->id}", [
                'title_uz' => 'Hacked Title',
            ]);

        $response->assertStatus(403);
    }

    // ── Vacancy Delete Authorization ──

    public function test_owner_can_delete_own_vacancy(): void
    {
        [$owner, $employer, $vacancy] = $this->createEmployerWithVacancy();

        $response = $this->authenticatedRequest($owner)
            ->deleteJson("/api/vacancies/{$vacancy->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('vacancies', ['id' => $vacancy->id]);
    }

    public function test_other_user_cannot_delete_vacancy(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $otherUser = User::factory()->create();

        $response = $this->authenticatedRequest($otherUser)
            ->deleteJson("/api/vacancies/{$vacancy->id}");

        $response->assertStatus(403);
    }

    // ── Application Stage Authorization ──

    public function test_vacancy_owner_can_update_application_stage(): void
    {
        [$owner, $employer, $vacancy] = $this->createEmployerWithVacancy();

        $worker = WorkerProfile::factory()->create();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $worker->id,
        ]);

        $response = $this->authenticatedRequest($owner)
            ->putJson("/api/applications/{$application->id}/stage", [
                'stage' => 'reviewed',
            ]);

        $response->assertStatus(200);
    }

    public function test_non_owner_cannot_update_application_stage(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $worker = WorkerProfile::factory()->create();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $worker->id,
        ]);

        $otherUser = User::factory()->create();

        $response = $this->authenticatedRequest($otherUser)
            ->putJson("/api/applications/{$application->id}/stage", [
                'stage' => 'reviewed',
            ]);

        $response->assertStatus(403);
    }

    // ── Application Withdraw Authorization ──

    public function test_worker_can_withdraw_own_application(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $workerUser = User::factory()->create();
        $workerProfile = WorkerProfile::factory()->create(['user_id' => $workerUser->id]);

        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $workerProfile->id,
        ]);

        $response = $this->authenticatedRequest($workerUser)
            ->deleteJson("/api/applications/{$application->id}/withdraw");

        $response->assertStatus(200);
    }

    public function test_other_worker_cannot_withdraw_application(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $workerProfile = WorkerProfile::factory()->create();
        $application = Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $workerProfile->id,
        ]);

        $otherUser = User::factory()->create();
        WorkerProfile::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->authenticatedRequest($otherUser)
            ->deleteJson("/api/applications/{$application->id}/withdraw");

        $response->assertStatus(403);
    }

    // ── Unauthenticated requests ──

    public function test_unauthenticated_user_cannot_update_vacancy(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $response = $this->putJson("/api/vacancies/{$vacancy->id}", [
            'title_uz' => 'Hacked',
        ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_user_cannot_delete_vacancy(): void
    {
        [, , $vacancy] = $this->createEmployerWithVacancy();

        $response = $this->deleteJson("/api/vacancies/{$vacancy->id}");

        $response->assertStatus(401);
    }
}
