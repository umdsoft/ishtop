<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\EmployerProfile;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $stats = Cache::remember('admin:dashboard:stats', 120, function () {
            $todayUsers = User::whereDate('created_at', today())->count();
            $yesterdayUsers = User::whereDate('created_at', today()->subDay())->count();

            $activeVacancies = Vacancy::where('status', 'active')->count();
            $pendingVacancies = Vacancy::where('status', 'pending')->count();

            $todayApps = Application::whereDate('created_at', today())->count();
            $weekApps = Application::where('created_at', '>=', now()->subWeek())->count();

            $totalRevenue = Payment::where('status', 'completed')->sum('amount');
            $monthRevenue = Payment::where('status', 'completed')
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('amount');

            return [
                'total_users' => User::count(),
                'today_users' => $todayUsers,
                'yesterday_users' => $yesterdayUsers,
                'workers' => WorkerProfile::count(),
                'employers' => EmployerProfile::count(),
                'active_vacancies' => $activeVacancies,
                'pending_vacancies' => $pendingVacancies,
                'total_vacancies' => Vacancy::count(),
                'today_apps' => $todayApps,
                'week_apps' => $weekApps,
                'total_apps' => Application::count(),
                'total_revenue' => $totalRevenue,
                'month_revenue' => $monthRevenue,
                'total_views' => Vacancy::sum('views_count'),
            ];
        });

        $userTrend = $stats['yesterday_users'] > 0
            ? round(($stats['today_users'] - $stats['yesterday_users']) / $stats['yesterday_users'] * 100)
            : ($stats['today_users'] > 0 ? 100 : 0);

        // 7-day sparkline data
        $users7d = Cache::remember('admin:chart:User', 300, function () {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $data[] = User::whereDate('created_at', today()->subDays($i))->count();
            }
            return $data;
        });

        $vacancies7d = Cache::remember('admin:chart:vacancies_7d', 300, function () {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $data[] = Vacancy::whereDate('created_at', today()->subDays($i))->count();
            }
            return $data;
        });

        return response()->json([
            'stats' => $stats,
            'user_trend' => $userTrend,
            'sparklines' => [
                'users' => $users7d,
                'vacancies' => $vacancies7d,
            ],
        ]);
    }

    public function chart(string $type): JsonResponse
    {
        return match ($type) {
            'registrations' => $this->registrationsChart(),
            'revenue' => $this->revenueChart(),
            'categories' => $this->categoriesChart(),
            default => response()->json(['message' => 'Chart topilmadi'], 404),
        };
    }

    private function registrationsChart(): JsonResponse
    {
        $data = Cache::remember('admin:chart:registrations', 300, function () {
            $counts = [];
            $labels = [];

            for ($i = 13; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d.m');
                $counts[] = User::whereDate('created_at', $date)->count();
            }

            return compact('counts', 'labels');
        });

        return response()->json($data);
    }

    private function revenueChart(): JsonResponse
    {
        $data = Cache::remember('admin:chart:revenue', 600, function () {
            $amounts = [];
            $labels = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $labels[] = $date->translatedFormat('M');
                $amounts[] = (float) Payment::where('status', 'completed')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount');
            }

            return compact('amounts', 'labels');
        });

        return response()->json($data);
    }

    private function categoriesChart(): JsonResponse
    {
        $data = Cache::remember('admin:chart:categories', 600, function () {
            $topSlugs = Vacancy::select('category', DB::raw('count(*) as total'))
                ->where('status', 'active')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            // slug → name_uz mapping
            $slugs = $topSlugs->pluck('category')->toArray();
            $nameMap = \App\Models\Category::whereIn('slug', $slugs)
                ->pluck('name_uz', 'slug');

            return [
                'counts' => $topSlugs->pluck('total')->toArray(),
                'labels' => $topSlugs->pluck('category')->map(fn($s) => $nameMap->get($s, $s))->toArray(),
            ];
        });

        return response()->json($data);
    }

    public function pendingVacancies(): JsonResponse
    {
        $vacancies = Vacancy::where('status', 'pending')
            ->with('employer:id,company_name')
            ->latest()
            ->get(['id', 'title_uz', 'title_ru', 'category', 'city', 'employer_id', 'created_at']);

        return response()->json(['vacancies' => $vacancies]);
    }

    public function latestVacancies(): JsonResponse
    {
        $vacancies = Vacancy::with('employer:id,company_name')
            ->latest()
            ->limit(10)
            ->get(['id', 'title_uz', 'title_ru', 'category', 'city', 'district', 'status', 'views_count', 'employer_id', 'salary_min', 'salary_max', 'created_at']);

        // slug → name_uz
        $slugs = $vacancies->pluck('category')->unique()->filter()->toArray();
        $catNames = \App\Models\Category::whereIn('slug', $slugs)->pluck('name_uz', 'slug');

        $vacancies->each(function ($v) use ($catNames) {
            $v->category_name = $catNames->get($v->category, $v->category);
        });

        return response()->json(['vacancies' => $vacancies]);
    }
}
