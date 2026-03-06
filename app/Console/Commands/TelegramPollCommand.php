<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\RunningMode\Polling;

class TelegramPollCommand extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Start Telegram bot in polling mode (development)';

    public function handle(Nutgram $bot): int
    {
        $this->info('Starting Telegram bot polling...');
        $this->info('Press Ctrl+C to stop.');

        $bot->setRunningMode(Polling::class);
        $bot->run();

        return self::SUCCESS;
    }
}
