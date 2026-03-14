<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Banner::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }

        $query->latest();
        $banners = $query->paginate($request->input('per_page', 15));

        return response()->json($banners);
    }

    public function show(Banner $banner): JsonResponse
    {
        return response()->json(['banner' => $banner]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image_url' => 'required|string',
            'click_url' => 'nullable|string',
            'advertiser_name' => 'nullable|string|max:255',
            'advertiser_contact' => 'nullable|string|max:255',
            'placement' => 'nullable|array',
            'priority' => 'nullable|integer',
            'status' => 'nullable|string|in:active,paused,draft',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $banner = Banner::create($validated);

        return response()->json(['banner' => $banner], 201);
    }

    public function update(Request $request, Banner $banner): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'image_url' => 'sometimes|string',
            'click_url' => 'nullable|string',
            'advertiser_name' => 'nullable|string|max:255',
            'advertiser_contact' => 'nullable|string|max:255',
            'placement' => 'nullable|array',
            'priority' => 'nullable|integer',
            'status' => 'nullable|string|in:active,paused,draft',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
        ]);

        $banner->update($validated);

        return response()->json(['banner' => $banner->fresh()]);
    }

    public function destroy(Banner $banner): JsonResponse
    {
        $banner->delete();

        return response()->json(['message' => 'Banner o\'chirildi']);
    }
}
