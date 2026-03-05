<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancy;
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
                'worker.user:id,first_name,last_name,username,last_active_at',
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
}
