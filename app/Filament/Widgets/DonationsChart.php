<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Donation;
use App\Models\Program;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DonationsChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des Dons (12 mois)';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'all';

    /**
     * Filtre du graphique : toutes les transactions, une catégorie de
     * campagnes, ou un programme précis.
     */
    protected function getFilters(): ?array
    {
        $filters = ['all' => 'Toutes les transactions'];

        foreach (Category::all() as $category) {
            $filters["cat-{$category->id}"] = 'Catégorie : '.$category->name;
        }

        foreach (Program::all() as $program) {
            $filters["prog-{$program->id}"] = 'Programme : '.$program->title;
        }

        return $filters;
    }

    protected function baseQuery(): Builder
    {
        $query = Donation::where('status', 'completed');

        if (str_starts_with($this->filter ?? 'all', 'cat-')) {
            $categoryId = (int) substr($this->filter, 4);
            $query->whereHas('campaign', fn (Builder $q) => $q->where('category_id', $categoryId));
        } elseif (str_starts_with($this->filter ?? 'all', 'prog-')) {
            $query->where('program_id', (int) substr($this->filter, 5));
        }

        return $query;
    }

    protected function getData(): array
    {
        $months = collect(range(11, 0, -1))->map(function ($monthsAgo) {
            $date = Carbon::now()->subMonths($monthsAgo);

            return [
                'month' => $date->translatedFormat('M y'),
                'total' => (clone $this->baseQuery())
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('amount'),
                'count' => (clone $this->baseQuery())
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Montant (FCFA)',
                    'data' => $months->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => 'Nombre de dons',
                    'data' => $months->pluck('count')->toArray(),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.15)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                    'yAxisID' => 'y1',
                ],
            ],
            'labels' => $months->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'left',
                ],
                'y1' => [
                    'type' => 'linear',
                    'display' => true,
                    'position' => 'right',
                    'grid' => ['drawOnChartArea' => false],
                ],
            ],
        ];
    }
}
