<?php

namespace Tests\Unit\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Services\ScoringService;
use Tests\TestCase;

class ScoringServiceTest extends TestCase
{
    protected ScoringService $scoringService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scoringService = new ScoringService();
    }

    public function test_scores_single_choice_question_correctly(): void
    {
        $question = new Question([
            'type' => 'single_choice',
            'weight' => 10,
            'correct_answer' => ['correct_value'],
        ]);

        $answer = 'correct_value';

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(10, $score);
    }

    public function test_scores_single_choice_question_incorrectly(): void
    {
        $question = new Question([
            'type' => 'single_choice',
            'weight' => 10,
            'correct_answer' => ['correct_value'],
        ]);

        $answer = 'wrong_value';

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(0, $score);
    }

    public function test_scores_knockout_question_pass(): void
    {
        $question = new Question([
            'type' => 'knockout',
            'weight' => 0,
            'is_knockout' => true,
            'correct_answer' => ['yes'],
        ]);

        $answer = 'yes';

        $score = $this->scoringService->scoreAnswer($question, $answer);
        $knockoutFailed = $this->scoringService->isKnockoutFailed($question, $answer);

        $this->assertEquals(0, $score);
        $this->assertFalse($knockoutFailed);
    }

    public function test_scores_knockout_question_fail(): void
    {
        $question = new Question([
            'type' => 'knockout',
            'weight' => 0,
            'is_knockout' => true,
            'correct_answer' => ['yes'],
        ]);

        $answer = 'no';

        $knockoutFailed = $this->scoringService->isKnockoutFailed($question, $answer);

        $this->assertTrue($knockoutFailed);
    }

    public function test_scores_multi_select_question_partially(): void
    {
        $question = new Question([
            'type' => 'multi_select',
            'weight' => 20,
            'correct_answer' => ['option1', 'option2', 'option3'],
        ]);

        $answer = ['option1', 'option2']; // 2 out of 3 correct

        $score = $this->scoringService->scoreAnswer($question, $answer);

        // Partial scoring: (2/3) * 20 = 13.33
        $this->assertGreaterThan(13, $score);
        $this->assertLessThan(14, $score);
    }

    public function test_scores_number_range_question(): void
    {
        $question = new Question([
            'type' => 'number_range',
            'weight' => 15,
            'scoring_config' => [
                'min' => 0,
                'max' => 100,
                'ideal' => 50,
            ],
        ]);

        $answer = 50; // Ideal answer

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(15, $score);
    }
}
