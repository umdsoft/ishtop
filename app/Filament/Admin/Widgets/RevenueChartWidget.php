<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Oylik daromad';
    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->translatedFormat('M Y');
            $data[] = Payment::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Daromad',
                    'data' => $data,
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'borderColor' => 'rgb(79, 70, 229)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
