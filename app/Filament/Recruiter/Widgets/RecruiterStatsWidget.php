<?php

namespace App\Filament\Recruiter\Widgets;

use App\Models\Vacancy;
use App\Models\Application;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RecruiterStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = auth()->id();

        return [
            Stat::make('Vakansiyalarim', Vacancy::whereHas('employer', fn($q) => $q->where('user_id', $userId))->count())
                ->description('Jami e\'lonlar')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('primary'),
            Stat::make('Faol e\'lonlar', Vacancy::whereHas('employer', fn($q) => $q->where('user_id', $userId))->where('status', 'active')->count())
                ->description('Hozir faol')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('Yangi arizalar', Application::whereHas('vacancy.employer', fn($q) => $q->where('user_id', $userId))->where('stage', 'new')->count())
                ->description('Ko\'rilmagan')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
        ];
    }
}
