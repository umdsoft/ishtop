<?php

namespace App\Services;

use App\Enums\SubscriptionPlan;
use App\Models\User;
use App\Models\Vacancy;

class SubscriptionLimitService
{
    private array $vacancyCountCache = [];

    /**
     * Get the current plan for a user.
     */
    public function currentPlan(User $user): SubscriptionPlan
    {
        $sub = $user->activeSubscription();

        if (!$sub) {
            return SubscriptionPlan::FREE;
        }

        return $sub->plan;
    }

    /**
     * Get all limits for the user's current plan.
     */
    public function limits(User $user): array
    {
        return $this->currentPlan($user)->limits();
    }

    /**
     * Get current usage stats for the user.
     */
    public function usage(User $user): array
    {
        $employerId = $user->active_employer_id;

        return [
            'vacancies' => $employerId
                ? Vacancy::where('employer_id', $employerId)
                    ->whereNotIn('status', ['closed', 'expired'])
                    ->count()
                : 0,
            'questionnaires' => $employerId
                ? \App\Models\Questionnaire::whereHas('vacancy', fn($q) => $q->where('employer_id', $employerId))
                    ->count()
                : 0,
            'template_messages' => \App\Models\MessageTemplate::where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Check if user can create a new vacancy.
     */
    public function canCreateVacancy(User $user): bool
    {
        $limits = $this->limits($user);
        $maxVacancies = $limits['max_vacancies'];

        // null = unlimited
        if ($maxVacancies === null) {
            return true;
        }

        return $this->activeVacancyCount($user) < $maxVacancies;
    }

    /**
     * How many vacancies the user can still create.
     */
    public function remainingVacancies(User $user): ?int
    {
        $limits = $this->limits($user);
        $maxVacancies = $limits['max_vacancies'];

        if ($maxVacancies === null) {
            return null; // unlimited
        }

        return max(0, $maxVacancies - $this->activeVacancyCount($user));
    }

    /**
     * Cached active vacancy count per employer (avoids duplicate queries within same request).
     */
    private function activeVacancyCount(User $user): int
    {
        $employerId = $user->active_employer_id;
        if (!$employerId) {
            return 0;
        }

        return $this->vacancyCountCache[$employerId] ??= Vacancy::where('employer_id', $employerId)
            ->whereNotIn('status', ['closed', 'expired'])
            ->count();
    }

    /**
     * Check if user can create a new questionnaire.
     */
    public function canCreateQuestionnaire(User $user): bool
    {
        $limits = $this->limits($user);
        $max = $limits['max_questionnaires'];

        if ($max === null) {
            return true;
        }

        $currentCount = \App\Models\Questionnaire::whereHas('vacancy', fn($q) => $q->where('employer_id', $user->active_employer_id))
            ->count();

        return $currentCount < $max;
    }

    /**
     * Check if a specific feature is available.
     */
    public function hasFeature(User $user, string $feature): bool
    {
        $limits = $this->limits($user);

        return $limits[$feature] ?? false;
    }

    /**
     * Get full subscription info for API response.
     */
    public function getSubscriptionInfo(User $user): array
    {
        $plan = $this->currentPlan($user);
        $sub = $user->activeSubscription();
        $limits = $plan->limits();
        $usage = $this->usage($user);

        return [
            'subscription' => $sub,
            'plan' => $plan->value,
            'plan_label' => $plan->label(),
            'days_left' => $sub?->daysLeft() ?? 0,
            'limits' => $limits,
            'usage' => $usage,
            'can_create_vacancy' => $this->canCreateVacancy($user),
            'remaining_vacancies' => $this->remainingVacancies($user),
        ];
    }
}
