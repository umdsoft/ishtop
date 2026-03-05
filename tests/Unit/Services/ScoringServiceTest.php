<?php

namespace Tests\Unit\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\ResponseAnswer;
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
            'type' => QuestionType::SINGLE_CHOICE,
            'weight' => 10,
            'correct_answer' => ['preferred' => 'correct_value', 'acceptable' => []],
            'scoring_config' => ['preferred_score' => 100, 'acceptable_score' => 50, 'other_score' => 0],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['selected' => 'correct_value'],
        ]);

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(100.0, $score);
    }

    public function test_scores_single_choice_question_incorrectly(): void
    {
        $question = new Question([
            'type' => QuestionType::SINGLE_CHOICE,
            'weight' => 10,
            'correct_answer' => ['preferred' => 'correct_value', 'acceptable' => []],
            'scoring_config' => ['preferred_score' => 100, 'acceptable_score' => 50, 'other_score' => 0],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['selected' => 'wrong_value'],
        ]);

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(0.0, $score);
    }

    public function test_scores_knockout_question_pass(): void
    {
        $question = new Question([
            'type' => QuestionType::KNOCKOUT,
            'weight' => 0,
            'is_knockout' => true,
            'correct_answer' => ['value' => true],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['value' => true],
        ]);

        $score = $this->scoringService->scoreAnswer($question, $answer);
        $knockoutFailed = $this->scoringService->isKnockoutFailed($question, $answer);

        $this->assertEquals(100.0, $score);
        $this->assertFalse($knockoutFailed);
    }

    public function test_scores_knockout_question_fail(): void
    {
        $question = new Question([
            'type' => QuestionType::KNOCKOUT,
            'weight' => 0,
            'is_knockout' => true,
            'correct_answer' => ['value' => true],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['value' => false],
        ]);

        $knockoutFailed = $this->scoringService->isKnockoutFailed($question, $answer);

        $this->assertTrue($knockoutFailed);
    }

    public function test_scores_multi_select_question_partially(): void
    {
        $question = new Question([
            'type' => QuestionType::MULTI_SELECT,
            'weight' => 20,
            'correct_answer' => ['required' => ['option1', 'option2', 'option3']],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['selected' => ['option1', 'option2']],
        ]);

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEqualsWithDelta(66.67, $score, 0.01);
    }

    public function test_scores_number_range_question(): void
    {
        $question = new Question([
            'type' => QuestionType::NUMBER_RANGE,
            'weight' => 15,
            'correct_answer' => ['min' => 0, 'max' => 100, 'ideal' => 50],
            'scoring_config' => ['decay_rate' => 10],
        ]);

        $answer = new ResponseAnswer([
            'answer_value' => ['value' => 50],
        ]);

        $score = $this->scoringService->scoreAnswer($question, $answer);

        $this->assertEquals(100.0, $score);
    }
}
