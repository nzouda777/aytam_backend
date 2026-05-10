<?php

namespace App\Filament\Widgets;

use App\Models\Donation;
use App\Models\Orphan;
use App\Models\Sponsorship;
use Filament\Widgets\ChartWidget;

class SponsorshipsChart extends ChartWidget
{
    protected static ?string $heading = 'Répartition des Parrainages';

    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'data' => [
                        Sponsorship::where('status', 'active')->count(),
                        Sponsorship::where('status', 'paused')->count(),
                        Sponsorship::where('status', 'completed')->count(),
                        Sponsorship::where('status', 'cancelled')->count(),
                    ],
                    'backgroundColor' => [
                        'rgb(16, 185, 129)',  // active - green
                        'rgb(245, 158, 11)',  // paused - amber
                        'rgb(59, 130, 246)',  // completed - blue
                        'rgb(239, 68, 68)',   // cancelled - red
                    ],
                ],
            ],
            'labels' => ['Actifs', 'En pause', 'Terminés', 'Annulés'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
