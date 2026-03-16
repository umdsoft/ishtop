<?php

namespace App\Jobs;

use App\Models\WorkerProfile;
use App\Services\MatchingService;
use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchVacanciesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MatchingService $matchingService, TelegramNotificationService $notificationService): void
    {
        WorkerProfile::active()
            ->with('user')
            ->chunk(100, function ($workers) use ($matchingService, $notificationService) {
                foreach ($workers as $worker) {
                    try {
                        $matches = $matchingService->findMatchesForWorker($worker, 3);

                        foreach ($matches as $vacancy) {
                            $score = $matchingService->calculateMatchScore($worker, $vacancy);
                            if ($score >= 50) {
                                $notificationService->notifyMatchingVacancy($worker, $vacancy, $score);
                            }
                        }
                    } catch (\Throwable $e) {
                        \Log::warning('MatchVacanciesJob failed for worker', [
                            'worker_id' => $worker->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });
    }
}
