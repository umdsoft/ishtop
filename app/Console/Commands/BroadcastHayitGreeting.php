<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;

class BroadcastHayitGreeting extends Command
{
    protected $signature = 'bot:broadcast-hayit {--test : Send only to first user for testing}';
    protected $description = 'Send Hayit (Eid) holiday greeting to all verified users';

    public function handle(Nutgram $bot): int
    {
        $query = User::where('is_verified', true)
            ->where('is_blocked', false)
            ->whereNotNull('telegram_id');

        if ($this->option('test')) {
            $query->limit(1);
        }

        $users = $query->get();

        $this->info("Sending Hayit greeting to {$users->count()} users...");

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $lang = $user->language?->value ?? 'uz';
            $text = $this->greetingText($lang, $user->first_name);

            try {
                $bot->sendMessage(
                    text: $text,
                    chat_id: $user->telegram_id,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                if (str_contains($e->getMessage(), 'Forbidden') || str_contains($e->getMessage(), '403')) {
                    $user->update(['is_blocked' => true]);
                }
                $this->warn("Failed for user #{$user->id}: {$e->getMessage()}");
            }

            usleep(40000); // 40ms = ~25 msg/sec
        }

        $this->info("Done! Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function greetingText(string $lang, ?string $name): string
    {
        $greeting = $name ? $name : '';

        if ($lang === 'ru') {
            return "🌙✨ *С праздником Рамадан Хайит!*

Уважаемый{$this->ruSuffix($greeting)},

Команда *KadrGo* поздравляет вас с праздником Рамадан Хайит!

Желаем вам и вашей семье мира, здоровья, благополучия и успехов в карьере! 🤲

Пусть этот светлый праздник принесёт радость и новые возможности!

С уважением,
Команда KadrGo 💼";
        }

        return "🌙✨ *Ramazon hayiti muborak bo'lsin!*

Hurmatli{$this->uzSuffix($greeting)},

*KadrGo* jamoasi sizni muborak Ramazon hayiti bilan chin dildan tabriklaymiz!

Sizga va oilangizga tinchlik, sog'lik, baxt-saodat va kelajakda yangi yutuqlar tilaymiz! 🤲

Hayit ayyomingiz muborak bo'lsin!

Hurmat bilan,
KadrGo jamoasi 💼";
    }

    private function uzSuffix(?string $name): string
    {
        return $name ? " {$name}" : '';
    }

    private function ruSuffix(?string $name): string
    {
        return $name ? " {$name}" : '';
    }
}
