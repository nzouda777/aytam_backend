<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use Filament\Widgets\ChartWidget;

class CampaignsChart extends ChartWidget
{
    protected static ?string $heading = 'Progression des Campagnes Actives';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $campaigns = Campaign::where('status', 'active')
            ->orderByDesc('current_amount')
            ->limit(8)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Collecté (FCFA)',
                    'data' => $campaigns->pluck('current_amount')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.7)',
                    'borderRadius' => 4,
                ],
                [
                    'label' => 'Restant (FCFA)',
                    'data' => $campaigns->map(fn ($c) => max(0, $c->goal_amount - $c->current_amount))->toArray(),
                    'backgroundColor' => 'rgba(229, 231, 235, 0.7)',
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $campaigns->pluck('title')->map(fn ($t) => \Illuminate\Support\Str::limit($t, 20))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => ['stacked' => true],
                'y' => ['stacked' => true],
            ],
        ];
    }
}
