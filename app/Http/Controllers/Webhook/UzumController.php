<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\UzumService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UzumController extends Controller
{
    public function __construct(private UzumService $uzumService) {}

    public function handle(Request $request): JsonResponse
    {
        if (!$this->uzumService->verifySignature($request)) {
            return response()->json([
                'error' => ['code' => -32504, 'message' => 'Unauthorized'],
            ], 200);
        }

        $result = $this->uzumService->handleWebhook($request);

        return response()->json($result);
    }
}
