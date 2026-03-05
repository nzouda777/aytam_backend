<?php

namespace App\Filament\Widgets;

use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Family;
use App\Models\Orphan;
use App\Models\Sponsorship;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Real trend data for last 7 months
        $donationTrend = collect(range(6, 0, -1))->map(fn ($i) =>
            Donation::where('status', 'completed')
                ->whereMonth('created_at', Carbon::now()->subMonths($i)->month)
                ->whereYear('created_at', Carbon::now()->subMonths($i)->year)
                ->count()
        )->toArray();

        $totalDonations = Donation::where('status', 'completed')->sum('amount');
        $thisMonthDonations = Donation::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');
        $lastMonthDonations = Donation::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');
        $donationGrowth = $lastMonthDonations > 0
            ? round((($thisMonthDonations - $lastMonthDonations) / $lastMonthDonations) * 100)
            : 0;

        $activeCampaigns = Campaign::where('status', 'active')->count();
        $campaignGoalTotal = Campaign::where('status', 'active')->sum('goal_amount');
        $campaignCollected = Campaign::where('status', 'active')->sum('current_amount');
        $avgProgress = $campaignGoalTotal > 0 ? round(($campaignCollected / $campaignGoalTotal) * 100) : 0;

        return [
            Stat::make('Total Dons', number_format($totalDonations, 0, ',', ' ') . ' FCFA')
                ->description($donationGrowth >= 0 ? "+{$donationGrowth}% ce mois" : "{$donationGrowth}% ce mois")
                ->descriptionIcon($donationGrowth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($donationGrowth >= 0 ? 'success' : 'danger')
                ->chart($donationTrend),

            Stat::make('Campagnes Actives', $activeCampaigns)
                ->description("Progression moyenne : {$avgProgress}%")
                ->descriptionIcon('heroicon-m-megaphone')
                ->color('info')
                ->chart([3, 5, 4, 7, 6, 8, $activeCampaigns]),

            Stat::make('Orphelins', Orphan::count())
                ->description('Parrainés : ' . Orphan::where('is_sponsored', true)->count())
                ->descriptionIcon('heroicon-m-heart')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, Orphan::count()]),

            Stat::make('Familles (Veuves)', Family::count())
                ->description('Total enregistrées')
                ->descriptionIcon('heroicon-m-home')
                ->color('warning')
                ->chart([3, 5, 2, 7, 4, 6, Family::count()]),

            Stat::make('Parrainages Actifs', Sponsorship::where('status', 'active')->count())
                ->description(number_format(Sponsorship::where('status', 'active')->sum('monthly_amount'), 0, ',', ' ') . ' FCFA/mois')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('success')
                ->chart([2, 4, 6, 5, 7, 4, Sponsorship::where('status', 'active')->count()]),

            Stat::make('Utilisateurs', User::count())
                ->description('Dons en attente : ' . Donation::where('status', 'pending')->count())
                ->descriptionIcon('heroicon-m-users')
                ->color('gray')
                ->chart([5, 3, 7, 4, 6, 5, User::count()]),
        ];
    }
}
