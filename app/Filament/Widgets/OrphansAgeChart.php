<?php

namespace App\Filament\Widgets;

use App\Models\Orphan;
use Filament\Widgets\ChartWidget;

class OrphansAgeChart extends ChartWidget
{
    protected static ?string $heading = 'Distribution par Âge (Orphelins)';

    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Orphan::all()->groupBy(function($orphan) {
            $age = $orphan->age;
            if ($age === null) return 'Inconnu';
            if ($age <= 5) return '0-5 ans';
            if ($age <= 12) return '6-12 ans';
            if ($age <= 18) return '13-18 ans';
            return '18+ ans';
        })->map->count();

        $labels = ['0-5 ans', '6-12 ans', '13-18 ans', '18+ ans'];
        $counts = collect($labels)->map(fn($label) => $data->get($label, 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Nombre d\'orphelins',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(217, 119, 6, 0.8)',
                        'rgba(180, 83, 9, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(251, 191, 36)',
                        'rgb(245, 158, 11)',
                        'rgb(217, 119, 6)',
                        'rgb(180, 83, 9)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
