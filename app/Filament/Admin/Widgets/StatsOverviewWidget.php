<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Application;
use App\Models\EmployerProfile;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
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

        return [
            Stat::make('Foydalanuvchilar', number_format($stats['total_users']))
                ->description($stats['today_users'] . ' ta bugun' . ($userTrend !== 0 ? " ({$userTrend}%)" : ''))
                ->descriptionIcon($userTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($userTrend >= 0 ? 'success' : 'danger')
                ->chart($this->getLast7DaysData(User::class)),

            Stat::make('Ishchilar / Ish beruvchilar', $stats['workers'] . ' / ' . $stats['employers'])
                ->description('Ro\'yxatdan o\'tgan profillar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Faol vakansiyalar', number_format($stats['active_vacancies']))
                ->description($stats['pending_vacancies'] . ' ta moderatsiyada')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color($stats['pending_vacancies'] > 0 ? 'warning' : 'success')
                ->chart($this->getLast7DaysVacancies()),

            Stat::make('Arizalar', number_format($stats['total_apps']))
                ->description($stats['today_apps'] . ' bugun, ' . $stats['week_apps'] . ' haftalik')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('info'),

            Stat::make('Daromad', number_format($stats['total_revenue'], 0, '.', ',') . " so'm")
                ->description(number_format($stats['month_revenue'], 0, '.', ',') . " so'm bu oy")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Ko\'rishlar', number_format($stats['total_views']))
                ->description('Jami vakansiya ko\'rishlar')
                ->descriptionIcon('heroicon-m-eye')
                ->color('gray'),
        ];
    }

    protected function getLast7DaysData(string $model): array
    {
        return Cache::remember('admin:chart:' . class_basename($model), 300, function () use ($model) {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $data[] = $model::whereDate('created_at', today()->subDays($i))->count();
            }
            return $data;
        });
    }

    protected function getLast7DaysVacancies(): array
    {
        return Cache::remember('admin:chart:vacancies_7d', 300, function () {
            $data = [];
            for ($i = 6; $i >= 0; $i--) {
                $data[] = Vacancy::whereDate('created_at', today()->subDays($i))->count();
            }
            return $data;
        });
    }
}
