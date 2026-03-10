<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Vacancy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        // Bitta query bilan vakansiya statistikasi
        $vacancyStats = Vacancy::where('employer_id', $employer->id)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->selectRaw("COALESCE(SUM(views_count), 0) as total_views")
            ->first();

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        // Bitta query bilan ariza statistikasi
        $appStats = $vacancyIds->isNotEmpty()
            ? Application::whereIn('vacancy_id', $vacancyIds)
                ->selectRaw("COUNT(*) as total")
                ->selectRaw("SUM(CASE WHEN stage = 'new' THEN 1 ELSE 0 END) as new_count")
                ->selectRaw("SUM(CASE WHEN stage = 'hired' THEN 1 ELSE 0 END) as hired_count")
                ->first()
            : (object) ['total' => 0, 'new_count' => 0, 'hired_count' => 0];

        return response()->json([
            'stats' => [
                'total_vacancies' => (int) $vacancyStats->total,
                'active_vacancies' => (int) $vacancyStats->active,
                'total_applications' => (int) $appStats->total,
                'new_applications' => (int) $appStats->new_count,
                'total_views' => (int) $vacancyStats->total_views,
                'hired_count' => (int) $appStats->hired_count,
            ],
        ]);
    }

    public function recentApplications(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $applications = Application::whereIn('vacancy_id', $vacancyIds)
            ->with([
                'vacancy:id,title_uz,title_ru,city',
                'worker:id,full_name,specialty,city',
            ])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return response()->json(['applications' => $applications]);
    }

    public function activityChart(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $days = (int) ($request->days ?? 30);
        $startDate = now()->subDays($days);

        $applications = Application::whereIn('vacancy_id', $vacancyIds)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'chart' => $applications,
            'period_days' => $days,
        ]);
    }
}
