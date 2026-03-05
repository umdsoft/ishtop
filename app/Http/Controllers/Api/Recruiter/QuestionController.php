<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'questionnaire_id' => 'required|uuid|exists:questionnaires,id',
            'type' => 'required|string|in:knockout,single_choice,multi_select,number_range,open_text,file_upload',
            'text_uz' => 'required|string',
            'text_ru' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'weight' => 'nullable|integer|min:0|max:100',
            'is_knockout' => 'nullable|boolean',
            'correct_answer' => 'nullable|array',
            'scoring_config' => 'nullable|array',
            'options' => 'nullable|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label_uz' => 'required_with:options|string',
            'options.*.label_ru' => 'nullable|string',
            'options.*.is_correct' => 'nullable|boolean',
            'options.*.score_value' => 'nullable|integer',
        ]);

        $maxOrder = Question::where('questionnaire_id', $validated['questionnaire_id'])->max('sort_order') ?? 0;

        $question = DB::transaction(function () use ($validated, $maxOrder) {
            $question = Question::create([
                'questionnaire_id' => $validated['questionnaire_id'],
                'sort_order' => $maxOrder + 1,
                'type' => $validated['type'],
                'text_uz' => $validated['text_uz'],
                'text_ru' => $validated['text_ru'] ?? null,
                'is_required' => $validated['is_required'] ?? true,
                'weight' => $validated['weight'] ?? 0,
                'is_knockout' => $validated['is_knockout'] ?? false,
                'correct_answer' => $validated['correct_answer'] ?? null,
                'scoring_config' => $validated['scoring_config'] ?? null,
            ]);

            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $i => $opt) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'sort_order' => $i + 1,
                        'value' => $opt['value'],
                        'label_uz' => $opt['label_uz'],
                        'label_ru' => $opt['label_ru'] ?? null,
                        'is_correct' => $opt['is_correct'] ?? false,
                        'score_value' => $opt['score_value'] ?? null,
                    ]);
                }
            }

            $question->questionnaire->updateStats();

            return $question;
        });

        return response()->json(['question' => $question->load('options')], 201);
    }

    public function update(Request $request, string $question): JsonResponse
    {
        $questionModel = Question::findOrFail($question);

        $validated = $request->validate([
            'type' => 'sometimes|string|in:knockout,single_choice,multi_select,number_range,open_text,file_upload',
            'text_uz' => 'sometimes|string',
            'text_ru' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'weight' => 'nullable|integer|min:0|max:100',
            'is_knockout' => 'nullable|boolean',
            'correct_answer' => 'nullable|array',
            'scoring_config' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        DB::transaction(function () use ($questionModel, $validated) {
            $questionModel->update(collect($validated)->except('options')->toArray());

            if (isset($validated['options'])) {
                $questionModel->options()->delete();
                foreach ($validated['options'] as $i => $opt) {
                    QuestionOption::create([
                        'question_id' => $questionModel->id,
                        'sort_order' => $i + 1,
                        'value' => $opt['value'],
                        'label_uz' => $opt['label_uz'],
                        'label_ru' => $opt['label_ru'] ?? null,
                        'is_correct' => $opt['is_correct'] ?? false,
                        'score_value' => $opt['score_value'] ?? null,
                    ]);
                }
            }
        });

        return response()->json(['question' => $questionModel->fresh()->load('options')]);
    }

    public function destroy(Request $request, string $question): JsonResponse
    {
        $questionModel = Question::findOrFail($question);
        $questionnaire = $questionModel->questionnaire;

        $questionModel->delete();
        $questionnaire->updateStats();

        return response()->json(['message' => 'Savol o\'chirildi']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|uuid|exists:questions,id',
            'questions.*.sort_order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->questions as $item) {
                Question::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
            }
        });

        return response()->json(['message' => 'Tartib yangilandi']);
    }
}
