<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use App\Models\Donation;
use App\Models\Family;
use App\Models\Orphan;
use App\Services\NotchPayService;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    protected $notchPayService;

    public function __construct(NotchPayService $notchPayService)
    {
        $this->notchPayService = $notchPayService;
    }

    public function index(Request $request)
    {
        $query = Sponsorship::with(['user', 'family.orphans']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        if ($request->has('payment_frequency')) {
            $query->where('payment_frequency', $request->payment_frequency);
        }

        $sponsorships = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $sponsorships,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'family_id' => 'required_without:orphan_id|exists:families,id',
            'orphan_id' => 'required_without:family_id|exists:orphans,id',
            'monthly_amount' => 'required|numeric|min:100',
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'payment_frequency' => 'required|in:monthly,quarterly,yearly',
            'notes' => 'nullable|string',
            'donor_name' => 'nullable|string|max:255',
            'donor_email' => 'nullable|email|max:255',
            'donor_phone' => 'nullable|string|max:20',
        ]);

        $user = $request->user();

        // Create pending sponsorship
        $sponsorshipData = [
            'user_id' => $user->id,
            'family_id' => $request->family_id,
            'orphan_id' => $request->orphan_id,
            'monthly_amount' => $request->monthly_amount,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'payment_frequency' => $request->payment_frequency,
            'notes' => $request->notes,
            'status' => 'pending', // Will be activated after payment
        ];

        $sponsorship = Sponsorship::create($sponsorshipData);

        // Create donation record for the payment
        $donationData = [
            'user_id' => $user->id,
            'amount' => $request->monthly_amount,
            'donor_name' => $request->donor_name ?? $user->name,
            'donor_email' => $request->donor_email ?? $user->email,
            'donor_phone' => $request->donor_phone,
            'payment_method' => 'mobile_money',
            'status' => 'pending',
            'transaction_id' => 'REF-' . time() . '-' . uniqid(),
            'payment_type' => $request->family_id ? 'family_sponsorship' : 'orphan_sponsorship',
            'payable_type' => $request->family_id ? Family::class : Orphan::class,
            'payable_id' => $request->family_id ?? $request->orphan_id,
        ];

        $donation = Donation::create($donationData);

        try {
            // Initialize payment based on sponsorship type
            if ($request->family_id) {
                $family = Family::find($request->family_id);
                $result = $this->notchPayService->initializeFamilySponsorship($donation, $family, $sponsorship);
            } else {
                $orphan = Orphan::find($request->orphan_id);
                $result = $this->notchPayService->initializeOrphanSponsorship($donation, $orphan, $sponsorship);
            }

            return response()->json([
                'success' => true,
                'message' => 'Sponsorship created. Please complete payment to activate.',
                'data' => $sponsorship->load(['user', 'family', 'orphan']),
                'donation' => $donation,
                'authorization_url' => $result['authorization_url'],
                'reference' => $result['reference']
            ], 201);

        } catch (\Exception $e) {
            // Clean up on failure
            $sponsorship->delete();
            $donation->update(['status' => 'failed']);
            
            return response()->json([
                'success' => false,
                'message' => 'Payment initialization error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $sponsorship = Sponsorship::with(['user', 'family.orphans'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $sponsorship,
        ]);
    }

    public function update(Request $request, $id)
    {
        $sponsorship = Sponsorship::findOrFail($id);

        $request->validate([
            'monthly_amount' => 'sometimes|numeric|min:1',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'sometimes|in:active,paused,completed,cancelled',
            'payment_frequency' => 'sometimes|in:monthly,quarterly,yearly',
            'name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $sponsorship->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Parrainage mis à jour avec succès',
            'data' => $sponsorship->load(['user', 'family']),
        ]);
    }

    public function destroy($id)
    {
        $sponsorship = Sponsorship::findOrFail($id);
        $sponsorship->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parrainage supprimé avec succès',
        ]);
    }

    public function active()
    {
        $sponsorships = Sponsorship::with(['user', 'family'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sponsorships,
        ]);
    }

    public function userSponsorships(Request $request)
    {
        $sponsorships = Sponsorship::with('family.orphans')
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $sponsorships,
        ]);
    }

    public function familySponsorships($familyId)
    {
        $sponsorships = Sponsorship::with('user')
            ->where('family_id', $familyId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sponsorships,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,paused,completed,cancelled',
        ]);

        $sponsorship = Sponsorship::findOrFail($id);
        $sponsorship->status = $request->status;

        // Si le statut est completed ou cancelled, définir la date de fin
        if (in_array($request->status, ['completed', 'cancelled']) && !$sponsorship->end_date) {
            $sponsorship->end_date = now();
        }

        $sponsorship->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut du parrainage mis à jour avec succès',
            'data' => $sponsorship->load(['user', 'family']),
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_sponsorships' => Sponsorship::count(),
            'active_sponsorships' => Sponsorship::active()->count(),
            'paused_sponsorships' => Sponsorship::paused()->count(),
            'completed_sponsorships' => Sponsorship::completed()->count(),
            'total_monthly_income' => Sponsorship::active()
                ->where('payment_frequency', 'monthly')
                ->sum('monthly_amount'),
            'average_monthly_amount' => Sponsorship::active()->avg('monthly_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Callback handler for sponsorship payments
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');
        
        if (!$reference) {
            return response()->json(['success' => false, 'message' => 'No reference provided'], 400);
        }

        $donation = Donation::where('transaction_id', $reference)->first();

        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
        }

        // Verify payment status
        try {
            $paymentMap = $this->notchPayService->verifyPayment($reference);
            
            if ($paymentMap->status === 'complete' && $donation->status !== 'completed') {
                // This will be handled by webhook, but we can double-check here
                $donation->update([
                    'status' => 'completed',
                    'payment_date' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Ignore error, rely on webhook
        }

        return response()->json([
            'success' => true,
            'data' => $donation,
            'sponsorship_status' => $donation->status === 'completed' ? 'active' : 'pending'
        ]);
    }
}