<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private User $user,
        private string $type,
        private string $title,
        private string $message,
        private array $data = [],
        private bool $sendTelegram = true,
    ) {}

    public function handle(): void
    {
        // Save to DB
        Notification::create([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
        ]);

        // Send via Telegram
        if ($this->sendTelegram && $this->user->telegram_id) {
            $this->sendTelegramMessage();
        }
    }

    private function sendTelegramMessage(): void
    {
        $text = "📌 *{$this->title}*\n\n{$this->message}";

        try {
            $response = Http::post('https://api.telegram.org/bot' . config('nutgram.token') . '/sendMessage', [
                'chat_id' => $this->user->telegram_id,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ]);

            // User blocked the bot — mark as blocked
            if ($response->status() === 403 || ($response->json('error_code') === 403)) {
                $this->user->update(['is_blocked' => true]);
            }
        } catch (\Throwable $e) {
            \Log::warning('SendNotificationJob telegram failed', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
