<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sponsorship;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
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
            'user_id' => 'required|exists:users,id',
            'family_id' => 'required|exists:families,id',
            'orphan_id' => 'required|exists:orphans,id',
            'monthly_amount' => 'required|numeric|min:1',
            'name' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'payment_frequency' => 'required|in:monthly,quarterly,yearly',
            'notes' => 'nullable|string',
        ]);

        $sponsorship = Sponsorship::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Parrainage créé avec succès',
            'data' => $sponsorship->load(['user', 'family']),
        ], 201);
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
}