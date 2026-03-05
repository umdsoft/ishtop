<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\WorkerProfile;
use App\Services\MatchingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MatchVacanciesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(MatchingService $matchingService): void
    {
        WorkerProfile::active()
            ->with('user')
            ->chunk(100, function ($workers) use ($matchingService) {
                foreach ($workers as $worker) {
                    $matches = $matchingService->findMatchesForWorker($worker, 5);

                    if ($matches->isNotEmpty()) {
                        SendNotificationJob::dispatch(
                            $worker->user,
                            'matching',
                            'Mos vakansiyalar topildi!',
                            "Sizga mos {$matches->count()} ta yangi vakansiya bor.",
                            ['vacancy_ids' => $matches->pluck('id')->toArray()],
                        );
                    }
                }
            });
    }
}
