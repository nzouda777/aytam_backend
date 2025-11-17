<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orphan;
use App\Models\Sponsorship;
use App\Models\Disbursements;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;


class FamilyController extends Controller
{
    public function index(Request $request)
    {
        $query = Family::with(['orphans', 'sponsorships']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->has('region')) {
            $query->where('region', 'like', "%{$request->region}%");
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('widow_name', 'like', "%{$search}%")
                  ->orWhere('family_code', 'like', "%{$search}%")
                  ->orWhere('widow_email', 'like', "%{$search}%");
            });
        }

        $families = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $families,
        ]);
    }

    public function store(Request $request)
    {
        
        $request->validate([
            'widow_name' => 'required|string|max:255',
            'widow_phone' => 'nullable|string|max:20',
            'widow_email' => 'nullable|email|max:255',
            'widow_date_of_birth' => 'nullable|date',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'region' => 'nullable|string|max:100',
            'orphans_count' => 'nullable|integer|min:0',
            'status' => 'nullable|in:active,inactive,pending',
            'registration_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data = $request->all();
        
        $data['registration_date'] = $request->registration_date ?? now();

        $family = Family::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Famille créée avec succès',
            'data' => $family->load('orphans'),
        ], 201);
    }

    public function show($id)
    {
        $family = Family::with(['orphans', 'sponsorships.user', 'disbursements'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $family,
        ]);
    }

    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);

        $request->validate([
            'widow_name' => 'sometimes|string|max:255',
            'widow_phone' => 'sometimes|string|max:20',
            'widow_email' => 'sometimes|email|max:255',
            'widow_date_of_birth' => 'sometimes|date',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string|max:100',
            'region' => 'sometimes|string|max:100',
            'orphans_count' => 'sometimes|integer|min:0',
            'status' => 'sometimes|in:active,inactive,pending',
            'notes' => 'nullable|string',
        ]);

        $family->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Famille mise à jour avec succès',
            'data' => $family->load('orphans'),
        ]);
    }

    public function destroy($id)
    {
        $family = Family::findOrFail($id);
        $family->delete();

        return response()->json([
            'success' => true,
            'message' => 'Famille supprimée avec succès',
        ]);
    }

    public function orphans($id)
    {
        $family = Family::findOrFail($id);
        $orphans = $family->orphans;

        return response()->json([
            'success' => true,
            'data' => $orphans,
        ]);
    }

    public function statistics(Request $request)
    {
        $stats = [
            'total_families' => Family::count(),
            'active_families' => Family::active()->count(),
            'pending_families' => Family::pending()->count(),
            'total_orphans' => Family::sum('orphans_count'),
            'total_received' => Family::sum('total_received'),
            'families_with_sponsorships' => Family::whereHas('sponsorships', function($q) {
                $q->where('status', 'active');
            })->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:active,inactive,pending',
        ]);

        $family = Family::findOrFail($id);
        $family->status = $request->status;
        $family->save();

        return response()->json([
            'success' => true,
            'message' => 'Statut de la famille mis à jour avec succès',
            'data' => $family,
        ]);
    }
}