<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Vacancy egasi — application stage'ni o'zgartirishi mumkin.
     */
    public function updateStage(User $user, Application $application): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $employer = $user->employerProfile;

        return $employer && $application->vacancy
            && $application->vacancy->employer_id === $employer->id;
    }

    /**
     * Ariza egasi (worker) — withdraw qilishi mumkin.
     */
    public function withdraw(User $user, Application $application): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $worker = $user->workerProfile;

        return $worker && $application->worker_id === $worker->id;
    }
}
