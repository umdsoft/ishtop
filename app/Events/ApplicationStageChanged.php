<?php

namespace App\Events;

use App\Enums\ApplicationStage;
use App\Models\Application;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplicationStageChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Application $application,
        public ApplicationStage $oldStage,
        public ApplicationStage $newStage,
    ) {}
}
