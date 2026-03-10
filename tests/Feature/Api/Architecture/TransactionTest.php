<?php

namespace Tests\Feature\Api\Architecture;

use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private function authenticatedRequest(User $user): static
    {
        $token = $user->createToken('test')->plainTextToken;
        return $this->withHeader('Authorization', "Bearer {$token}");
    }

    // ── Application store uses DB::transaction ──

    public function test_application_store_increments_count_atomically(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create();
        $vacancy = Vacancy::factory()->active()->create([
            'employer_id' => $employer->id,
            'applications_count' => 5,
        ]);

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/applications', [
                'vacancy_id' => $vacancy->id,
                'cover_letter' => 'Hello',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('vacancies', [
            'id' => $vacancy->id,
            'applications_count' => 6,
        ]);
    }

    public function test_duplicate_application_rejected(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create();
        $vacancy = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);

        // First application
        $this->authenticatedRequest($user)
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id]);

        // Duplicate attempt
        $response = $this->authenticatedRequest($user)
            ->postJson('/api/applications', ['vacancy_id' => $vacancy->id]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Ariza allaqachon yuborilgan');
    }

    // ── Review store uses DB::transaction ──

    public function test_review_updates_employer_rating_atomically(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create([
            'rating' => 0,
            'rating_count' => 0,
        ]);

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/reviews', [
                'employer_profile_id' => $employer->id,
                'rating' => 4,
                'comment' => 'Great employer!',
            ]);

        $response->assertStatus(201);

        $employer->refresh();
        $this->assertEquals(4.0, (float) $employer->rating);
        $this->assertEquals(1, $employer->rating_count);
    }

    public function test_multiple_reviews_calculate_average_correctly(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $employer = EmployerProfile::factory()->create();

        // First review: rating 4
        $this->authenticatedRequest($user1)
            ->postJson('/api/reviews', [
                'employer_profile_id' => $employer->id,
                'rating' => 4,
            ]);

        // Second review: rating 2
        $this->authenticatedRequest($user2)
            ->postJson('/api/reviews', [
                'employer_profile_id' => $employer->id,
                'rating' => 2,
            ]);

        $employer->refresh();
        $this->assertEquals(3.0, (float) $employer->rating);
        $this->assertEquals(2, $employer->rating_count);
    }

    public function test_duplicate_review_rejected(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create();

        $this->authenticatedRequest($user)
            ->postJson('/api/reviews', [
                'employer_profile_id' => $employer->id,
                'rating' => 5,
            ]);

        $response = $this->authenticatedRequest($user)
            ->postJson('/api/reviews', [
                'employer_profile_id' => $employer->id,
                'rating' => 1,
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Siz allaqachon sharh qoldirgansiz');
    }
}
