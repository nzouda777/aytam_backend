<?php

namespace App\Filament\Widgets;

use App\Models\Orphan;
use Filament\Widgets\ChartWidget;

class OrphansGenderChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition par Sexe (Orphelins)';

    protected static ?int $sort = 8;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $maleCount = Orphan::where('gender', 'male')->count();
        $femaleCount = Orphan::where('gender', 'female')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Orphelins',
                    'data' => [$maleCount, $femaleCount],
                    'backgroundColor' => [
                        '#3b82f6', // Info/Blue for Male
                        '#f43f5e', // Danger/Pink for Female
                    ],
                ],
            ],
            'labels' => ['Masculin', 'Féminin'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
