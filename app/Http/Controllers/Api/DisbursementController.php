<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disbursement;
use Illuminate\Http\Request;

class DisbursementController extends Controller
{
    public function index(Request $request)
    {
        $query = Disbursement::with(['campaign', 'family', 'processedBy']);

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->has('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        if ($request->has('processed_by')) {
            $query->where('processed_by', $request->processed_by);
        }

        // Filtres de date
        if ($request->has('date_from')) {
            $query->whereDate('disbursement_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('disbursement_date', '<=', $request->date_to);
        }

        $disbursements = $query->orderBy('disbursement_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $disbursements,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'campaign_id' => 'nullable|exists:campaigns,id',
            'family_id' => 'required|exists:families,id',
            'amount' => 'required|numeric|min:1',
            'disbursement_date' => 'required|date',
            'type' => 'required|in:cash,goods,services',
            'description' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['processed_by'] = $request->user()->id;

        $disbursement = Disbursement::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Décaissement créé avec succès',
            'data' => $disbursement->load(['campaign', 'family', 'processedBy']),
        ], 201);
    }

    public function show($id)
    {
        $disbursement = Disbursement::with(['campaign', 'family', 'processedBy'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $disbursement,
        ]);
    }

    public function update(Request $request, $id)
    {
        $disbursement = Disbursement::findOrFail($id);

        $request->validate([
            'amount' => 'sometimes|numeric|min:1',
            'disbursement_date' => 'sometimes|date',
            'type' => 'sometimes|in:cash,goods,services',
            'description' => 'sometimes|string',
            'notes' => 'nullable|string',
        ]);

        $disbursement->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Décaissement mis à jour avec succès',
            'data' => $disbursement->load(['campaign', 'family', 'processedBy']),
        ]);
    }

    public function destroy($id)
    {
        $disbursement = Disbursement::findOrFail($id);
        $disbursement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Décaissement supprimé avec succès',
        ]);
    }

    public function familyDisbursements($familyId)
    {
        $disbursements = Disbursement::with(['campaign', 'processedBy'])
            ->where('family_id', $familyId)
            ->orderBy('disbursement_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $disbursements,
        ]);
    }

    public function campaignDisbursements($campaignId)
    {
        $disbursements = Disbursement::with(['family', 'processedBy'])
            ->where('campaign_id', $campaignId)
            ->orderBy('disbursement_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $disbursements,
        ]);
    }

    public function thisMonth()
    {
        $disbursements = Disbursement::with(['campaign', 'family', 'processedBy'])
            ->thisMonth()
            ->orderBy('disbursement_date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $disbursements,
        ]);
    }

    public function thisYear()
    {
        $disbursements = Disbursement::with(['campaign', 'family', 'processedBy'])
            ->thisYear()
            ->orderBy('disbursement_date', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $disbursements,
        ]);
    }

    public function statistics(Request $request)
    {
        $stats = [
            'total_disbursements' => Disbursement::sum('amount'),
            'total_count' => Disbursement::count(),
            'month_disbursements' => Disbursement::thisMonth()->sum('amount'),
            'year_disbursements' => Disbursement::thisYear()->sum('amount'),
            'cash_disbursements' => Disbursement::cash()->sum('amount'),
            'goods_disbursements' => Disbursement::goods()->count(),
            'services_disbursements' => Disbursement::services()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}