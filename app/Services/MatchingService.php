<?php

namespace App\Services;

use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Support\Collection;

class MatchingService
{
    public function findMatchesForWorker(WorkerProfile $worker, int $limit = 10): Collection
    {
        $query = Vacancy::active();

        if ($worker->city) {
            $query->where('city', $worker->city);
        }

        if ($worker->expected_salary_min) {
            $query->where(function ($q) use ($worker) {
                $q->whereNull('salary_max')
                  ->orWhere('salary_max', '>=', $worker->expected_salary_min);
            });
        }

        if ($worker->work_types) {
            $query->whereIn('work_type', $worker->work_types);
        }

        return $query->orderByDesc('published_at')->limit($limit)->get();
    }

    public function findMatchesForVacancy(Vacancy $vacancy, int $limit = 20): Collection
    {
        $query = WorkerProfile::active();

        if ($vacancy->city) {
            $query->where('city', $vacancy->city);
        }

        if ($vacancy->salary_min) {
            $query->where(function ($q) use ($vacancy) {
                $q->whereNull('expected_salary_max')
                  ->orWhere('expected_salary_max', '>=', $vacancy->salary_min);
            });
        }

        $query->where('work_types', 'LIKE', "%{$vacancy->work_type->value}%");

        return $query->limit($limit)->get();
    }

    /**
     * Find recommended candidates for a vacancy with scoring, excluding already applied.
     */
    public function getRecommendedCandidates(Vacancy $vacancy, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = WorkerProfile::query()
            ->whereIn('search_status', ['open', 'passive'])
            ->whereDoesntHave('applications', fn($q) => $q->where('vacancy_id', $vacancy->id));

        // Soft filters — don't strictly require, but prefer matches
        // City filter (soft — include all but city matches will score higher)
        // We don't filter strictly so recruiters can see nearby candidates too

        $workers = $query
            ->select('id', 'full_name', 'city', 'district', 'specialty', 'experience_years', 'expected_salary_min', 'expected_salary_max', 'work_types', 'skills', 'photo_url', 'search_status')
            ->paginate($perPage);

        // Calculate match score for each worker
        $workers->getCollection()->transform(function ($worker) use ($vacancy) {
            $worker->match_score = $this->calculateMatchScore($worker, $vacancy);
            return $worker;
        });

        // Sort by match_score descending (within the page)
        $sorted = $workers->getCollection()->sortByDesc('match_score')->values();
        $workers->setCollection($sorted);

        return $workers;
    }

    public function calculateMatchScore(WorkerProfile $worker, Vacancy $vacancy): int
    {
        $score = 0;
        $maxScore = 0;

        // City match (30 points)
        $maxScore += 30;
        if ($worker->city && $worker->city === $vacancy->city) {
            $score += 30;
        }

        // Salary match (25 points)
        $maxScore += 25;
        if ($this->salaryMatches($worker, $vacancy)) {
            $score += 25;
        }

        // Work type match (20 points)
        $maxScore += 20;
        if ($worker->work_types && in_array($vacancy->work_type->value, $worker->work_types)) {
            $score += 20;
        }

        // Experience match (25 points)
        $maxScore += 25;
        $score += $this->experienceScore($worker, $vacancy);

        return $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : 0;
    }

    private function salaryMatches(WorkerProfile $worker, Vacancy $vacancy): bool
    {
        if (!$worker->expected_salary_min && !$vacancy->salary_min) return true;
        if ($worker->expected_salary_min && $vacancy->salary_max) {
            return $vacancy->salary_max >= $worker->expected_salary_min;
        }
        return true;
    }

    private function experienceScore(WorkerProfile $worker, Vacancy $vacancy): int
    {
        $required = match ($vacancy->experience_required) {
            'no_experience' => 0,
            '1-3' => 1,
            '3-5' => 3,
            '5+' => 5,
            default => 0,
        };

        if ($worker->experience_years >= $required) return 25;
        if ($worker->experience_years >= $required - 1) return 15;
        return 5;
    }
}
