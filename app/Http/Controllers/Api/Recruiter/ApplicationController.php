<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Enums\ApplicationStage;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Questionnaire;
use App\Models\Vacancy;
use App\Services\MatchingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $applications = Application::where('vacancy_id', $vacancyModel->id)
            ->with([
                'worker:id,full_name,city,specialty,experience_years,expected_salary_min,expected_salary_max,photo_url',
                'worker.user:id,first_name,last_name,username,phone,last_active_at',
            ])
            ->when($request->stage, fn($q, $v) => $q->where('stage', $v))
            ->when($request->sort === 'score', fn($q) => $q->orderByDesc('questionnaire_score'))
            ->when($request->sort === 'rating', fn($q) => $q->orderByDesc('recruiter_rating'))
            ->when($request->sort === 'matching', fn($q) => $q->orderByDesc('matching_score'))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $stageCounts = Application::where('vacancy_id', $vacancyModel->id)
            ->selectRaw('stage, COUNT(*) as count')
            ->groupBy('stage')
            ->pluck('count', 'stage');

        return response()->json([
            'applications' => $applications,
            'stage_counts' => $stageCounts,
        ]);
    }

    public function show(Request $request, string $application): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        $app = Application::with([
            'worker',
            'worker.user:id,first_name,last_name,username,phone,last_active_at',
            'vacancy:id,title_uz,title_ru,status,employer_id',
            'questionnaireResponse.answers.question.options',
            'notes' => fn($q) => $q->orderByDesc('created_at'),
            'notes.user:id,first_name,last_name',
            'tags',
        ])->findOrFail($application);

        if ($app->vacancy->employer_id !== $employer->id) {
            return response()->json(['message' => 'Ruxsat yo\'q'], 403);
        }

        // Auto-mark as viewed
        if (!$app->viewed_at) {
            $app->update(['viewed_at' => now()]);
            if ($app->stage === ApplicationStage::NEW) {
                $app->moveToStage(ApplicationStage::REVIEWED);
                $app->refresh();
            }
        }

        // Load questionnaire with questions (even if no response exists)
        $questionnaire = Questionnaire::where('vacancy_id', $app->vacancy_id)
            ->with(['questions' => fn($q) => $q->orderBy('sort_order'), 'questions.options'])
            ->first();

        // Match analysis
        $matchAnalysis = null;
        if ($app->worker) {
            $vacancy = Vacancy::find($app->vacancy_id);
            if ($vacancy) {
                $matchAnalysis = app(MatchingService::class)->getDetailedAnalysis($app->worker, $vacancy);
            }
        }

        return response()->json([
            'application' => $app,
            'questionnaire' => $questionnaire,
            'match_analysis' => $matchAnalysis,
        ]);
    }
}
