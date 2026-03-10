<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\QuestionnaireResponse;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');
        $totalViews = Vacancy::where('employer_id', $employer->id)->sum('views_count');

        // Bitta query bilan ariza statistikasi
        $appStats = $vacancyIds->isNotEmpty()
            ? Application::whereIn('vacancy_id', $vacancyIds)
                ->selectRaw("COUNT(*) as total")
                ->selectRaw("SUM(CASE WHEN stage = 'hired' THEN 1 ELSE 0 END) as hired_count")
                ->selectRaw("AVG(CASE WHEN questionnaire_score IS NOT NULL THEN questionnaire_score END) as avg_score")
                ->first()
            : (object) ['total' => 0, 'hired_count' => 0, 'avg_score' => 0];

        $totalApps = (int) $appStats->total;
        $conversionRate = $totalViews > 0 ? round(($totalApps / $totalViews) * 100, 2) : 0;

        return response()->json([
            'overview' => [
                'total_views' => (int) $totalViews,
                'total_applications' => $totalApps,
                'hired_count' => (int) $appStats->hired_count,
                'avg_questionnaire_score' => round((float) ($appStats->avg_score ?? 0), 2),
                'conversion_rate' => $conversionRate,
            ],
        ]);
    }

    public function funnel(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $stages = Application::whereIn('vacancy_id', $vacancyIds)
            ->selectRaw('stage, COUNT(*) as count')
            ->groupBy('stage')
            ->pluck('count', 'stage');

        $funnel = [
            'new' => $stages->get('new', 0),
            'reviewed' => $stages->get('reviewed', 0),
            'shortlisted' => $stages->get('shortlisted', 0),
            'interview' => $stages->get('interview', 0),
            'offered' => $stages->get('offered', 0),
            'hired' => $stages->get('hired', 0),
            'rejected' => $stages->get('rejected', 0),
        ];

        return response()->json(['funnel' => $funnel]);
    }

    public function timeToHire(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $hiredApps = Application::whereIn('vacancy_id', $vacancyIds)
            ->where('stage', 'hired')
            ->select('created_at', 'updated_at')
            ->get();

        $avgDays = 0;
        if ($hiredApps->count() > 0) {
            $totalDays = $hiredApps->sum(function ($app) {
                return $app->created_at->diffInDays($app->updated_at);
            });
            $avgDays = round($totalDays / $hiredApps->count(), 1);
        }

        $byVacancy = Application::whereIn('vacancy_id', $vacancyIds)
            ->where('stage', 'hired')
            ->with('vacancy:id,title_uz,title_ru')
            ->get()
            ->groupBy('vacancy_id')
            ->map(function ($apps) {
                $avg = $apps->avg(fn($a) => $a->created_at->diffInDays($a->updated_at));
                return [
                    'vacancy' => $apps->first()->vacancy?->title_uz,
                    'avg_days' => round($avg, 1),
                    'count' => $apps->count(),
                ];
            })
            ->values();

        return response()->json([
            'avg_days_to_hire' => $avgDays,
            'total_hires' => $hiredApps->count(),
            'by_vacancy' => $byVacancy,
        ]);
    }

    public function questionnaireStats(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $responses = QuestionnaireResponse::whereHas('questionnaire', function ($q) use ($vacancyIds) {
            $q->whereIn('vacancy_id', $vacancyIds);
        });

        $totalResponses = $responses->count();
        $avgScore = $responses->avg('total_score');
        $passRate = $totalResponses > 0
            ? round(($responses->clone()->where('knockout_passed', true)->count() / $totalResponses) * 100, 1)
            : 0;

        $scoreDistribution = QuestionnaireResponse::whereHas('questionnaire', function ($q) use ($vacancyIds) {
            $q->whereIn('vacancy_id', $vacancyIds);
        })
            ->selectRaw('FLOOR(total_score / 10) * 10 as score_range, COUNT(*) as count')
            ->groupBy('score_range')
            ->orderBy('score_range')
            ->pluck('count', 'score_range');

        return response()->json([
            'total_responses' => $totalResponses,
            'avg_score' => round($avgScore ?? 0, 2),
            'pass_rate' => $passRate,
            'score_distribution' => $scoreDistribution,
        ]);
    }
}
