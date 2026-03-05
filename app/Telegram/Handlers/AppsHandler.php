<?php

namespace App\Telegram\Handlers;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\User;
use App\Telegram\Conversations\QuestionnaireConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * AppsHandler
 * Handles /apps command - view user's applications
 */
class AppsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $workerProfile = $user->workerProfile;

        if (!$workerProfile) {
            $this->showNoProfileMessage($bot, $lang);
            return;
        }

        // Get applications
        $applications = Application::with('vacancy.employer')
            ->where('worker_id', $workerProfile->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        if ($applications->isEmpty()) {
            $this->showNoApplications($bot, $lang);
            return;
        }

        $this->showApplicationsList($bot, $applications, $lang);
    }

    protected function showNoProfileMessage(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📝 *Мои заявки*\n\nЧтобы подавать заявки, сначала создайте резюме.\n\n/resume — Создать резюме"
            : "📝 *Mening arizalarim*\n\nAriza yuborish uchun avval rezume yarating.\n\n/resume — Rezume yaratish";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )
                ),
        );
    }

    protected function showNoApplications(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📝 *Мои заявки*\n\nУ вас пока нет заявок.\nНачните искать работу!\n\n/search — Поиск вакансий"
            : "📝 *Mening arizalarim*\n\nSizda hali ariza yo'q.\nIsh qidirishni boshlang!\n\n/search — Vakansiya qidirish";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '🔍 Искать работу' : '🔍 Ish qidirish',
                        callback_data: 'search:start'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )
                ),
        );

        $bot->onCallbackQueryData('search:start', function (Nutgram $bot) {
            $bot->answerCallbackQuery();
            $bot->sendMessage(text: '/search');
        });
    }

    protected function showApplicationsList(Nutgram $bot, $applications, string $lang): void
    {
        $header = $lang === 'ru'
            ? "📝 *Мои заявки* (" . $applications->count() . ")\n\n"
            : "📝 *Mening arizalarim* (" . $applications->count() . ")\n\n";

        $text = $header;

        // Group by status
        $grouped = $applications->groupBy('status');

        foreach ($grouped as $status => $apps) {
            $statusIcon = match ($status) {
                ApplicationStatus::NEW => '🆕',
                ApplicationStatus::UNDER_REVIEW => '👀',
                ApplicationStatus::SHORTLISTED => '⭐',
                ApplicationStatus::INTERVIEW => '🎯',
                ApplicationStatus::REJECTED => '❌',
                ApplicationStatus::ACCEPTED => '✅',
                ApplicationStatus::WITHDRAWN => '🚫',
                default => '⚪',
            };

            $statusLabel = match ($status) {
                ApplicationStatus::NEW => $lang === 'ru' ? 'Новые' : 'Yangi',
                ApplicationStatus::UNDER_REVIEW => $lang === 'ru' ? 'На рассмотрении' : 'Ko\'rib chiqilmoqda',
                ApplicationStatus::SHORTLISTED => $lang === 'ru' ? 'В избранном' : 'Tanlab olingan',
                ApplicationStatus::INTERVIEW => $lang === 'ru' ? 'Интервью' : 'Intervyu',
                ApplicationStatus::REJECTED => $lang === 'ru' ? 'Отклонены' : 'Rad etilgan',
                ApplicationStatus::ACCEPTED => $lang === 'ru' ? 'Приняты' : 'Qabul qilingan',
                ApplicationStatus::WITHDRAWN => $lang === 'ru' ? 'Отозваны' : 'Bekor qilingan',
                default => $status->value,
            };

            $text .= "{$statusIcon} *{$statusLabel}* ({$apps->count()})\n\n";
        }

        $text .= $lang === 'ru'
            ? "_Выберите заявку для подробностей:_"
            : "_Tafsilotlar uchun arizani tanlang:_";

        $keyboard = InlineKeyboardMarkup::make();

        // Add top 8 applications as buttons
        foreach ($applications->take(8) as $app) {
            $statusIcon = match ($app->status) {
                ApplicationStatus::NEW => '🆕',
                ApplicationStatus::UNDER_REVIEW => '👀',
                ApplicationStatus::SHORTLISTED => '⭐',
                ApplicationStatus::INTERVIEW => '🎯',
                ApplicationStatus::REJECTED => '❌',
                ApplicationStatus::ACCEPTED => '✅',
                ApplicationStatus::WITHDRAWN => '🚫',
                default => '⚪',
            };

            $vacancyTitle = mb_substr($app->vacancy->title ?? 'Vakansiya', 0, 25);

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$statusIcon} {$vacancyTitle}",
                    callback_data: 'app:view:' . $app->id
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '🌐 ' . ($lang === 'ru' ? 'Открыть Mini App' : 'Mini App ochish'),
                url: env('APP_URL') . '/miniapp#/applications'
            )
        )->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'menu:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );

        // Handle view callbacks
        foreach ($applications as $app) {
            $bot->onCallbackQueryData('app:view:' . $app->id, function (Nutgram $bot) use ($app, $lang) {
                $bot->answerCallbackQuery();
                $this->showApplicationDetails($bot, $app, $lang);
            });
        }
    }

    protected function showApplicationDetails(Nutgram $bot, Application $app, string $lang): void
    {
        $vacancy = $app->vacancy;

        $statusIcon = match ($app->status) {
            ApplicationStatus::NEW => '🆕',
            ApplicationStatus::UNDER_REVIEW => '👀',
            ApplicationStatus::SHORTLISTED => '⭐',
            ApplicationStatus::INTERVIEW => '🎯',
            ApplicationStatus::REJECTED => '❌',
            ApplicationStatus::ACCEPTED => '✅',
            ApplicationStatus::WITHDRAWN => '🚫',
            default => '⚪',
        };

        $statusText = match ($app->status) {
            ApplicationStatus::NEW => $lang === 'ru' ? 'Новая' : 'Yangi',
            ApplicationStatus::UNDER_REVIEW => $lang === 'ru' ? 'На рассмотрении' : 'Ko\'rib chiqilmoqda',
            ApplicationStatus::SHORTLISTED => $lang === 'ru' ? 'В избранном' : 'Tanlab olingan',
            ApplicationStatus::INTERVIEW => $lang === 'ru' ? 'Интервью назначено' : 'Intervyu belgilandi',
            ApplicationStatus::REJECTED => $lang === 'ru' ? 'Отклонена' : 'Rad etilgan',
            ApplicationStatus::ACCEPTED => $lang === 'ru' ? 'Принята' : 'Qabul qilingan',
            ApplicationStatus::WITHDRAWN => $lang === 'ru' ? 'Отозвана' : 'Bekor qilingan',
            default => $app->status->value,
        };

        $salary = '-';
        if ($vacancy->salary_type === 'negotiable') {
            $salary = $lang === 'ru' ? 'Договорная' : 'Kelishiladi';
        } elseif ($vacancy->salary_min && $vacancy->salary_max) {
            $salary = number_format($vacancy->salary_min) . ' - ' . number_format($vacancy->salary_max) . " so'm";
        }

        $text = "{$statusIcon} *{$vacancy->title}*\n\n"
            . "🏢 " . ($vacancy->employer->company_name ?? '-') . "\n"
            . "📍 " . ($vacancy->city ?? '-') . "\n"
            . "💰 {$salary}\n"
            . "📊 " . ($lang === 'ru' ? 'Статус' : 'Holat') . ": {$statusText}\n"
            . "📅 " . ($lang === 'ru' ? 'Подано' : 'Yuborildi') . ": " . $app->created_at->format('d.m.Y H:i') . "\n\n";

        if ($app->questionnaire_completed) {
            $percentage = $app->questionnaire_max_score > 0
                ? round(($app->questionnaire_score / $app->questionnaire_max_score) * 100)
                : 0;

            $text .= "📋 " . ($lang === 'ru' ? 'Анкета' : 'Savolnoma') . ": ✅\n";
            $text .= "📊 " . ($lang === 'ru' ? 'Результат' : 'Natija') . ": {$app->questionnaire_score}/{$app->questionnaire_max_score} ({$percentage}%)\n\n";
        } elseif ($app->status !== ApplicationStatus::REJECTED && $app->status !== ApplicationStatus::WITHDRAWN) {
            $text .= "⚠️ " . ($lang === 'ru' ? 'Анкета не заполнена' : 'Savolnoma to\'ldirilmagan') . "\n\n";
        }

        if ($app->cover_letter) {
            $text .= "✉️ " . ($lang === 'ru' ? 'Сопроводительное письмо' : 'Qo\'shimcha xat') . ":\n";
            $text .= mb_substr($app->cover_letter, 0, 150) . "...\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();

        // Show questionnaire button if not completed
        if (!$app->questionnaire_completed && $app->status !== ApplicationStatus::REJECTED && $app->status !== ApplicationStatus::WITHDRAWN) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📋 ' . ($lang === 'ru' ? 'Заполнить анкету' : 'Savolnomani to\'ldirish'),
                    callback_data: 'app:quest:' . $app->id
                )
            );
        }

        // Show withdraw button if not yet rejected/accepted/withdrawn
        if (!in_array($app->status, [ApplicationStatus::REJECTED, ApplicationStatus::ACCEPTED, ApplicationStatus::WITHDRAWN])) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '🚫 ' . ($lang === 'ru' ? 'Отозвать заявку' : 'Arizani bekor qilish'),
                    callback_data: 'app:withdraw:' . $app->id
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'app:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );

        $bot->onCallbackQueryData('app:quest:' . $app->id, function (Nutgram $bot) use ($app) {
            $bot->answerCallbackQuery();
            QuestionnaireConversation::begin($bot, $app->id);
        });

        $bot->onCallbackQueryData('app:withdraw:' . $app->id, function (Nutgram $bot) use ($app, $lang) {
            $app->update(['status' => ApplicationStatus::WITHDRAWN]);
            $bot->answerCallbackQuery(
                text: $lang === 'ru' ? '🚫 Заявка отозвана' : '🚫 Ariza bekor qilindi',
                show_alert: true
            );
            $this->showApplicationDetails($bot, $app->fresh(), $lang);
        });

        $bot->onCallbackQueryData('app:back', function (Nutgram $bot) {
            $bot->answerCallbackQuery();
            $this->__invoke($bot);
        });
    }
}
