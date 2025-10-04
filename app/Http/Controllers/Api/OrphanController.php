<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Orphan;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrphanController extends Controller
{
    public function index(Request $request)
    {
        $query = Orphan::with('family');

        // Filtres
        if ($request->has('family_id')) {
            $query->where('family_id', $request->family_id);
        }

        if ($request->has('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->has('is_sponsored')) {
            $query->where('is_sponsored', $request->boolean('is_sponsored'));
        }

        // Recherche
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $orphans = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $orphans,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'family_id' => 'required|exists:families,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'school_name' => 'nullable|string|max:255',
            'school_level' => 'nullable|string|max:100',
            'health_status' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_sponsored' => 'sometimes|boolean',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('orphans', 'public');
        }

        $orphan = Orphan::create($data);

        // Mettre à jour le compteur d'orphelins de la famille
        $family = Family::find($request->family_id);
        $family->orphans_count = $family->orphans()->count();
        $family->save();

        return response()->json([
            'success' => true,
            'message' => 'Orphelin créé avec succès',
            'data' => $orphan->load('family'),
        ], 201);
    }

    public function show($id)
    {
        $orphan = Orphan::with('family')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $orphan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $orphan = Orphan::findOrFail($id);

        $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'date_of_birth' => 'sometimes|date',
            'gender' => 'sometimes|in:male,female',
            'school_name' => 'nullable|string|max:255',
            'school_level' => 'nullable|string|max:100',
            'health_status' => 'nullable|string',
            'special_needs' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'is_sponsored' => 'sometimes|boolean',
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            // Supprimer l'ancienne photo
            if ($orphan->photo) {
                Storage::disk('public')->delete($orphan->photo);
            }
            $data['photo'] = $request->file('photo')->store('orphans', 'public');
        }

        $orphan->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Orphelin mis à jour avec succès',
            'data' => $orphan->load('family'),
        ]);
    }

    public function destroy($id)
    {
        $orphan = Orphan::findOrFail($id);
        $familyId = $orphan->family_id;

        // Supprimer la photo si elle existe
        if ($orphan->photo) {
            Storage::disk('public')->delete($orphan->photo);
        }

        $orphan->delete();

        // Mettre à jour le compteur d'orphelins de la famille
        $family = Family::find($familyId);
        $family->orphans_count = $family->orphans()->count();
        $family->save();

        return response()->json([
            'success' => true,
            'message' => 'Orphelin supprimé avec succès',
        ]);
    }

    public function sponsored()
    {
        $orphans = Orphan::with('family')
            ->sponsored()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orphans,
        ]);
    }

    public function unsponsored()
    {
        $orphans = Orphan::with('family')
            ->unsponsored()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $orphans,
        ]);
    }

    public function statistics()
    {
        $stats = [
            'total_orphans' => Orphan::count(),
            'sponsored_orphans' => Orphan::sponsored()->count(),
            'unsponsored_orphans' => Orphan::unsponsored()->count(),
            'male_orphans' => Orphan::male()->count(),
            'female_orphans' => Orphan::female()->count(),
            'school_age_orphans' => Orphan::all()->filter->is_school_age->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}