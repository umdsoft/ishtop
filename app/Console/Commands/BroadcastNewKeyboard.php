<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Telegram\Keyboards\PersistentMenuKeyboard;
use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class BroadcastNewKeyboard extends Command
{
    protected $signature = 'bot:broadcast-keyboard';
    protected $description = 'Send new keyboard to all verified users';

    public function handle(Nutgram $bot): int
    {
        $users = User::where('is_verified', true)
            ->where('is_blocked', false)
            ->whereNotNull('telegram_id')
            ->get();

        $this->info("Sending new keyboard to {$users->count()} users...");

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $lang = $user->language?->value ?? 'uz';
            $isRu = $lang === 'ru';

            $text = $isRu
                ? "📱 Обновление! Теперь используйте кнопку *Открыть приложение* для быстрого доступа ко всем функциям."
                : "📱 Yangilanish! Endi barcha funksiyalarga tez kirish uchun *Ilovani ochish* tugmasidan foydalaning.";

            try {
                $bot->sendMessage(
                    text: $text,
                    chat_id: $user->telegram_id,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                    reply_markup: PersistentMenuKeyboard::make($lang, $user->telegram_id),
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                if (str_contains($e->getMessage(), 'Forbidden') || str_contains($e->getMessage(), '403')) {
                    $user->update(['is_blocked' => true]);
                }
            }

            // Rate limiting — Telegram allows ~30 msg/sec
            usleep(40000); // 40ms = ~25 msg/sec
        }

        $this->info("Done! Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }
}
