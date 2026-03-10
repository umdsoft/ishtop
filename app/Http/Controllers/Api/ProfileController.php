<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function workerShow(Request $request): JsonResponse
    {
        $profile = $request->user()->workerProfile;
        return response()->json(['profile' => $profile]);
    }

    public function workerDetail(WorkerProfile $worker): JsonResponse
    {
        $worker->increment('views_count');

        $user = $worker->user;

        $data = $worker->only([
            'id', 'full_name', 'birth_date', 'city', 'district', 'specialty', 'employment_status',
            'experience_years', 'expected_salary_min', 'expected_salary_max',
            'skills', 'work_types', 'bio', 'work_experience',
            'photo_url', 'education_level', 'gender',
            'resume_file_url', 'linkedin_url',
        ]);

        // User contact info
        $data['phone'] = $user?->phone;
        $data['telegram_username'] = $user?->username;

        return response()->json(['profile' => $data]);
    }

    public function workerUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'nullable|string|max:200',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|string|in:male,female',
            'employment_status' => 'nullable|string|in:student,unemployed,employed',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'education_level' => 'nullable|string|max:50',
            'specialty' => 'nullable|string|max:200',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:100',
            'expected_salary_min' => 'nullable|integer|min:0',
            'expected_salary_max' => 'nullable|integer|min:0|gte:expected_salary_min',
            'work_types' => 'nullable|array',
            'work_types.*' => 'string|max:30',
            'preferred_categories' => 'nullable|array',
            'preferred_categories.*' => 'string|max:100',
            'bio' => 'nullable|string|max:2000',
            'work_experience' => 'nullable|array|max:10',
            'work_experience.*.company' => 'required|string|max:200',
            'work_experience.*.position' => 'required|string|max:200',
            'work_experience.*.start_date' => 'required|string|max:20',
            'work_experience.*.end_date' => 'nullable|string|max:20',
            'work_experience.*.description' => 'nullable|string|max:500',
            'linkedin_url' => 'nullable|url|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $profile = $request->user()->workerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        // Clear recommended cache so new profile data takes effect immediately
        cache()->forget("recommended_worker_{$profile->id}");

        return response()->json(['profile' => $profile]);
    }

    public function updateSearchStatus(Request $request): JsonResponse
    {
        $request->validate(['status' => 'required|in:open,passive,closed']);

        $workerProfile = $request->user()->workerProfile;

        if (!$workerProfile) {
            return response()->json(['message' => 'Worker profili topilmadi'], 404);
        }

        $workerProfile->update(['search_status' => $request->status]);

        return response()->json(['message' => 'Status yangilandi']);
    }

    public function employerShow(Request $request): JsonResponse
    {
        $profile = $request->user()->employerProfile;
        return response()->json(['profile' => $profile]);
    }

    public function employerUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'nullable|string|max:200',
            'industry' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:3000',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:500',
            'employees_count' => 'nullable|string|max:20',
            'stir_number' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        $profile = $request->user()->employerProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
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

        $validated = $request->validate([
            'owner_name' => 'nullable|string|max:200',
            'position' => 'nullable|string|max:100',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'education' => 'nullable|string|max:1000',
            'achievements' => 'nullable|string|max:2000',
            'bio' => 'nullable|string|max:2000',
        ]);

        $resume = $employer->resume()->updateOrCreate(
            ['employer_profile_id' => $employer->id],
            $validated
        );

        return response()->json(['resume' => $resume]);
    }
}
