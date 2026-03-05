<?php

namespace App\Services;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionnaireResponse;
use App\Models\ResponseAnswer;

class ScoringService
{
    public function score(QuestionnaireResponse $response): float
    {
        $answers = $response->answers()->with('question')->get();
        $totalWeight = 0;
        $weightedScore = 0;
        $knockoutFailed = false;

        foreach ($answers as $answer) {
            $question = $answer->question;
            $score = $this->scoreAnswer($question, $answer);
            $answer->update(['score' => $score]);

            if ($question->is_knockout && $score === 0.0) {
                $knockoutFailed = true;
                $answer->update(['is_knockout_failed' => true]);
            }

            if (!$question->is_knockout && $question->weight > 0) {
                $totalWeight += $question->weight;
                $weightedScore += ($score / 100) * $question->weight;
            }
        }

        if ($knockoutFailed) {
            $response->update([
                'total_score' => 0,
                'knockout_passed' => false,
                'status' => 'scored',
                'scored_at' => now(),
            ]);

            $response->application->update([
                'questionnaire_score' => 0,
                'knockout_passed' => false,
            ]);

            return 0;
        }

        $finalScore = $totalWeight > 0
            ? ($weightedScore / $totalWeight) * 100
            : 0;

        $response->update([
            'total_score' => round($finalScore, 2),
            'knockout_passed' => true,
            'status' => 'scored',
            'scored_at' => now(),
        ]);

        $response->application->update([
            'questionnaire_score' => round($finalScore, 2),
            'knockout_passed' => true,
        ]);

        return round($finalScore, 2);
    }

    public function isKnockoutFailed(Question $question, ResponseAnswer $answer): bool
    {
        $score = $this->scoreAnswer($question, $answer);
        return $question->is_knockout && $score === 0.0;
    }

    public function scoreAnswer(Question $q, ResponseAnswer $a): float
    {
        return match ($q->type) {
            QuestionType::KNOCKOUT => $this->scoreKnockout($q, $a),
            QuestionType::SINGLE_CHOICE => $this->scoreSingle($q, $a),
            QuestionType::MULTI_SELECT => $this->scoreMulti($q, $a),
            QuestionType::NUMBER_RANGE => $this->scoreNumber($q, $a),
            QuestionType::OPEN_TEXT => 0.0, // manual or AI
            QuestionType::FILE_UPLOAD => $this->scoreFile($q, $a),
        };
    }

    private function scoreKnockout(Question $q, ResponseAnswer $a): float
    {
        $expected = $q->correct_answer['value'] ?? true;
        $given = $a->answer_value['value'] ?? null;
        return $given === $expected ? 100.0 : 0.0;
    }

    private function scoreSingle(Question $q, ResponseAnswer $a): float
    {
        $selected = $a->answer_value['selected'] ?? null;
        $config = $q->correct_answer;
        $scoring = $q->scoring_config;

        if ($selected === ($config['preferred'] ?? null)) {
            return (float) ($scoring['preferred_score'] ?? 100);
        }

        if (in_array($selected, $config['acceptable'] ?? [])) {
            return (float) ($scoring['acceptable_score'] ?? 50);
        }

        return (float) ($scoring['other_score'] ?? 0);
    }

    private function scoreMulti(Question $q, ResponseAnswer $a): float
    {
        $selected = $a->answer_value['selected'] ?? [];
        $required = $q->correct_answer['required'] ?? [];

        if (empty($required)) return 0.0;

        $matched = count(array_intersect($selected, $required));
        return round(($matched / count($required)) * 100, 2);
    }

    private function scoreNumber(Question $q, ResponseAnswer $a): float
    {
        $value = $a->answer_value['value'] ?? null;
        if ($value === null) return 0.0;

        $config = $q->correct_answer;
        $min = $config['min'] ?? null;
        $max = $config['max'] ?? null;
        $ideal = $config['ideal'] ?? null;

        if ($min !== null && $value < $min) {
            $decayRate = $q->scoring_config['decay_rate'] ?? 10;
            return max(0, 100 - (($min - $value) * $decayRate));
        }

        if ($max !== null && $value > $max) {
            $decayRate = $q->scoring_config['decay_rate'] ?? 10;
            return max(0, 100 - (($value - $max) * $decayRate));
        }

        return 100.0;
    }

    private function scoreFile(Question $q, ResponseAnswer $a): float
    {
        $fileUrl = $a->answer_value['file_url'] ?? null;
        $isRequired = $q->scoring_config['required'] ?? false;

        if ($fileUrl) return 100.0;
        return $isRequired ? 0.0 : 50.0;
    }
}
