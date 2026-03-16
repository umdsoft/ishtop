<?php

namespace App\Http\Controllers\Api;

use App\Enums\ApplicationStage;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreApplicationRequest;
use App\Jobs\SendNotificationJob;
use App\Models\Application;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicationController extends Controller
{
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        $vacancy = Vacancy::with(['employer:id,user_id,company_name', 'employer.user:id,first_name,last_name,telegram_id'])->findOrFail($validated['vacancy_id']);

        // Prevent applying to own vacancy
        if ($vacancy->employer && $vacancy->employer->user_id === $user->id) {
            return response()->json(['message' => 'O\'z vakansiyangizga ariza yuborib bo\'lmaydi'], 403);
        }

        // Auto-create worker profile if missing
        $worker = $user->workerProfile ?? $user->workerProfile()->create([
            'full_name' => trim("{$user->first_name} {$user->last_name}"),
        ]);

        $existing = Application::where('vacancy_id', $validated['vacancy_id'])
            ->where('worker_id', $worker->id)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Ariza allaqachon yuborilgan'], 422);
        }

        $application = DB::transaction(function () use ($validated, $worker) {
            $application = Application::create([
                'vacancy_id' => $validated['vacancy_id'],
                'worker_id' => $worker->id,
                'cover_letter' => $validated['cover_letter'] ?? null,
                'stage' => ApplicationStage::NEW,
            ]);

            Vacancy::where('id', $validated['vacancy_id'])->increment('applications_count');

            return $application;
        });

        // Notify vacancy owner about new application (async)
        if ($vacancy->employer?->user_id) {
            $owner = $vacancy->employer->user;
            if ($owner) {
                $title = $vacancy->title_uz ?: $vacancy->title_ru;
                $workerName = trim("{$user->first_name} {$user->last_name}");
                SendNotificationJob::dispatch(
                    $owner,
                    'new_application',
                    "Yangi ariza: {$title}",
                    "{$workerName} vakansiyangizga ariza yubordi",
                    [
                        'application_id' => $application->id,
                        'vacancy_id' => $vacancy->id,
                        'worker_name' => $workerName,
                    ],
                );
            }
        }

        return response()->json(['application' => $application], 201);
    }

    public function my(Request $request): JsonResponse
    {
        $worker = $request->user()->workerProfile;
        if (!$worker) {
            return response()->json(['data' => [], 'total' => 0]);
        }
        $applications = Application::where('worker_id', $worker->id)
            ->with(['vacancy:id,title_uz,title_ru,category,city,salary_min,salary_max,employer_id', 'vacancy.employer:id,company_name,logo_url'])
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function received(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        if (!$employer) {
            return response()->json(['data' => [], 'total' => 0]);
        }
        $applications = Application::whereHas('vacancy', fn($q) => $q->where('employer_id', $employer->id))
            ->with(['worker:id,full_name,city,experience_years', 'vacancy:id,title_uz,title_ru'])
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function updateStage(Request $request, Application $application): JsonResponse
    {
        $this->authorize('updateStage', $application);

        $request->validate(['stage' => 'required|string']);

        $stage = ApplicationStage::from($request->stage);
        $application->moveToStage($stage);

        return response()->json(['application' => $application->fresh()]);
    }

    public function withdraw(Request $request, Application $application): JsonResponse
    {
        $this->authorize('withdraw', $application);

        $application->delete();
        return response()->json(['message' => 'Ariza qaytarib olindi']);
    }
}
