<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Webhook;

class TelegramWebhookController extends Controller
{
    public function handle(?string $secret = null): JsonResponse
    {
        $expected = config('nutgram.webhook_secret');
        if ($expected && $secret !== $expected) {
            abort(404);
        }

        try {
            $bot = app(Nutgram::class);
            $bot->setRunningMode(Webhook::class);
            $bot->run();
        } catch (\Throwable $e) {
            Log::error('Telegram webhook: ' . $e->getMessage());
        }

        return response()->json(['ok' => true]);
    }
}
