<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = City::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_uz', 'like', "%{$search}%")
                    ->orWhere('name_ru', 'like', "%{$search}%");
            });
        }

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }

        $cities = $query->orderBy('name_uz')->paginate($request->input('per_page', 50));

        return response()->json($cities);
    }

    public function show(City $city): JsonResponse
    {
        return response()->json(['city' => $city]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name_uz' => 'required|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $city = City::create($validated);

        return response()->json(['city' => $city], 201);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $validated = $request->validate([
            'name_uz' => 'sometimes|string|max:100',
            'name_ru' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'type' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $city->update($validated);

        return response()->json(['city' => $city->fresh()]);
    }

    public function destroy(City $city): JsonResponse
    {
        $city->delete();

        return response()->json(['message' => 'Shahar o\'chirildi']);
    }
}
