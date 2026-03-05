<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function workerShow(Request $request): JsonResponse
    {
        $profile = $request->user()->workerProfile;
        return response()->json(['profile' => $profile]);
    }

    public function workerUpdate(Request $request): JsonResponse
    {
        $profile = $request->user()->workerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only([
                'full_name', 'birth_date', 'gender', 'city', 'district',
                'education_level', 'specialty', 'experience_years', 'skills',
                'expected_salary_min', 'expected_salary_max', 'work_types',
                'bio', 'latitude', 'longitude',
            ])
        );

        return response()->json(['profile' => $profile]);
    }

    public function updateSearchStatus(Request $request): JsonResponse
    {
        $request->validate(['status' => 'required|in:open,passive,closed']);

        $request->user()->workerProfile->update(['search_status' => $request->status]);

        return response()->json(['message' => 'Status yangilandi']);
    }

    public function employerShow(Request $request): JsonResponse
    {
        $profile = $request->user()->employerProfile;
        return response()->json(['profile' => $profile]);
    }

    public function employerUpdate(Request $request): JsonResponse
    {
        $profile = $request->user()->employerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $request->only([
                'company_name', 'industry', 'description', 'address',
                'phone', 'website', 'employees_count', 'stir_number',
                'latitude', 'longitude',
            ])
        );

        return response()->json(['profile' => $profile]);
    }

    public function employerResumeShow(Request $request): JsonResponse
    {
        $resume = $request->user()->employerProfile?->resume;
        return response()->json(['resume' => $resume]);
    }

    public function employerResumeUpdate(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $resume = $employer->resume()->updateOrCreate(
            ['employer_profile_id' => $employer->id],
            $request->only(['owner_name', 'position', 'experience_years', 'education', 'achievements', 'bio'])
        );

        return response()->json(['resume' => $resume]);
    }
}
