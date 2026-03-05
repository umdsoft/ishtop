<?php

use App\Telegram\Conversations\PostVacancyConversation;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Conversations\ResumeBuilderConversation;
use App\Telegram\Handlers\HelpHandler;
use App\Telegram\Handlers\MenuHandler;
use App\Telegram\Handlers\SearchHandler;
use App\Telegram\Handlers\StartHandler;
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
$bot->onCommand('apps', [MenuHandler::class, 'apps']);
$bot->onCommand('settings', [MenuHandler::class, 'settings']);
$bot->onCommand('web', [MenuHandler::class, 'web']);
$bot->onCommand('cancel', function (Nutgram $bot) {
    $bot->sendMessage('❌ Bekor qilindi. /menu — Bosh menyu');
});

// ── Callback Queries ──

// Language selection
$bot->onCallbackQueryData('lang:{lang}', [StartHandler::class, 'setLanguage']);

// Menu callbacks
$bot->onCallbackQueryData('menu:{action}', [MenuHandler::class, 'handleCallback']);

// Search callbacks
$bot->onCallbackQueryData('search:{action}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_cat:{slug}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_city:{city}', [SearchHandler::class, 'handleCallback']);

$bot->onCallbackQueryData('search_page:{data}', [SearchHandler::class, 'handleCallback']);

// Vacancy callbacks

$bot->onCallbackQueryData('vacancy_view:{id}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('vacancy_apply:{id}', [SearchHandler::class, 'handleCallback']);

// Resume callbacks
$bot->onCallbackQueryData('resume:create', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    (new ResumeBuilderConversation())->begin($bot);
});
$bot->onCallbackQueryData('resume:view', [MenuHandler::class, 'viewResume']);

// Vacancy create callback
$bot->onCallbackQueryData('vacancy:create', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    (new PostVacancyConversation())->begin($bot);
});

// Fallback
$bot->fallback(function (Nutgram $bot) {
    $bot->sendMessage("Buyruq tushunarsiz. /menu bosing.");
});
