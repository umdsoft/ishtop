<?php

namespace App\Observers;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Services\TelegramNotificationService;
use Illuminate\Support\Facades\Log;

class ApplicationObserver
{
    public function __construct(private TelegramNotificationService $notificationService) {}

    public function created(Application $application): void
    {
        try {
            $this->notificationService->notifyNewApplication($application);
        } catch (\Throwable $e) {
            Log::warning('Failed to send new application notification', ['error' => $e->getMessage()]);
        }
    }

    public function updated(Application $application): void
    {
        if ($application->wasChanged('stage')) {
            $oldStageValue = $application->getOriginal('stage');
            $oldStage = $oldStageValue instanceof ApplicationStage
                ? $oldStageValue
                : ApplicationStage::tryFrom($oldStageValue) ?? ApplicationStage::NEW;

            try {
                $this->notificationService->notifyApplicationStageChanged(
                    $application,
                    $oldStage,
                    $application->stage,
                );
            } catch (\Throwable $e) {
                Log::warning('Failed to send application stage notification', ['error' => $e->getMessage()]);
            }
        }
    }
}
