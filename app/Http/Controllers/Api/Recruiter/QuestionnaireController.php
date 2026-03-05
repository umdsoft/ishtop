<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Questionnaire;
use App\Models\Vacancy;
use App\Services\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function __construct(private QuestionnaireService $questionnaireService) {}

    public function store(Request $request, string $vacancy): JsonResponse
    {
        $vacancyModel = Vacancy::findOrFail($vacancy);

        if ($vacancyModel->questionnaire) {
            return response()->json(['message' => 'Bu vakansiyada allaqachon savolnoma mavjud'], 422);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:300',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'time_limit_minutes' => 'nullable|integer|min:1|max:120',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'auto_reject_below' => 'nullable|integer|min:0|max:100',
        ]);

        $questionnaire = $this->questionnaireService->createForVacancy($vacancyModel, $validated);

        $vacancyModel->update(['has_questionnaire' => true]);

        return response()->json(['questionnaire' => $questionnaire], 201);
    }

    public function show(Request $request, string $vacancy): JsonResponse
    {
        $vacancyModel = Vacancy::findOrFail($vacancy);

        $questionnaire = $vacancyModel->questionnaire()
            ->with(['questions' => function ($q) {
                $q->orderBy('sort_order')->with('options');
            }])
            ->first();

        if (!$questionnaire) {
            return response()->json(['questionnaire' => null]);
        }

        return response()->json(['questionnaire' => $questionnaire]);
    }

    public function update(Request $request, string $questionnaire): JsonResponse
    {
        $questionnaireModel = Questionnaire::findOrFail($questionnaire);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:300',
            'description' => 'nullable|string',
            'is_required' => 'nullable|boolean',
            'time_limit_minutes' => 'nullable|integer|min:1|max:120',
            'passing_score' => 'nullable|integer|min:0|max:100',
            'auto_reject_below' => 'nullable|integer|min:0|max:100',
        ]);

        $questionnaireModel->update($validated);

        return response()->json(['questionnaire' => $questionnaireModel->fresh()]);
    }
}
