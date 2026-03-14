<?php

namespace App\Filament\Admin\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class UserRegistrationChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ro\'yxatdan o\'tishlar (14 kun)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        return Cache::remember('admin:chart:registrations', 300, function () {
            $data = [];
            $labels = [];

            for ($i = 13; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $labels[] = $date->format('d.m');
                $data[] = User::whereDate('created_at', $date)->count();
            }

            return [
                'datasets' => [
                    [
                        'label' => 'Yangi foydalanuvchilar',
                        'data' => $data,
                        'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                        'borderColor' => 'rgb(99, 102, 241)',
                        'borderRadius' => 4,
                    ],
                ],
                'labels' => $labels,
            ];
        });
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }
}
