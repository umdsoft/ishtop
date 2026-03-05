<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\QuestionnaireResponse;
use App\Models\ResponseAnswer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function show(Request $request, string $application): JsonResponse
    {
        $app = Application::findOrFail($application);

        $response = QuestionnaireResponse::where('application_id', $app->id)
            ->with(['answers' => function ($q) {
                $q->with('question:id,text_uz,text_ru,type,weight,is_knockout');
            }])
            ->first();

        if (!$response) {
            return response()->json(['response' => null, 'message' => 'Savolnoma javoblari topilmadi']);
        }

        return response()->json([
            'response' => $response,
            'total_score' => $response->total_score,
            'knockout_passed' => $response->knockout_passed,
        ]);
    }

    public function manualScore(Request $request, string $answer): JsonResponse
    {
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
        ]);

        $answerModel = ResponseAnswer::findOrFail($answer);

        $answerModel->update([
            'manual_score' => $request->score,
            'manual_scored_by' => $request->user()->id,
        ]);

        $response = $answerModel->response;
        $answers = $response->answers()->with('question')->get();
        $totalWeight = $answers->sum(fn($a) => $a->question->weight);

        if ($totalWeight > 0) {
            $weightedScore = $answers->sum(function ($a) use ($totalWeight) {
                $score = $a->getFinalScore() ?? 0;
                return ($a->question->weight / $totalWeight) * $score;
            });
            $response->update(['total_score' => round($weightedScore, 2)]);
        }

        return response()->json([
            'answer' => $answerModel->fresh(),
            'new_total_score' => $response->fresh()->total_score,
        ]);
    }
}
