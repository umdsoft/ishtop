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
