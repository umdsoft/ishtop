<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vacancy;

class VacancyPolicy
{
    /**
     * Vacancy egasi yoki admin — update qilishi mumkin.
     */
    public function update(User $user, Vacancy $vacancy): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $employer = $user->employerProfile;

        return $employer && $vacancy->employer_id === $employer->id;
    }

    /**
     * Vacancy egasi yoki admin — delete qilishi mumkin.
     */
    public function delete(User $user, Vacancy $vacancy): bool
    {
        return $this->update($user, $vacancy);
    }

    /**
     * Vacancy egasi — activate qilishi mumkin.
     */
    public function activate(User $user, Vacancy $vacancy): bool
    {
        return $this->update($user, $vacancy);
    }

    /**
     * Vacancy egasi — candidates ko'rishi mumkin.
     */
    public function viewCandidates(User $user, Vacancy $vacancy): bool
    {
        return $this->update($user, $vacancy);
    }
}
