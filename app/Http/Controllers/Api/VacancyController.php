<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vacancy;
use App\Services\GeoService;
use App\Services\MatchingService;
use App\Services\VacancyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VacancyController extends Controller
{
    public function __construct(
        private VacancyService $vacancyService,
        private GeoService $geoService,
        private MatchingService $matchingService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $vacancies = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->when($request->category, fn($q, $v) => $q->where('category', $v))
            ->when($request->city, fn($q, $v) => $q->where('city', $v))
            ->when($request->work_type, fn($q, $v) => $q->where('work_type', $v))
            ->when($request->salary_min, fn($q, $v) => $q->where('salary_max', '>=', $v))
            ->when($request->salary_max, fn($q, $v) => $q->where('salary_min', '<=', $v))
            ->when($request->q, fn($q, $v) => $q->search($v))
            ->orderByDesc('is_top')
            ->orderByDesc('published_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($vacancies);
    }

    public function show(Vacancy $vacancy): JsonResponse
    {
        $this->vacancyService->incrementViews($vacancy);
        $vacancy->load(['employer.user:id,first_name', 'questionnaire:id,vacancy_id,questions_count']);

        return response()->json(['vacancy' => $vacancy]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'required_without:title_ru|nullable|string|max:300',
            'title_ru' => 'required_without:title_uz|nullable|string|max:300',
            'category' => 'required|string|max:50',
            'description_uz' => 'required_without:description_ru|nullable|string',
            'description_ru' => 'required_without:description_uz|nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
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

    public function update(Request $request, Vacancy $vacancy): JsonResponse
    {
        $validated = $request->validate([
            'language' => 'nullable|string|in:uz,ru',
            'title_uz' => 'sometimes|string|max:300',
            'title_ru' => 'nullable|string|max:300',
            'category' => 'sometimes|string|max:50',
            'description_uz' => 'sometimes|string',
            'description_ru' => 'nullable|string',
            'requirements_uz' => 'nullable|string',
            'requirements_ru' => 'nullable|string',
            'responsibilities_uz' => 'nullable|string',
            'responsibilities_ru' => 'nullable|string',
            'work_type' => 'sometimes|string',
            'city' => 'nullable|string',
            'salary_min' => 'nullable|integer',
            'salary_max' => 'nullable|integer',
            'experience_required' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:20',
        ]);

        $vacancy->update($validated);

        return response()->json(['vacancy' => $vacancy->fresh()]);
    }

    public function destroy(Vacancy $vacancy): JsonResponse
    {
        $vacancy->delete();
        return response()->json(['message' => 'Deleted']);
    }

    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:50',
        ]);

        $vacancies = $this->geoService
            ->nearbyVacancies($request->lat, $request->lng, $request->radius ?? 10)
            ->with('employer:id,company_name,logo_url')
            ->paginate(20);

        return response()->json($vacancies);
    }

    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();
        $worker = $user->workerProfile;

        if ($worker) {
            $vacancies = $this->matchingService->findMatchesForWorker($worker, 10);
        } else {
            $vacancies = Vacancy::active()
                ->with('employer:id,company_name,logo_url')
                ->orderByDesc('is_top')
                ->orderByDesc('published_at')
                ->limit(10)
                ->get();
        }

        return response()->json(['vacancies' => $vacancies]);
    }
}
