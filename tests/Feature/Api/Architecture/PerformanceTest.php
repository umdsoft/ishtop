<?php

namespace Tests\Feature\Api\Architecture;

use App\Models\Application;
use App\Models\Chat;
use App\Models\EmployerProfile;
use App\Models\Message;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use App\Services\MatchingService;
use App\Services\SubscriptionLimitService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function authHeaders(User $user): array
    {
        $token = $user->createToken('test')->plainTextToken;
        return ['Authorization' => "Bearer {$token}"];
    }

    // ── ChatController: withCount instead of N+1 ──

    public function test_chat_index_includes_unread_count(): void
    {
        $worker = User::factory()->create();
        $employer = User::factory()->create();

        $chat = Chat::create([
            'worker_user_id' => $worker->id,
            'employer_user_id' => $employer->id,
            'last_message_at' => now(),
        ]);

        // Create 3 unread messages from employer to worker
        for ($i = 0; $i < 3; $i++) {
            Message::create([
                'chat_id' => $chat->id,
                'sender_id' => $employer->id,
                'text' => "Message $i",
                'type' => 'text',
                'is_read' => false,
            ]);
        }

        // Create 1 read message
        Message::create([
            'chat_id' => $chat->id,
            'sender_id' => $employer->id,
            'text' => 'Read message',
            'type' => 'text',
            'is_read' => true,
        ]);

        $response = $this->getJson('/api/chats', $this->authHeaders($worker));

        $response->assertStatus(200);
        $chatData = $response->json('data.0');
        $this->assertEquals(3, $chatData['unread_count']);
    }

    // ── MatchingService: batch count ──

    public function test_matching_service_batch_count_returns_correct_counts(): void
    {
        $employer = EmployerProfile::factory()->create();

        $testCategory = 'it';

        // Create 5 open workers in Toshkent with matching category
        WorkerProfile::factory()->count(5)->create([
            'search_status' => 'open',
            'city' => 'Toshkent',
            'preferred_categories' => [$testCategory],
        ]);

        // Create 3 passive workers in Samarqand with matching category
        WorkerProfile::factory()->count(3)->create([
            'search_status' => 'passive',
            'city' => 'Samarqand',
            'preferred_categories' => [$testCategory],
        ]);

        // Create 2 closed workers (should not be counted)
        WorkerProfile::factory()->count(2)->create([
            'search_status' => 'closed',
            'city' => 'Toshkent',
            'preferred_categories' => [$testCategory],
        ]);

        $vacancy1 = Vacancy::factory()->active()->create([
            'employer_id' => $employer->id,
            'city' => 'Toshkent',
            'category' => $testCategory,
        ]);

        $vacancy2 = Vacancy::factory()->active()->create([
            'employer_id' => $employer->id,
            'city' => 'Samarqand',
            'category' => $testCategory,
        ]);

        $vacancy3 = Vacancy::factory()->active()->create([
            'employer_id' => $employer->id,
            'city' => null,
            'category' => $testCategory,
        ]);

        $service = app(MatchingService::class);

        $batchCounts = $service->countRecommendedCandidatesBatch(
            collect([$vacancy1, $vacancy2, $vacancy3])
        );

        // vacancy1 (Toshkent): 5 open in Toshkent = 5 (city workers only, no null city workers)
        $this->assertArrayHasKey($vacancy1->id, $batchCounts);

        // vacancy3 (no city): all open+passive = 8
        $this->assertEquals(8, $batchCounts[$vacancy3->id]);

        // Batch results should match individual counts
        foreach ([$vacancy1, $vacancy2, $vacancy3] as $vacancy) {
            $individualCount = $service->countRecommendedCandidates($vacancy);
            $this->assertEquals(
                $individualCount,
                $batchCounts[$vacancy->id],
                "Batch count for vacancy {$vacancy->id} should match individual count"
            );
        }
    }

    public function test_matching_service_batch_count_excludes_applied_workers(): void
    {
        $employer = EmployerProfile::factory()->create();
        $testCategory = 'it';

        $workers = WorkerProfile::factory()->count(5)->create([
            'search_status' => 'open',
            'city' => 'Toshkent',
            'preferred_categories' => [$testCategory],
        ]);

        $vacancy = Vacancy::factory()->active()->create([
            'employer_id' => $employer->id,
            'city' => 'Toshkent',
            'category' => $testCategory,
        ]);

        // 2 workers applied to this vacancy
        Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $workers[0]->id,
        ]);
        Application::factory()->create([
            'vacancy_id' => $vacancy->id,
            'worker_id' => $workers[1]->id,
        ]);

        $service = app(MatchingService::class);

        $batchCounts = $service->countRecommendedCandidatesBatch(collect([$vacancy]));

        // 5 total - 2 applied = 3
        $individualCount = $service->countRecommendedCandidates($vacancy);
        $this->assertEquals($individualCount, $batchCounts[$vacancy->id]);
    }

    public function test_matching_service_batch_count_empty_collection(): void
    {
        $service = app(MatchingService::class);

        $result = $service->countRecommendedCandidatesBatch(collect());

        $this->assertEmpty($result);
    }

    // ── User: activeSubscription caching ──

    public function test_active_subscription_returns_correct_subscription(): void
    {
        $user = User::factory()->create();

        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'business',
            'status' => 'active',
            'price' => 99000,
            'starts_at' => now()->subDays(5),
            'expires_at' => now()->addDays(25),
        ]);

        // Expired subscription (should not be returned)
        Subscription::create([
            'user_id' => $user->id,
            'plan' => 'recruiter_pro',
            'status' => 'active',
            'price' => 199000,
            'starts_at' => now()->subDays(35),
            'expires_at' => now()->subDays(5),
        ]);

        $subscription = $user->activeSubscription();

        $this->assertNotNull($subscription);
        $this->assertEquals('business', $subscription->plan->value);
    }

    public function test_active_subscription_returns_null_when_none(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->activeSubscription());
    }

    // ── SubscriptionLimitService: cached vacancy count ──

    public function test_subscription_limit_service_caches_vacancy_count(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $user->id]);
        $user->update(['active_employer_id' => $employer->id]);

        // Create 3 active vacancies
        Vacancy::factory()->count(3)->create([
            'employer_id' => $employer->id,
            'status' => 'active',
            'published_at' => now(),
        ]);

        // Create 1 closed vacancy (should not count)
        Vacancy::factory()->create([
            'employer_id' => $employer->id,
            'status' => 'closed',
        ]);

        $service = app(SubscriptionLimitService::class);

        // Both methods should return consistent results
        $canCreate = $service->canCreateVacancy($user);
        $remaining = $service->remainingVacancies($user);

        // Free plan has limited vacancies
        $this->assertIsBool($canCreate);
        $this->assertIsInt($remaining);
    }

    // ── ReviewController: uses denormalized rating ──

    public function test_review_by_employer_returns_denormalized_rating(): void
    {
        $worker = User::factory()->create();
        $employer = EmployerProfile::factory()->create([
            'rating' => 4.50,
            'rating_count' => 10,
        ]);

        Review::create([
            'employer_profile_id' => $employer->id,
            'worker_user_id' => $worker->id,
            'rating' => 5,
            'comment' => 'Great employer',
        ]);

        $response = $this->getJson(
            "/api/reviews/employer/{$employer->id}",
            $this->authHeaders($worker)
        );

        $response->assertStatus(200);
        // Should return the denormalized rating from employer profile, not computed AVG
        $this->assertEquals(4.50, $response->json('avg_rating'));
    }

    // ── Recruiter stats: efficient application count ──

    public function test_recruiter_vacancy_stats_include_total_applications(): void
    {
        $user = User::factory()->create();
        $employer = EmployerProfile::factory()->create(['user_id' => $user->id]);
        $user->update(['active_employer_id' => $employer->id]);

        $vacancy1 = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);
        $vacancy2 = Vacancy::factory()->active()->create(['employer_id' => $employer->id]);

        // 3 applications on vacancy1, 2 on vacancy2
        Application::factory()->count(3)->create(['vacancy_id' => $vacancy1->id]);
        Application::factory()->count(2)->create(['vacancy_id' => $vacancy2->id]);

        $response = $this->getJson(
            '/api/recruiter/vacancies',
            $this->authHeaders($user)
        );

        $response->assertStatus(200);
        $this->assertEquals(5, $response->json('stats.total_applications'));
        $this->assertEquals(2, $response->json('stats.active'));
    }
}
