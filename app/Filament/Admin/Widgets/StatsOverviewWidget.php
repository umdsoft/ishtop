<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use App\Models\Vacancy;
use App\Models\Application;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Foydalanuvchilar', User::count())
                ->description('Jami ro\'yxatdan o\'tgan')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
            Stat::make('Vakansiyalar', Vacancy::where('status', 'active')->count())
                ->description('Faol e\'lonlar')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('success'),
            Stat::make('Arizalar', Application::whereDate('created_at', today())->count())
                ->description('Bugungi arizalar')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('warning'),
            Stat::make('Daromad', number_format(Payment::where('status', 'completed')->sum('amount'), 0, '.', ',') . " so'm")
                ->description('Jami to\'lovlar')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
        ];
    }
}
