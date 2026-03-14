<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Vacancy;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TopCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top kategoriyalar';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;
    protected static ?string $maxHeight = '280px';

    protected function getData(): array
    {
        return Cache::remember('admin:chart:categories', 600, function () {
            $categories = Vacancy::select('category', DB::raw('count(*) as total'))
                ->where('status', 'active')
                ->whereNotNull('category')
                ->groupBy('category')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            return [
                'datasets' => [
                    [
                        'label' => 'Vakansiyalar',
                        'data' => $categories->pluck('total')->toArray(),
                        'backgroundColor' => [
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                        ],
                        'borderWidth' => 0,
                    ],
                ],
                'labels' => $categories->pluck('category')->toArray(),
            ];
        });
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }
}
