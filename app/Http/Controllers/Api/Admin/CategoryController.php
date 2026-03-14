<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_uz', 'like', "%{$search}%")
                    ->orWhere('name_ru', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('sort_order')->paginate($request->input('per_page', 50));

        return response()->json($categories);
    }

    public function show(Category $category): JsonResponse
    {
        $category->load('children');

        return response()->json(['category' => $category]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_uz' => 'required|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category = Category::create($validated);

        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name_uz' => 'sometimes|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'slug' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:50',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);

        return response()->json(['category' => $category->fresh()]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json(['message' => 'Kategoriya o\'chirildi']);
    }
}
