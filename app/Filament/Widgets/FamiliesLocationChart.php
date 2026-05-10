<?php

namespace App\Filament\Widgets;

use App\Models\Family;
use Filament\Widgets\ChartWidget;

class FamiliesLocationChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition Géographique (Familles)';

    protected static ?int $sort = 7;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Family::query()
            ->selectRaw('city, count(*) as count')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Familles',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        '#10b981',
                        '#3b82f6',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                    ],
                ],
            ],
            'labels' => $data->pluck('city')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
