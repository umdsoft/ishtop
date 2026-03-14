<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = [
            'pricing' => config('kadrgo'),
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
        ];

        return response()->json(['settings' => $settings]);
    }

    public function update(Request $request): JsonResponse
    {
        // Settings update logic - for now returns current settings
        // Can be extended to save settings to database

        Cache::flush();

        return response()->json(['message' => 'Sozlamalar saqlandi']);
    }
}
