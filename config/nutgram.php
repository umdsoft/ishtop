<?php

return [
    'token' => env('TELEGRAM_BOT_TOKEN', ''),
    'is_local' => env('NUTGRAM_LOCAL', false),
    'local_path' => env('NUTGRAM_LOCAL_PATH', ''),
    'log_channel' => env('NUTGRAM_LOG_CHANNEL', 'stack'),
    'cache' => 'redis',
    'polling' => [
        'timeout' => 10,
        'limit' => 100,
    ],
];
