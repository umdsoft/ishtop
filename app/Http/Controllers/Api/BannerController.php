<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct(private BannerService $bannerService) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'placement' => 'nullable|string',
            'category' => 'nullable|string',
        ]);

        $banners = $this->bannerService->getForPlacement(
            $request->placement ?? 'main',
            $request->user(),
            $request->category,
        );

        return response()->json(['banners' => $banners]);
    }

    public function impression(Request $request, string $banner): JsonResponse
    {
        $bannerModel = Banner::findOrFail($banner);

        $this->bannerService->recordImpression(
            $bannerModel,
            $request->user()?->id,
            $request->input('placement', 'main'),
            $request->ip(),
        );

        return response()->json(['success' => true]);
    }

    public function click(Request $request, string $banner): JsonResponse
    {
        $bannerModel = Banner::findOrFail($banner);

        $recorded = $this->bannerService->recordClick(
            $bannerModel,
            $request->user()?->id,
            $request->input('placement', 'main'),
            $request->ip(),
        );

        return response()->json([
            'success' => $recorded,
            'redirect_url' => $bannerModel->click_url,
        ]);
    }
}
