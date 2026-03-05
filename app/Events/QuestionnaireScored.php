<?php

namespace App\Events;

use App\Models\QuestionnaireResponse;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuestionnaireScored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public QuestionnaireResponse $response,
        public float $score,
    ) {}
}
