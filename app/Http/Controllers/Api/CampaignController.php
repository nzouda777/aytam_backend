<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $query = Campaign::with(['category', 'donations']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $campaigns = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'goal_amount' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'urgency' => 'sometimes|in:normal,urgent',
            'image' => 'nullable|image|max:2048',
            'beneficiaries_count' => 'sometimes|integer|min:0',
            'is_featured' => 'sometimes|boolean',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $campaign = Campaign::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Campagne créée avec succès',
            'data' => $campaign->load('category'),
        ], 201);
    }

    public function show($id)
    {
        $campaign = Campaign::with(['category', 'donations.user', 'disbursements'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $campaign,
        ]);
    }

    public function update(Request $request, $id)
    {
        $campaign = Campaign::findOrFail($id);

        $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'goal_amount' => 'sometimes|numeric|min:0',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:draft,active,completed,cancelled',
            'urgency' => 'sometimes|in:normal,urgent',
            'image' => 'nullable|image|max:2048',
            'beneficiaries_count' => 'sometimes|integer|min:0',
            'is_featured' => 'sometimes|boolean',
        ]);

        $data = $request->except(['image']);

        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image
            if ($campaign->image) {
                Storage::disk('public')->delete($campaign->image);
            }
            $data['image'] = $request->file('image')->store('campaigns', 'public');
        }

        $campaign->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Campagne mise à jour avec succès',
            'data' => $campaign->load('category'),
        ]);
    }

    public function destroy($id)
    {
        $campaign = Campaign::findOrFail($id);

        // Supprimer l'image si elle existe
        if ($campaign->image) {
            Storage::disk('public')->delete($campaign->image);
        }

        $campaign->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campagne supprimée avec succès',
        ]);
    }

    public function featured()
    {
        $campaigns = Campaign::with('category')
            ->featured()
            ->active()
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function active()
    {
        $campaigns = Campaign::with('category')
            ->active()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    public function urgent()
    {
        $campaigns = Campaign::with('category')
            ->active()
            ->urgent()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }
}