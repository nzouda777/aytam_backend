<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Donation;
use App\Models\Family;
use App\Models\Sponsorship;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function statistics(Request $request)
    {
        // Statistiques principales
        $totalRaised = Donation::completed()->sum('amount');
        $lastMonthRaised = Donation::completed()
            ->whereMonth('created_at', now()->subMonth()->month)
            ->sum('amount');
        
        $percentageChange = $lastMonthRaised > 0 
            ? (($totalRaised - $lastMonthRaised) / $lastMonthRaised) * 100 
            : 0;

        $stats = [
            'total_raised' => [
                'amount' => $totalRaised,
                'percentage_change' => round($percentageChange, 1),
            ],
            'active_campaigns' => [
                'count' => Campaign::active()->count(),
                'average_progress' => Campaign::active()->avg('current_amount'),
            ],
            'total_donors' => [
                'count' => Donation::completed()->distinct('user_id')->count('user_id'),
                'percentage_change' => 8.2, // À calculer dynamiquement
            ],
            'families_helped' => [
                'count' => Family::active()->count(),
                'percentage_change' => 15.3, // À calculer dynamiquement
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function monthlyDonations(Request $request)
    {
        $months = $request->get('months', 6);
        $donations = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $amount = Donation::completed()
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->sum('amount');

            $donations[] = [
                'month' => $date->format('M'),
                'amount' => $amount,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function campaignCategories()
    {
        $categories = Category::active()
            ->withCount(['campaigns' => function($query) {
                $query->where('status', 'active');
            }])
            ->get()
            ->map(function($category) {
                return [
                    'name' => $category->name,
                    'count' => $category->campaigns_count,
                    'color' => $category->color,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function recentCampaigns(Request $request)
    {
        $limit = $request->get('limit', 5);
        
        $campaigns = Campaign::with('category')
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function recentDonations(Request $request)
    {
        $limit = $request->get('limit', 10);
        
        $donations = Donation::with(['user', 'campaign'])
            ->completed()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function alerts(Request $request)
    {
        $alerts = [];

        // Campagnes urgentes
        $urgentCampaigns = Campaign::active()
            ->urgent()
            ->where('end_date', '<=', now()->addDays(7))
            ->count();

        if ($urgentCampaigns > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'campaign',
                'title' => 'Campagnes urgentes',
                'message' => "{$urgentCampaigns} campagne(s) urgente(s) se terminent bientôt",
            ];
        }

        // Familles en attente
        $pendingFamilies = Family::pending()->count();
        if ($pendingFamilies > 0) {
            $alerts[] = [
                'type' => 'info',
                'category' => 'family',
                'title' => 'Familles en attente',
                'message' => "{$pendingFamilies} famille(s) en attente de validation",
            ];
        }

        // Parrainages expirant bientôt
        $expiringSponsorships = Sponsorship::active()
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now()->addDays(30))
            ->count();

        if ($expiringSponsorships > 0) {
            $alerts[] = [
                'type' => 'warning',
                'category' => 'sponsorship',
                'title' => 'Parrainages expirant',
                'message' => "{$expiringSponsorships} parrainage(s) expirent dans 30 jours",
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $alerts,
        ]);
    }

    public function overview(Request $request)
    {
        $data = [
            'statistics' => $this->statistics($request)->getData()->data,
            'monthly_donations' => $this->monthlyDonations($request)->getData()->data,
            'campaign_categories' => $this->campaignCategories()->getData()->data,
            'recent_campaigns' => $this->recentCampaigns($request)->getData()->data,
            'recent_donations' => $this->recentDonations($request)->getData()->data,
            'alerts' => $this->alerts($request)->getData()->data,
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function donorStats(Request $request)
    {
        $userId = $request->user()->id;

        $stats = [
            'total_donated' => Donation::completed()
                ->where('user_id', $userId)
                ->sum('amount'),
            'donation_count' => Donation::completed()
                ->where('user_id', $userId)
                ->count(),
            'active_sponsorships' => Sponsorship::active()
                ->where('user_id', $userId)
                ->count(),
            'campaigns_supported' => Donation::completed()
                ->where('user_id', $userId)
                ->distinct('campaign_id')
                ->count('campaign_id'),
            'recent_donations' => Donation::with('campaign')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}