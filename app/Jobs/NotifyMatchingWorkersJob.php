<?php

namespace App\Jobs;

use App\Models\Vacancy;
use App\Services\MatchingService;
use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyMatchingWorkersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Vacancy $vacancy) {}

    public function handle(MatchingService $matchingService, TelegramNotificationService $notificationService): void
    {
        $workers = $matchingService->findMatchesForVacancy($this->vacancy, 100);

        $vacancyDistrict = $this->vacancy->district
            ? mb_strtolower(trim($this->vacancy->district))
            : null;
        $vacancyRegion = $this->vacancy->city
            ? mb_strtolower(trim($this->vacancy->city))
            : null;

        foreach ($workers as $worker) {
            try {
                $score = $matchingService->calculateMatchScore($worker, $this->vacancy);

                $workerDistrict = $worker->district ? mb_strtolower(trim($worker->district)) : null;
                $workerRegion   = $worker->city     ? mb_strtolower(trim($worker->city))     : null;

                $isSameDistrict = $vacancyDistrict && $workerDistrict && $vacancyDistrict === $workerDistrict;
                $isSameRegion   = $vacancyRegion   && $workerRegion   && $vacancyRegion   === $workerRegion;

                // Same tuman  → threshold 15 (very local, notify even with low score)
                // Same viloyat → threshold 25
                // Different    → threshold 50 (must be strong match)
                $threshold = match (true) {
                    $isSameDistrict => 15,
                    $isSameRegion   => 25,
                    default         => 50,
                };

                if ($score >= $threshold) {
                    $notificationService->notifyMatchingVacancy($worker, $this->vacancy, $score);
                }
            } catch (\Throwable $e) {
                \Log::warning('NotifyMatchingWorkers failed for worker', [
                    'worker_id'  => $worker->id,
                    'vacancy_id' => $this->vacancy->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
