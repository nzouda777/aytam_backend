<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('campaigns');

        if ($request->has('active')) {
            $query->active();
        }

        $categories = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'color' => 'sometimes|string|max:7',
            'is_active' => 'sometimes|boolean',
        ]);

        $category = Category::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie créée avec succès',
            'data' => $category,
        ], 201);
    }

    public function show($id)
    {
        $category = Category::withCount('campaigns')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255|unique:categories,name,' . $id,
            'description' => 'nullable|string',
            'color' => 'sometimes|string|max:7',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Catégorie mise à jour avec succès',
            'data' => $category,
        ]);
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Vérifier s'il y a des campagnes associées
        if ($category->campaigns()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de supprimer une catégorie avec des campagnes associées',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès',
        ]);
    }

    public function campaigns($id)
    {
        $category = Category::findOrFail($id);
        $campaigns = $category->campaigns()
            ->with('donations')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }
}