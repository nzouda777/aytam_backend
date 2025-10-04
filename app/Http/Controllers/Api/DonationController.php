<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Campaign;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function index(Request $request)
    {
        $query = Donation::with(['user', 'campaign']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Recherche par email ou nom
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_email', 'like', "%{$search}%");
            });
        }

        // Filtres de date
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $donations = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'required|numeric|min:1',
            'donor_name' => 'required_without:user_id|string|max:255',
            'donor_email' => 'required_without:user_id|email|max:255',
            'donor_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:card,bank_transfer,mobile_money,cash',
            'is_anonymous' => 'sometimes|boolean',
            'is_recurring' => 'sometimes|boolean',
            'message' => 'nullable|string',
        ]);

        $data = $request->all();
        
        // Si l'utilisateur est connecté
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
            $data['donor_name'] = $request->user()->name;
            $data['donor_email'] = $request->user()->email;
        }

        // Créer le don avec statut pending
        $donation = Donation::create($data);

        // TODO: Intégrer le traitement du paiement ici
        // Pour l'instant, on simule un paiement réussi
        $donation->update([
            'status' => 'completed',
            'payment_date' => now(),
        ]);

        // Mettre à jour le montant de la campagne
        if ($donation->campaign_id) {
            $campaign = Campaign::find($donation->campaign_id);
            $campaign->current_amount += $donation->amount;
            $campaign->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Don effectué avec succès',
            'data' => $donation->load(['campaign', 'user']),
        ], 201);
    }

    public function show($id)
    {
        $donation = Donation::with(['user', 'campaign'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $donation,
        ]);
    }

    public function userDonations(Request $request)
    {
        $donations = Donation::with('campaign')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function campaignDonations($campaignId)
    {
        $donations = Donation::with('user')
            ->where('campaign_id', $campaignId)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $donations,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,failed,refunded',
        ]);

        $donation = Donation::findOrFail($id);
        
        $oldStatus = $donation->status;
        $donation->status = $request->status;

        // Si le statut passe à completed
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $donation->payment_date = now();
            
            // Mettre à jour le montant de la campagne
            if ($donation->campaign_id) {
                $campaign = Campaign::find($donation->campaign_id);
                $campaign->current_amount += $donation->amount;
                $campaign->save();
            }
        }

        // Si le statut passe à refunded et était completed
        if ($request->status === 'refunded' && $oldStatus === 'completed') {
            if ($donation->campaign_id) {
                $campaign = Campaign::find($donation->campaign_id);
                $campaign->current_amount -= $donation->amount;
                $campaign->save();
            }
        }

        $donation->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut du don mis à jour avec succès',
            'data' => $donation->load(['campaign', 'user']),
        ]);
    }

    public function statistics(Request $request)
    {
        $stats = [
            'total_donations' => Donation::completed()->sum('amount'),
            'total_count' => Donation::completed()->count(),
            'average_donation' => Donation::completed()->avg('amount'),
            'today_donations' => Donation::completed()->whereDate('created_at', today())->sum('amount'),
            'month_donations' => Donation::completed()->whereMonth('created_at', now()->month)->sum('amount'),
            'year_donations' => Donation::completed()->whereYear('created_at', now()->year)->sum('amount'),
            'recurring_donations' => Donation::completed()->where('is_recurring', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}