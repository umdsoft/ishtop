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
        if ($request->boolean('tree')) {
            return $this->tree($request);
        }

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

    private function tree(Request $request): JsonResponse
    {
        $query = Category::root()
            ->withCount(['children', 'children as active_vacancies_count' => function ($q) {
                // Count vacancies in children via category_id
            }])
            ->with(['children' => function ($q) {
                $q->orderBy('sort_order')
                    ->withCount(['children']);
            }])
            ->orderBy('sort_order');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_uz', 'like', "%{$search}%")
                    ->orWhere('name_ru', 'like', "%{$search}%")
                    ->orWhereHas('children', function ($cq) use ($search) {
                        $cq->where('name_uz', 'like', "%{$search}%")
                            ->orWhere('name_ru', 'like', "%{$search}%");
                    });
            });
        }

        $categories = $query->get();

        // Count vacancies per category (both parent slug match and child category_id)
        $vacancyCounts = \App\Models\Vacancy::query()
            ->selectRaw('category_id, count(*) as cnt')
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('cnt', 'category_id');

        foreach ($categories as $parent) {
            $childVacancies = 0;
            foreach ($parent->children as $child) {
                $count = $vacancyCounts[$child->id] ?? 0;
                $child->vacancies_count = $count;
                $childVacancies += $count;
            }
            $parentOwn = $vacancyCounts[$parent->id] ?? 0;
            $parent->vacancies_count = $parentOwn + $childVacancies;
        }

        return response()->json(['data' => $categories]);
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
