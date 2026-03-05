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

        $vacancyIds = Vacancy::where('employer_id', $employer->id)->pluck('id');

        $totalVacancies = $vacancyIds->count();
        $activeVacancies = Vacancy::where('employer_id', $employer->id)->active()->count();
        $totalApplications = Application::whereIn('vacancy_id', $vacancyIds)->count();
        $newApplications = Application::whereIn('vacancy_id', $vacancyIds)->where('stage', 'new')->count();
        $totalViews = Vacancy::where('employer_id', $employer->id)->sum('views_count');
        $hiredCount = Application::whereIn('vacancy_id', $vacancyIds)->where('stage', 'hired')->count();

        return response()->json([
            'stats' => [
                'total_vacancies' => $totalVacancies,
                'active_vacancies' => $activeVacancies,
                'total_applications' => $totalApplications,
                'new_applications' => $newApplications,
                'total_views' => $totalViews,
                'hired_count' => $hiredCount,
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
                'vacancy:id,title,city',
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
