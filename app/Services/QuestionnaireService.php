<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Questionnaire;
use App\Models\QuestionnaireResponse;
use App\Models\QuestionnaireTemplate;
use App\Models\Vacancy;
use Illuminate\Support\Facades\DB;

class QuestionnaireService
{
    public function __construct(
        private ScoringService $scoringService
    ) {}

    public function createForVacancy(Vacancy $vacancy, array $data): Questionnaire
    {
        $questionnaire = Questionnaire::create([
            'vacancy_id' => $vacancy->id,
            'title' => $data['title'] ?? $vacancy->title . ' — Savolnoma',
            'description' => $data['description'] ?? null,
            'is_required' => $data['is_required'] ?? true,
            'time_limit_minutes' => $data['time_limit_minutes'] ?? null,
            'passing_score' => $data['passing_score'] ?? null,
            'auto_reject_below' => $data['auto_reject_below'] ?? null,
        ]);

        $vacancy->update(['has_questionnaire' => true]);

        return $questionnaire;
    }

    public function submitResponse(Application $application, array $answers): QuestionnaireResponse
    {
        return DB::transaction(function () use ($application, $answers) {
            $questionnaire = $application->vacancy->questionnaire;

            $response = QuestionnaireResponse::create([
                'questionnaire_id' => $questionnaire->id,
                'application_id' => $application->id,
                'user_id' => $application->worker->user_id,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            foreach ($answers as $answer) {
                $response->answers()->create([
                    'question_id' => $answer['question_id'],
                    'answer_value' => $answer['answer_value'],
                ]);
            }

            $score = $this->scoringService->score($response);

            $questionnaire->updateStats();

            return $response->fresh();
        });
    }

    public function createFromTemplate(Vacancy $vacancy, QuestionnaireTemplate $template): Questionnaire
    {
        return DB::transaction(function () use ($vacancy, $template) {
            $questionnaire = $this->createForVacancy($vacancy, [
                'title' => $template->name,
            ]);

            foreach ($template->questions_data as $index => $qData) {
                $question = $questionnaire->questions()->create([
                    'sort_order' => $index + 1,
                    'type' => $qData['type'],
                    'text_uz' => $qData['text_uz'],
                    'text_ru' => $qData['text_ru'] ?? null,
                    'is_required' => $qData['is_required'] ?? true,
                    'weight' => $qData['weight'] ?? 0,
                    'is_knockout' => $qData['is_knockout'] ?? false,
                    'correct_answer' => $qData['correct_answer'] ?? null,
                    'scoring_config' => $qData['scoring_config'] ?? null,
                ]);

                foreach ($qData['options'] ?? [] as $optIndex => $opt) {
                    $question->options()->create([
                        'sort_order' => $optIndex + 1,
                        'value' => $opt['value'],
                        'label_uz' => $opt['label_uz'],
                        'label_ru' => $opt['label_ru'] ?? null,
                        'is_correct' => $opt['is_correct'] ?? false,
                        'score_value' => $opt['score_value'] ?? null,
                    ]);
                }
            }

            $questionnaire->updateStats();
            $template->incrementUsage();

            return $questionnaire;
        });
    }
}
