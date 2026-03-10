<?php

namespace App\Services;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VacancyService
{
    public function create(array $data): Vacancy
    {
        return DB::transaction(function () use ($data) {
            $vacancy = Vacancy::create(array_merge($data, [
                'status' => VacancyStatus::PENDING,
            ]));

            return $vacancy;
        });
    }

    public function approve(Vacancy $vacancy): void
    {
        $vacancy->update([
            'status' => VacancyStatus::ACTIVE,
            'published_at' => now(),
            'expires_at' => now()->addDays(15),
        ]);
    }

    public function reject(Vacancy $vacancy, string $reason = null): void
    {
        $vacancy->update([
            'status' => VacancyStatus::CLOSED,
        ]);
    }

    public function pause(Vacancy $vacancy): void
    {
        $vacancy->update(['status' => VacancyStatus::PAUSED]);
    }

    public function resume(Vacancy $vacancy): void
    {
        $vacancy->update(['status' => VacancyStatus::ACTIVE]);
    }

    public function close(Vacancy $vacancy): void
    {
        $vacancy->update(['status' => VacancyStatus::CLOSED]);
    }

    public function expireOverdue(): int
    {
        return Vacancy::where('status', VacancyStatus::ACTIVE)
            ->where('expires_at', '<', now())
            ->update(['status' => VacancyStatus::EXPIRED]);
    }

    public function incrementViews(Vacancy $vacancy): void
    {
        $ip = request()->ip();
        $cacheKey = "vacancy_view:{$vacancy->id}:{$ip}";

        if (Cache::has($cacheKey)) {
            return;
        }

        Cache::put($cacheKey, true, now()->addHour());
        $vacancy->increment('views_count');
    }
}
