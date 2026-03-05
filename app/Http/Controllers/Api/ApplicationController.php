<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStage;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vacancy_id' => 'required|uuid|exists:vacancies,id',
            'cover_letter' => 'nullable|string|max:2000',
        ]);

        $worker = $request->user()->workerProfile;
        if (!$worker) {
            return response()->json(['message' => 'Worker profili kerak'], 403);
        }

        $existing = Application::where('vacancy_id', $request->vacancy_id)
            ->where('worker_id', $worker->id)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Ariza allaqachon yuborilgan'], 422);
        }

        $application = Application::create([
            'vacancy_id' => $request->vacancy_id,
            'worker_id' => $worker->id,
            'cover_letter' => $request->cover_letter,
            'stage' => ApplicationStage::NEW,
        ]);

        Vacancy::where('id', $request->vacancy_id)->increment('applications_count');

        return response()->json(['application' => $application], 201);
    }

    public function my(Request $request): JsonResponse
    {
        $worker = $request->user()->workerProfile;
        $applications = Application::where('worker_id', $worker?->id)
            ->with('vacancy:id,title,category,city,salary_min,salary_max')
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function received(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $applications = Application::whereHas('vacancy', fn($q) => $q->where('employer_id', $employer?->id))
            ->with(['worker:id,full_name,city,experience_years', 'vacancy:id,title'])
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function updateStage(Request $request, Application $application): JsonResponse
    {
        $request->validate(['stage' => 'required|string']);

        $stage = ApplicationStage::from($request->stage);
        $application->moveToStage($stage);

        return response()->json(['application' => $application->fresh()]);
    }

    public function withdraw(Application $application): JsonResponse
    {
        $application->delete();
        return response()->json(['message' => 'Ariza qaytarib olindi']);
    }
}
