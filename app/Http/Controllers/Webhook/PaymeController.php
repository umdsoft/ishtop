<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\PaymeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymeController extends Controller
{
    public function __construct(private PaymeService $paymeService) {}

    public function handle(Request $request): JsonResponse
    {
        // Payme IP va Auth tekshirish
        if (!$this->paymeService->authenticate($request)) {
            return response()->json([
                'error' => [
                    'code' => -32504,
                    'message' => [
                        'uz' => 'Avtorizatsiya xatosi',
                        'ru' => 'Ошибка авторизации',
                        'en' => 'Authorization error',
                    ],
                ],
                'id' => $request->input('id'),
            ], 200); // Payme doimo 200 kutadi
        }

        Log::channel('daily')->info('Payme webhook', [
            'method' => $request->input('method'),
            'params' => $request->input('params'),
        ]);

        $result = $this->paymeService->handleWebhook($request);

        return response()->json($result, 200);
    }
}
