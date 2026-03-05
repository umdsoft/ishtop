<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function compare(Request $request): JsonResponse
    {
        $request->validate([
            'application_ids' => 'required|array|min:2|max:5',
            'application_ids.*' => 'uuid|exists:applications,id',
        ]);

        $applications = Application::whereIn('id', $request->application_ids)
            ->with([
                'worker:id,full_name,city,specialty,experience_years,expected_salary_min,expected_salary_max,skills,work_types,education_level,bio',
                'worker.user:id,first_name,last_name,username',
            ])
            ->get();

        $comparison = $applications->map(function ($app) {
            $worker = $app->worker;
            return [
                'application_id' => $app->id,
                'stage' => $app->stage,
                'questionnaire_score' => $app->questionnaire_score,
                'matching_score' => $app->matching_score,
                'recruiter_rating' => $app->recruiter_rating,
                'applied_at' => $app->created_at,
                'worker' => [
                    'id' => $worker?->id,
                    'full_name' => $worker?->full_name,
                    'city' => $worker?->city,
                    'specialty' => $worker?->specialty,
                    'experience_years' => $worker?->experience_years,
                    'education_level' => $worker?->education_level,
                    'skills' => $worker?->skills ?? [],
                    'work_types' => $worker?->work_types ?? [],
                    'salary_range' => $worker ? [
                        'min' => $worker->expected_salary_min,
                        'max' => $worker->expected_salary_max,
                    ] : null,
                    'bio' => $worker?->bio,
                ],
            ];
        });

        return response()->json(['comparison' => $comparison]);
    }
}
