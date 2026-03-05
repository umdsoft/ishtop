<?php

namespace App\Telegram\Handlers;

use App\Enums\VacancyStatus;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\Vacancy;
use App\Telegram\Conversations\PostVacancyConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * PostHandler
 * Handles /post command and vacancy management
 */
class PostHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $employer = $user->employerProfile;

        if (!$employer) {
            // No employer profile yet
            $this->showEmployerPrompt($bot, $lang);
            return;
        }

        // Show vacancy list
        $this->showVacancyList($bot, $employer, $lang);
    }

    protected function showEmployerPrompt(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📢 *Размещение вакансий*\n\nЧтобы размещать вакансии, создайте профиль работодателя.\n\nЭто бесплатно и занимает 1 минуту!"
            : "📢 *Vakansiya e'lon qilish*\n\nVakansiya joylashtirish uchun ish beruvchi profilini yarating.\n\nBu bepul va 1 daqiqa vaqt oladi!";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '✏️ Создать вакансию' : '✏️ Vakansiya yaratish',
                        callback_data: 'post:create'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )
                ),
        );

        $bot->onCallbackQueryData('post:create', function (Nutgram $bot) {
            $bot->answerCallbackQuery();
            PostVacancyConversation::begin($bot);
        });
    }

    protected function showVacancyList(Nutgram $bot, EmployerProfile $employer, string $lang): void
    {
        $vacancies = Vacancy::where('employer_id', $employer->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $header = $lang === 'ru'
            ? "📢 *Мои вакансии*\n\n"
            : "📢 *Mening vakansiyalarim*\n\n";

        if ($vacancies->isEmpty()) {
            $text = $header . ($lang === 'ru'
                    ? "У вас пока нет вакансий.\nСоздайте первую вакансию!"
                    : "Sizda hali vakansiya yo'q.\nBirinchi vakansiyani yarating!");

            $bot->sendMessage(
                text: $text,
                parse_mode: ParseMode::MARKDOWN,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make(
                            '➕ ' . ($lang === 'ru' ? 'Создать вакансию' : 'Vakansiya yaratish'),
                            callback_data: 'post:create'
                        )
                    )
                    ->addRow(
                        InlineKeyboardButton::make(
                            '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                            callback_data: 'menu:back'
                        )
                    ),
            );

            $bot->onCallbackQueryData('post:create', function (Nutgram $bot) {
                $bot->answerCallbackQuery();
                PostVacancyConversation::begin($bot);
            });

            return;
        }

        $text = $header;

        foreach ($vacancies as $vacancy) {
            $statusIcon = match ($vacancy->status) {
                VacancyStatus::ACTIVE => '🟢',
                VacancyStatus::PENDING => '🟡',
                VacancyStatus::PAUSED => '⏸',
                VacancyStatus::CLOSED => '⏹',
                VacancyStatus::REJECTED => '🔴',
                default => '⚪',
            };

            $statusText = match ($vacancy->status) {
                VacancyStatus::ACTIVE => $lang === 'ru' ? 'Активна' : 'Faol',
                VacancyStatus::PENDING => $lang === 'ru' ? 'На модерации' : 'Moderatsiyada',
                VacancyStatus::PAUSED => $lang === 'ru' ? 'Приостановлена' : 'To\'xtatilgan',
                VacancyStatus::CLOSED => $lang === 'ru' ? 'Закрыта' : 'Yopilgan',
                VacancyStatus::REJECTED => $lang === 'ru' ? 'Отклонена' : 'Rad etilgan',
                default => '-',
            };

            $viewsCount = $vacancy->views_count ?? 0;
            $appsCount = $vacancy->applications_count ?? 0;

            $text .= "{$statusIcon} *{$vacancy->title}*\n";
            $text .= "   📊 {$statusText} • 👁 {$viewsCount} • 📝 {$appsCount}\n";
            $text .= "   📅 " . $vacancy->created_at->format('d.m.Y') . "\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    '➕ ' . ($lang === 'ru' ? 'Новая вакансия' : 'Yangi vakansiya'),
                    callback_data: 'post:create'
                )
            );

        // Add first 5 vacancies as buttons
        foreach ($vacancies->take(5) as $vacancy) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📌 ' . mb_substr($vacancy->title, 0, 30),
                    callback_data: 'post:view:' . $vacancy->id
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '🌐 ' . ($lang === 'ru' ? 'Открыть панель' : 'Panelni ochish'),
                url: env('APP_URL') . '/recruiter'
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

        $bot->onCallbackQueryData('post:create', function (Nutgram $bot) {
            $bot->answerCallbackQuery();
            PostVacancyConversation::begin($bot);
        });

        // Handle view vacancy
        foreach ($vacancies as $vacancy) {
            $bot->onCallbackQueryData('post:view:' . $vacancy->id, function (Nutgram $bot) use ($vacancy, $lang) {
                $bot->answerCallbackQuery();
                $this->showVacancyDetails($bot, $vacancy, $lang);
            });
        }
    }

    protected function showVacancyDetails(Nutgram $bot, Vacancy $vacancy, string $lang): void
    {
        $statusIcon = match ($vacancy->status) {
            VacancyStatus::ACTIVE => '🟢',
            VacancyStatus::PENDING => '🟡',
            VacancyStatus::PAUSED => '⏸',
            VacancyStatus::CLOSED => '⏹',
            VacancyStatus::REJECTED => '🔴',
            default => '⚪',
        };

        $statusText = match ($vacancy->status) {
            VacancyStatus::ACTIVE => $lang === 'ru' ? 'Активна' : 'Faol',
            VacancyStatus::PENDING => $lang === 'ru' ? 'На модерации' : 'Moderatsiyada',
            VacancyStatus::PAUSED => $lang === 'ru' ? 'Приостановлена' : 'To\'xtatilgan',
            VacancyStatus::CLOSED => $lang === 'ru' ? 'Закрыта' : 'Yopilgan',
            VacancyStatus::REJECTED => $lang === 'ru' ? 'Отклонена' : 'Rad etilgan',
            default => '-',
        };

        $salary = '-';
        if ($vacancy->salary_type === 'negotiable') {
            $salary = $lang === 'ru' ? 'Договорная' : 'Kelishiladi';
        } elseif ($vacancy->salary_min && $vacancy->salary_max) {
            $salary = number_format($vacancy->salary_min) . ' - ' . number_format($vacancy->salary_max) . " so'm";
        } elseif ($vacancy->salary_min) {
            $salary = number_format($vacancy->salary_min) . "+ so'm";
        }

        $text = "{$statusIcon} *{$vacancy->title}*\n\n"
            . "📂 " . ($vacancy->category ?? '-') . "\n"
            . "📍 " . ($vacancy->city ?? '-') . "\n"
            . "💰 {$salary}\n"
            . "📊 " . ($lang === 'ru' ? 'Статус' : 'Holat') . ": {$statusText}\n\n"
            . "📝 " . ($lang === 'ru' ? 'Описание' : 'Tavsif') . ":\n" . mb_substr($vacancy->description, 0, 200) . "...\n\n"
            . "📊 " . ($lang === 'ru' ? 'Статистика' : 'Statistika') . ":\n"
            . "👁 " . ($vacancy->views_count ?? 0) . " " . ($lang === 'ru' ? 'просмотров' : 'ko\'rildi') . "\n"
            . "📝 " . ($vacancy->applications_count ?? 0) . " " . ($lang === 'ru' ? 'заявок' : 'ariza');

        $keyboard = InlineKeyboardMarkup::make();

        if ($vacancy->status === VacancyStatus::ACTIVE) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '⏸ ' . ($lang === 'ru' ? 'Приостановить' : 'To\'xtatish'),
                    callback_data: 'post:pause:' . $vacancy->id
                )
            );
        } elseif ($vacancy->status === VacancyStatus::PAUSED) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '▶️ ' . ($lang === 'ru' ? 'Активировать' : 'Faollashtirish'),
                    callback_data: 'post:activate:' . $vacancy->id
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '📊 ' . ($lang === 'ru' ? 'Заявки' : 'Arizalar'),
                url: env('APP_URL') . '/recruiter/vacancies/' . $vacancy->id
            )
        )->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'post:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );

        $bot->onCallbackQueryData('post:pause:' . $vacancy->id, function (Nutgram $bot) use ($vacancy, $lang) {
            $vacancy->update(['status' => VacancyStatus::PAUSED]);
            $bot->answerCallbackQuery(
                text: $lang === 'ru' ? '⏸ Вакансия приостановлена' : '⏸ Vakansiya to\'xtatildi',
                show_alert: true
            );
            $this->showVacancyDetails($bot, $vacancy->fresh(), $lang);
        });

        $bot->onCallbackQueryData('post:activate:' . $vacancy->id, function (Nutgram $bot) use ($vacancy, $lang) {
            $vacancy->update(['status' => VacancyStatus::ACTIVE]);
            $bot->answerCallbackQuery(
                text: $lang === 'ru' ? '▶️ Вакансия активирована' : '▶️ Vakansiya faollashtirildi',
                show_alert: true
            );
            $this->showVacancyDetails($bot, $vacancy->fresh(), $lang);
        });

        $bot->onCallbackQueryData('post:back', function (Nutgram $bot) {
            $bot->answerCallbackQuery();
            $this->__invoke($bot);
        });
    }
}
