<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Vacancy;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopCategoriesWidget extends ChartWidget
{
    protected static ?string $heading = 'Top kategoriyalar';
    protected static ?int $sort = 6;

    protected function getData(): array
    {
        $categories = Vacancy::select('category', DB::raw('count(*) as total'))
            ->where('status', 'active')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Vakansiyalar soni',
                    'data' => $categories->pluck('total')->toArray(),
                    'backgroundColor' => ['#6366f1', '#8b5cf6', '#a78bfa', '#c4b5fd', '#ddd6fe'],
                ],
            ],
            'labels' => $categories->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
