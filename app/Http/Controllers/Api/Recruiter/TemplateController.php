<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\QuestionnaireTemplate;
use App\Models\Vacancy;
use App\Services\QuestionnaireService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function __construct(private QuestionnaireService $questionnaireService) {}

    public function index(Request $request): JsonResponse
    {
        $templates = QuestionnaireTemplate::where('user_id', $request->user()->id)
            ->orWhere('is_public', true)
            ->orderByDesc('usage_count')
            ->paginate($request->per_page ?? 20);

        return response()->json($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'category' => 'nullable|string|max:50',
            'questions_data' => 'required|array',
            'is_public' => 'nullable|boolean',
        ]);

        $template = QuestionnaireTemplate::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'category' => $validated['category'] ?? null,
            'questions_data' => $validated['questions_data'],
            'is_public' => $validated['is_public'] ?? false,
        ]);

        return response()->json(['template' => $template], 201);
    }

    public function update(Request $request, string $template): JsonResponse
    {
        $templateModel = QuestionnaireTemplate::where('id', $template)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:200',
            'category' => 'nullable|string|max:50',
            'questions_data' => 'sometimes|array',
            'is_public' => 'nullable|boolean',
        ]);

        $templateModel->update($validated);

        return response()->json(['template' => $templateModel->fresh()]);
    }

    public function apply(Request $request, string $template): JsonResponse
    {
        $request->validate([
            'vacancy_id' => 'required|uuid|exists:vacancies,id',
        ]);

        $templateModel = QuestionnaireTemplate::findOrFail($template);
        $vacancy = Vacancy::findOrFail($request->vacancy_id);

        if ($vacancy->questionnaire) {
            return response()->json(['message' => 'Bu vakansiyada allaqachon savolnoma mavjud'], 422);
        }

        $questionnaire = $this->questionnaireService->createFromTemplate($vacancy, $templateModel);
        $vacancy->update(['has_questionnaire' => true]);

        return response()->json(['questionnaire' => $questionnaire->load('questions.options')]);
    }

    public function destroy(Request $request, string $template): JsonResponse
    {
        $templateModel = QuestionnaireTemplate::where('id', $template)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $templateModel->delete();

        return response()->json(['message' => 'Shablon o\'chirildi']);
    }
}
