<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function __construct(private VacancyService $vacancyService) {}

    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancies = Vacancy::where('employer_id', $employer->id)
            ->when($request->status, fn($q, $v) => $q->where('status', $v))
            ->withCount('applications')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($vacancies);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:300',
            'category' => 'required|string|max:50',
            'description' => 'required|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'work_type' => 'required|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'experience_required' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
        ]);

        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancy = $this->vacancyService->create(array_merge($validated, [
            'employer_id' => $employer->id,
        ]));

        return response()->json(['vacancy' => $vacancy], 201);
    }

    public function show(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->withCount('applications')
            ->with('questionnaire')
            ->firstOrFail();

        return response()->json(['vacancy' => $vacancyModel]);
    }

    public function update(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $validated = $request->validate([
            'title' => 'sometimes|string|max:300',
            'category' => 'sometimes|string|max:50',
            'description' => 'sometimes|string',
            'requirements' => 'nullable|string',
            'responsibilities' => 'nullable|string',
            'work_type' => 'sometimes|string',
            'city' => 'nullable|string',
            'district' => 'nullable|string',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'salary_type' => 'nullable|string|in:fixed,range,negotiable',
            'experience_required' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
            'contact_method' => 'nullable|string|max:30',
            'status' => 'nullable|string|in:draft,paused,closed',
        ]);

        $vacancyModel->update($validated);

        return response()->json(['vacancy' => $vacancyModel->fresh()]);
    }

    public function destroy(Request $request, string $vacancy): JsonResponse
    {
        $employer = $request->user()->employerProfile;
        $vacancyModel = Vacancy::where('id', $vacancy)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $vacancyModel->delete();

        return response()->json(['message' => 'Vakansiya o\'chirildi']);
    }
}
