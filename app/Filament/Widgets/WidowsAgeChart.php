<?php

namespace App\Filament\Widgets;

use App\Models\Family;
use Filament\Widgets\ChartWidget;

class WidowsAgeChart extends ChartWidget
{
    protected static ?string $heading = 'Distribution par Âge (Veuves)';

    protected static ?int $sort = 6;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = Family::all()->groupBy(function($family) {
            $age = $family->widow_age;
            if ($age === null) return 'Inconnu';
            if ($age < 30) return '< 30 ans';
            if ($age <= 45) return '30-45 ans';
            if ($age <= 60) return '45-60 ans';
            return '60+ ans';
        })->map->count();

        $labels = ['< 30 ans', '30-45 ans', '45-60 ans', '60+ ans'];
        $counts = collect($labels)->map(fn($label) => $data->get($label, 0))->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Nombre de veuves',
                    'data' => $counts,
                    'backgroundColor' => [
                        'rgba(244, 63, 94, 0.8)',
                        'rgba(225, 29, 72, 0.8)',
                        'rgba(190, 18, 60, 0.8)',
                        'rgba(159, 18, 57, 0.8)',
                    ],
                    'borderColor' => [
                        'rgb(244, 63, 94)',
                        'rgb(225, 29, 72)',
                        'rgb(190, 18, 60)',
                        'rgb(159, 18, 57)',
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
