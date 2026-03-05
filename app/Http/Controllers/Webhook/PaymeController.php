<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PaymeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymeController extends Controller
{
    public function __construct(private PaymeService $paymeService) {}

    public function handle(Request $request): JsonResponse
    {
        $auth = $request->header('Authorization', '');
        $expectedAuth = 'Basic ' . base64_encode(config('services.payme.merchant_id') . ':' . config('services.payme.secret_key'));

        if ($auth !== $expectedAuth) {
            return response()->json([
                'error' => ['code' => -32504, 'message' => ['uz' => 'Avtorizatsiya xatosi']],
            ], 200);
        }

        $result = $this->paymeService->handleWebhook($request);

        return response()->json($result);
    }
}
