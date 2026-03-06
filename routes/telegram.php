<?php

use App\Telegram\Conversations\PostVacancyConversation;
use App\Telegram\Conversations\ResumeBuilderConversation;
use App\Telegram\Handlers\AppsHandler;
use App\Telegram\Handlers\HelpHandler;
use App\Telegram\Handlers\MenuHandler;
use App\Telegram\Handlers\PostHandler;
use App\Telegram\Handlers\ResumeHandler;
use App\Telegram\Handlers\InlineQueryHandler;
use App\Telegram\Handlers\SearchHandler;
use App\Telegram\Handlers\SettingsHandler;
use App\Telegram\Handlers\StartHandler;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;

/** @var Nutgram $bot */

// ── Commands ──
$bot->onCommand('start', StartHandler::class);
$bot->onCommand('menu', MenuHandler::class);
$bot->onCommand('help', HelpHandler::class);
$bot->onCommand('search', SearchHandler::class);
$bot->onCommand('resume', function (Nutgram $bot) {
    (new ResumeBuilderConversation())->begin($bot);
});
$bot->onCommand('post', function (Nutgram $bot) {
    (new PostVacancyConversation())->begin($bot);
});
$bot->onCommand('apps', AppsHandler::class);
$bot->onCommand('settings', SettingsHandler::class);
$bot->onCommand('web', [MenuHandler::class, 'web']);
$bot->onCommand('cancel', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->user()->id)->first();
    $lang = $user?->language?->value ?? 'uz';
    $text = $lang === 'ru' ? '❌ Отменено. /menu — Главное меню' : '❌ Bekor qilindi. /menu — Bosh menyu';
    $bot->sendMessage($text);
});

// ── Callback Queries ──

// Language selection (from settings language picker)
$bot->onCallbackQueryData('lang:{lang}', [StartHandler::class, 'setLanguage']);

// Menu callbacks
$bot->onCallbackQueryData('menu:{action}', [MenuHandler::class, 'handleCallback']);

// Search callbacks
$bot->onCallbackQueryData('search:{action}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_cat:{slug}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_city:{city}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_page:{data}', [SearchHandler::class, 'handleCallback']);

// Vacancy callbacks (from search)
$bot->onCallbackQueryData('vacancy_view:{id}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('vacancy_apply:{id}', [SearchHandler::class, 'handleCallback']);

// Resume callbacks
$bot->onCallbackQueryData('resume:create', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    (new ResumeBuilderConversation())->begin($bot);
});
$bot->onCallbackQueryData('resume:view', [MenuHandler::class, 'viewResume']);
$bot->onCallbackQueryData('resume:edit', [ResumeHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('resume:toggle_search', [ResumeHandler::class, 'handleCallback']);

// Post/Vacancy management callbacks
$bot->onCallbackQueryData('post:create', [PostHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('post:view:{id}', [PostHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('post:pause:{id}', [PostHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('post:activate:{id}', [PostHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('post:back', [PostHandler::class, 'handleCallback']);

// Vacancy create callback
$bot->onCallbackQueryData('vacancy:create', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    (new PostVacancyConversation())->begin($bot);
});

// Settings callbacks
$bot->onCallbackQueryData('settings:{action}', [SettingsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('settings:lang:{lang}', [SettingsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('settings:delete:confirm', [SettingsHandler::class, 'handleCallback']);

// Top-up callbacks
$bot->onCallbackQueryData('topup:{amount}', [SettingsHandler::class, 'handleTopup']);

// Applications callbacks
$bot->onCallbackQueryData('app:view:{id}', [AppsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('app:quest:{id}', [AppsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('app:withdraw:{id}', [AppsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('app:back', [AppsHandler::class, 'handleCallback']);

// Inline query
$bot->onInlineQuery(InlineQueryHandler::class);

// Noop callback (pagination indicators, etc.)
$bot->onCallbackQueryData('noop', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
});

// Fallback
$bot->fallback(function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->user()->id)->first();
    $lang = $user?->language?->value ?? 'uz';
    $text = $lang === 'ru'
        ? 'Команда не распознана. Нажмите /menu'
        : 'Buyruq tushunarsiz. /menu bosing.';
    $bot->sendMessage($text);
});
