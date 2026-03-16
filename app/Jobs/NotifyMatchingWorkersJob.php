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
        $workers = $matchingService->findMatchesForVacancy($this->vacancy, 50);

        foreach ($workers as $worker) {
            try {
                $score = $matchingService->calculateMatchScore($worker, $this->vacancy);
                if ($score >= 50) {
                    $notificationService->notifyMatchingVacancy($worker, $this->vacancy, $score);
                }
            } catch (\Throwable $e) {
                \Log::warning('NotifyMatchingWorkers failed for worker', [
                    'worker_id' => $worker->id,
                    'vacancy_id' => $this->vacancy->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
