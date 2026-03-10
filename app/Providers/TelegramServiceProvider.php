<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use SergiX44\Nutgram\Configuration;
use SergiX44\Nutgram\Nutgram;

class TelegramServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Nutgram::class, function () {
            $config = config('nutgram');

            $bot = new Nutgram($config['token'], new Configuration(
                pollingTimeout: $config['polling']['timeout'] ?? 10,
                pollingLimit: $config['polling']['limit'] ?? 100,
                pollingAllowedUpdates: $config['polling']['allowed_updates'] ?? [
                    'message',
                    'edited_message',
                    'callback_query',
                    'inline_query',
                    'chosen_inline_result',
                    'my_chat_member',
                ],
                cache: Cache::store(config('nutgram.cache', 'file')),
            ));

            return $bot;
        });
    }

    public function boot(): void
    {
        $bot = $this->app->make(Nutgram::class);

        $routesFile = base_path('routes/telegram.php');
        if (file_exists($routesFile)) {
            require $routesFile;
        }
    }
}
