<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Services\NotchPayService;

class DonationController extends Controller
{
    protected $notchPayService;

    public function __construct(NotchPayService $notchPayService)
    {
        $this->notchPayService = $notchPayService;
    }

    public function index(Request $request)
    {
        // ... (existing index code)
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

        $donations = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $donations->toArray()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'amount' => 'required|numeric|min:100',
            'donor_name' => 'required_without:user_id|string|max:255',
            'donor_email' => 'max:255',
            'donor_phone' => 'nullable|string|max:20',
            'payment_method' => 'required|in:cash,orange_money,mobile_money',
            // 'payment_type' => 'sometimes|in:campaign_donation,family_sponsorship,orphan_sponsorship',
            'is_anonymous' => 'sometimes|boolean',
            'is_recurring' => 'sometimes|boolean',
            'message' => 'nullable|string',
        ]);

        $data = $request->all();
        
            $data['donor_name'] = $request->donor_name ;
            $data['donor_email'] = $request->donor_email;
            $data['donor_phone'] = $request->donor_phone;
        $data['campaign_id'] = $request->campaign_id;

        // Generate unique reference
        $data['transaction_id'] = 'REF-' . time() . '-' . uniqid();
        $data['status'] = 'pending';
        $data['payment_type'] = $request->input('payment_type', 'campaign_donation');

        // Set polymorphic relationship based on payment type
        if ($data['payment_type'] === 'campaign_donation' && isset($data['campaign_id'])) {
            $data['payable_type'] = Campaign::class;
            $data['payable_id'] = $data['campaign_id'];
        }

        // Créer le don avec statut pending
        $donation = Donation::create($data);

        try {
            // Call appropriate service method based on payment type
            $campaign = $donation->campaign_id ? Campaign::find($donation->campaign_id) : null;
            $result = $this->notchPayService->initializeCampaignDonation($donation, $campaign);
            // update campaign amount
            
            return response()->json([
                'success' => true,
                'message' => 'Payment initialized',
                'data' => $donation,
                'authorization_url' => $result['authorization_url'],
                'reference' => $result['reference']
            ], 201);

        } catch (\Exception $e) {
            $donation->update(['status' => 'failed']);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Notch Pay Webhook
     */
    public function handleWebhook(Request $request)
    {
        $event = $request->input('event');
        $data = $request->input('data');
        
        if (!$data || !isset($data['reference'])) {
            return response()->json(['status' => 'ignored'], 200);
        }

        $processed = $this->notchPayService->processWebhook($event, $data);

        if (!$processed) {
            return response()->json(['status' => 'not_found_or_ignored'], 404);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Callback handler (optional check)
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'No reference provided'], 400);
        }

        $donation = Donation::where('transaction_id', $reference)->first();

        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Donation not found'], 404);
        }

        try {
            $paymentMap = $this->notchPayService->verifyPayment($reference);
            
            if ($paymentMap->status === 'complete' || $paymentMap->status === 'accepted') {
                 $this->notchPayService->completeDonation($donation, [
                    'method' => 'notch_pay' // We could extract more info from $paymentMap if needed
                 ]);
            }
        } catch (\Exception $e) {
            // Ignore error
        }

        return response()->json([
            'success' => true,
            'data' => $donation
        ]);
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