<?php

use App\Telegram\Conversations\PostVacancyConversation;
use App\Telegram\Conversations\ResumeBuilderConversation;
use App\Telegram\Handlers\AppsHandler;
use App\Telegram\Handlers\HelpHandler;
use App\Telegram\Handlers\MenuHandler;
use App\Telegram\Handlers\NotificationsHandler;
use App\Telegram\Handlers\PostHandler;
use App\Telegram\Handlers\ResumeHandler;
use App\Telegram\Handlers\InlineQueryHandler;
use App\Telegram\Handlers\SavedHandler;
use App\Telegram\Handlers\SearchHandler;
use App\Telegram\Handlers\SettingsHandler;
use App\Telegram\Handlers\StartHandler;
use App\Models\User;
use SergiX44\Nutgram\Nutgram;

/** @var Nutgram $bot */

// ── Global Exception Handler ──
$bot->onException(function (Nutgram $bot, \Throwable $e) {
    $message = $e->getMessage();

    // 403/blocked errors — mark user as blocked and suppress
    if (str_contains($message, 'Forbidden') || str_contains($message, '403')
        || str_contains($message, 'bot was blocked') || str_contains($message, 'chat not found')
        || str_contains($message, 'user is deactivated')) {
        $telegramId = $bot->userId();
        if ($telegramId) {
            User::where('telegram_id', $telegramId)->update(['is_blocked' => true]);
        }
        \Illuminate\Support\Facades\Log::info("Bot blocked/deactivated by user {$telegramId}");
        return;
    }

    \Illuminate\Support\Facades\Log::error('Nutgram exception: ' . $message, [
        'update_type' => $bot->update()?->getType()?->value,
        'user_id' => $bot->userId(),
    ]);
});

$bot->onApiError(function (Nutgram $bot, \SergiX44\Nutgram\Telegram\Exceptions\TelegramException $e) {
    $message = $e->getMessage();
    $telegramId = $bot->userId();

    // 403 Forbidden — user blocked the bot
    if (str_contains($message, 'Forbidden') || str_contains($message, '403')
        || str_contains($message, 'bot was blocked') || str_contains($message, 'chat not found')
        || str_contains($message, 'user is deactivated')) {
        if ($telegramId) {
            User::where('telegram_id', $telegramId)->update(['is_blocked' => true]);
        }
        \Illuminate\Support\Facades\Log::info("Bot blocked/deactivated by user {$telegramId}");
        return;
    }

    \Illuminate\Support\Facades\Log::warning('Telegram API error: ' . $message, [
        'user_id' => $telegramId,
    ]);
});

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
$bot->onCommand('saved', SavedHandler::class);
$bot->onCommand('notifications', NotificationsHandler::class);
$bot->onCommand('settings', SettingsHandler::class);
$bot->onCommand('web', [MenuHandler::class, 'web']);
$bot->onCommand('cancel', function (Nutgram $bot) {
    $user = User::where('telegram_id', $bot->user()->id)->first();
    $lang = $user?->language?->value ?? 'uz';
    $text = $lang === 'ru' ? '❌ Отменено. /menu — Главное меню' : '❌ Bekor qilindi. /menu — Bosh menyu';
    $bot->sendMessage($text);
});

// ── Persistent keyboard text handlers ──
$bot->onText('🔍 Ish qidirish', fn(Nutgram $bot) => (new SearchHandler())($bot));
$bot->onText('🔍 Поиск работы', fn(Nutgram $bot) => (new SearchHandler())($bot));
$bot->onText('📝 Rezume', function (Nutgram $bot) { (new MenuHandler())->resume($bot); });
$bot->onText('📝 Резюме', function (Nutgram $bot) { (new MenuHandler())->resume($bot); });
$bot->onText('📋 Arizalarim', fn(Nutgram $bot) => (new AppsHandler())($bot));
$bot->onText('📋 Мои заявки', fn(Nutgram $bot) => (new AppsHandler())($bot));
$bot->onText('🤍 Saqlanganlar', fn(Nutgram $bot) => (new SavedHandler())($bot));
$bot->onText('🤍 Сохранённые', fn(Nutgram $bot) => (new SavedHandler())($bot));
$bot->onText('📌 Menyu', fn(Nutgram $bot) => (new MenuHandler())($bot));
$bot->onText('📌 Меню', fn(Nutgram $bot) => (new MenuHandler())($bot));

// ── Callback Queries ──

// Language selection (from settings language picker)
$bot->onCallbackQueryData('lang:{lang}', [StartHandler::class, 'setLanguage']);

// Menu callbacks
$bot->onCallbackQueryData('menu:{action}', [MenuHandler::class, 'handleCallback']);

// Search callbacks
$bot->onCallbackQueryData('search:{action}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_cat:{slug}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_subcat:{slug}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_region:{region}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_districts:{region}', [SearchHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('search_district:{data}', [SearchHandler::class, 'handleCallback']);
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
$bot->onCallbackQueryData('resume:linkedin_pdf', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    (new \App\Telegram\Conversations\LinkedInPdfConversation())->begin($bot);
});

// LinkedIn import callbacks
$bot->onCallbackQueryData('linkedin_import:apply_all', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $user = \App\Models\User::where('telegram_id', $bot->user()->id)->first();
    if (!$user) return;

    $cached = cache()->get("linkedin_import_{$user->id}");
    if (!$cached) {
        $lang = $user->language?->value ?? 'uz';
        $bot->editMessageText(
            text: $lang === 'ru' ? '❌ Данные истекли. Загрузите PDF снова.' : '❌ Ma\'lumotlar eskirgan. PDF ni qaytadan yuklang.',
            message_id: $bot->callbackQuery()->message->message_id,
        );
        return;
    }

    $profile = $user->workerProfile;
    if ($profile) {
        $updateData = $cached['mapped'];
        $updateData['linkedin_import_data'] = $cached['raw'];
        $updateData['linkedin_imported_at'] = now();
        $profile->update($updateData);
    }

    cache()->forget("linkedin_import_{$user->id}");

    $lang = $user->language?->value ?? 'uz';
    $bot->editMessageText(
        text: $lang === 'ru'
            ? '✅ LinkedIn данные успешно импортированы в профиль!'
            : '✅ LinkedIn ma\'lumotlari profilga muvaffaqiyatli import qilindi!',
        message_id: $bot->callbackQuery()->message->message_id,
    );
});

$bot->onCallbackQueryData('linkedin_import:cancel', function (Nutgram $bot) {
    $bot->answerCallbackQuery();
    $user = \App\Models\User::where('telegram_id', $bot->user()->id)->first();
    if ($user) {
        cache()->forget("linkedin_import_{$user->id}");
    }
    $lang = $user?->language?->value ?? 'uz';
    $bot->editMessageText(
        text: $lang === 'ru' ? '❌ Импорт отменён.' : '❌ Import bekor qilindi.',
        message_id: $bot->callbackQuery()->message->message_id,
    );
});

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

// Saved vacancies callbacks
$bot->onCallbackQueryData('saved:list', [SavedHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('saved:toggle:{id}', [SavedHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('saved:remove:{id}', [SavedHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('saved:view:{id}', [SavedHandler::class, 'handleCallback']);

// Notifications callbacks
$bot->onCallbackQueryData('notif:list', [NotificationsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('notif:read_all', [NotificationsHandler::class, 'handleCallback']);
$bot->onCallbackQueryData('notif:view:{id}', [NotificationsHandler::class, 'handleCallback']);

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

    // Ro'yxatdan o'tmagan foydalanuvchi — /start ga yo'naltirish
    if (!$user || !$user->is_verified) {
        $text = $lang === 'ru'
            ? "⚠️ Сначала пройдите регистрацию.\n\nНажмите /start для начала."
            : "⚠️ Avval ro'yxatdan o'ting.\n\n/start buyrug'ini bosing.";
        $bot->sendMessage($text);
        return;
    }

    $text = $lang === 'ru'
        ? 'Команда не распознана. Нажмите /menu'
        : 'Buyruq tushunarsiz. /menu bosing.';
    $bot->sendMessage($text);
});
