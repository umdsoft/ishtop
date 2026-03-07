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
            $this->showEmployerPrompt($bot, $lang);
            return;
        }

        $this->showVacancyList($bot, $employer, $lang);
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        $user = User::where('telegram_id', $bot->user()->id)->first();
        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $lang = $user->language?->value ?? 'uz';

        if ($data === 'post:create') {
            (new PostVacancyConversation())->begin($bot);
            return;
        }

        if ($data === 'post:back') {
            $this->__invoke($bot);
            return;
        }

        if (str_starts_with($data, 'post:view:')) {
            $vacancyId = str_replace('post:view:', '', $data);
            $vacancy = Vacancy::find($vacancyId);
            if ($vacancy) {
                $this->showVacancyDetails($bot, $vacancy, $lang);
            }
            return;
        }

        if (str_starts_with($data, 'post:pause:')) {
            $vacancyId = str_replace('post:pause:', '', $data);
            $vacancy = Vacancy::find($vacancyId);
            if ($vacancy) {
                $vacancy->update(['status' => VacancyStatus::PAUSED]);
                $bot->answerCallbackQuery(
                    text: $lang === 'ru' ? '⏸ Вакансия приостановлена' : '⏸ Vakansiya to\'xtatildi',
                    show_alert: true
                );
                $this->showVacancyDetails($bot, $vacancy->fresh(), $lang);
            }
            return;
        }

        if (str_starts_with($data, 'post:activate:')) {
            $vacancyId = str_replace('post:activate:', '', $data);
            $vacancy = Vacancy::find($vacancyId);
            if ($vacancy) {
                $vacancy->update(['status' => VacancyStatus::ACTIVE]);
                $bot->answerCallbackQuery(
                    text: $lang === 'ru' ? '▶️ Вакансия активирована' : '▶️ Vakansiya faollashtirildi',
                    show_alert: true
                );
                $this->showVacancyDetails($bot, $vacancy->fresh(), $lang);
            }
            return;
        }
    }

    protected function showEmployerPrompt(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📢 *Размещение вакансий*\n\nЧтобы размещать вакансии, создайте профиль работодателя.\n\nЭто бесплатно и занимает 1 минуту!"
            : "📢 *Vakansiya e'lon qilish*\n\nVakansiya joylashtirish uchun ish beruvchi profilini yarating.\n\nBu bepul va 1 daqiqa vaqt oladi!";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
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
                parse_mode: ParseMode::MARKDOWN_LEGACY,
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

            $text .= "{$statusIcon} *{$vacancy->title()}*\n";
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

        foreach ($vacancies->take(5) as $vacancy) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📌 ' . mb_substr($vacancy->title(), 0, 30),
                    callback_data: 'post:view:' . $vacancy->id
                )
            );
        }

        $recruiterUrl = config('app.url') . '/recruiter';
        if (!str_contains($recruiterUrl, 'localhost')) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '🌐 ' . ($lang === 'ru' ? 'Открыть панель' : 'Panelni ochish'),
                    url: $recruiterUrl
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'menu:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
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
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($vacancy->salary_min) . ' - ' . number_format($vacancy->salary_max) . " {$currency}";
        } elseif ($vacancy->salary_min) {
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($vacancy->salary_min) . "+ {$currency}";
        }

        $text = "{$statusIcon} *{$vacancy->title()}*\n\n"
            . "📂 " . ($vacancy->category ?? '-') . "\n"
            . "📍 " . ($vacancy->city ?? '-') . "\n"
            . "💰 {$salary}\n"
            . "📊 " . ($lang === 'ru' ? 'Статус' : 'Holat') . ": {$statusText}\n\n"
            . "📝 " . ($lang === 'ru' ? 'Описание' : 'Tavsif') . ":\n" . mb_substr($vacancy->description($lang), 0, 200) . "...\n\n"
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

        $vacancyUrl = config('app.url') . '/recruiter/vacancies/' . $vacancy->id;
        if (!str_contains($vacancyUrl, 'localhost')) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📊 ' . ($lang === 'ru' ? 'Заявки' : 'Arizalar'),
                    url: $vacancyUrl
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'post:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }
}
