<?php

namespace App\Jobs;

use App\Events\QuestionnaireScored;
use App\Models\QuestionnaireResponse;
use App\Services\ScoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScoreQuestionnaireJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private QuestionnaireResponse $response
    ) {}

    public function handle(ScoringService $scoringService): void
    {
        $score = $scoringService->score($this->response);

        event(new QuestionnaireScored($this->response, $score));
    }
}
