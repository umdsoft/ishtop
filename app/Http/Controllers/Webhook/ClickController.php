<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\ClickService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClickController extends Controller
{
    public function __construct(private ClickService $clickService) {}

    public function prepare(Request $request): JsonResponse
    {
        $result = $this->clickService->handlePrepare($request);
        return response()->json($result);
    }

    public function complete(Request $request): JsonResponse
    {
        $result = $this->clickService->handleComplete($request);
        return response()->json($result);
    }
}
