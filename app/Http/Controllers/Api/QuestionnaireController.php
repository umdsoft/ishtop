<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Questionnaire;
use App\Models\Vacancy;
use App\Services\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function __construct(private QuestionnaireService $questionnaireService) {}

    public function show(Vacancy $vacancy): JsonResponse
    {
        $questionnaire = $vacancy->questionnaire()
            ->with(['questions' => function ($q) {
                $q->orderBy('sort_order')->with('options');
            }])
            ->first();

        if (!$questionnaire) {
            return response()->json(['questionnaire' => null]);
        }

        return response()->json(['questionnaire' => $questionnaire]);
    }

    public function respond(Request $request, Questionnaire $questionnaire): JsonResponse
    {
        $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|uuid|exists:questions,id',
            'answers.*.value' => 'required',
            'time_spent_seconds' => 'nullable|integer|min:0',
        ]);

        $user = $request->user();
        $worker = $user->workerProfile;

        if (!$worker) {
            return response()->json(['message' => 'Rezume kerak'], 422);
        }

        $application = Application::where('vacancy_id', $questionnaire->vacancy_id)
            ->where('worker_id', $worker->id)
            ->first();

        if (!$application) {
            return response()->json(['message' => 'Avval arizangizni yuboring'], 422);
        }

        $existing = $questionnaire->responses()
            ->where('application_id', $application->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Siz allaqachon javob bergansiz', 'response' => $existing], 422);
        }

        $response = $this->questionnaireService->submitResponse($application, $request->answers);

        if ($request->time_spent_seconds) {
            $response->update(['time_spent_seconds' => $request->time_spent_seconds]);
        }

        return response()->json([
            'response' => $response->load('answers'),
            'score' => $response->total_score,
            'passed' => $response->knockout_passed,
        ], 201);
    }
}
