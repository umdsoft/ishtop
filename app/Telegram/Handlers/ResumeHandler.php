<?php

namespace App\Telegram\Handlers;

use App\Enums\SearchStatus;
use App\Models\User;
use App\Models\WorkerProfile;
use App\Telegram\Conversations\ResumeBuilderConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ResumeHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $profile = $user->workerProfile;

        if (!$profile) {
            $this->showCreatePrompt($bot, $lang);
            return;
        }

        $this->showResume($bot, $profile, $lang);
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

        if ($data === 'resume:edit') {
            (new ResumeBuilderConversation())->begin($bot);
            return;
        }

        if ($data === 'resume:toggle_search') {
            $profile = $user->workerProfile;
            if (!$profile) return;

            $newStatus = $profile->search_status === SearchStatus::OPEN
                ? SearchStatus::CLOSED
                : SearchStatus::OPEN;

            $profile->update(['search_status' => $newStatus]);

            $msg = $newStatus === SearchStatus::OPEN
                ? ($lang === 'ru' ? '✅ Поиск активирован!' : '✅ Qidiruv faollashtirildi!')
                : ($lang === 'ru' ? '⏸ Поиск приостановлен' : '⏸ Qidiruv to\'xtatildi');

            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $this->showResume($bot, $profile->fresh(), $lang);
            return;
        }
    }

    protected function showCreatePrompt(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📝 *Создание резюме*\n\nУ вас ещё нет резюме.\nСоздайте его, чтобы работодатели могли найти вас!\n\n_Это займёт всего 3-5 минут._"
            : "📝 *Rezume yaratish*\n\nSizda hali rezume yo'q.\nIsh beruvchilar sizni topishi uchun rezume yarating!\n\n_Bu atigi 3-5 daqiqa vaqt oladi._";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '✏️ Создать резюме' : '✏️ Rezume yaratish',
                        callback_data: 'resume:create'
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

    protected function showResume(Nutgram $bot, WorkerProfile $profile, string $lang): void
    {
        $name = $profile->full_name ?? '-';
        $birth = $profile->birth_date ? $profile->birth_date->format('d.m.Y') : '-';
        $gender = match ($profile->gender) {
            'male' => $lang === 'ru' ? 'Мужской' : 'Erkak',
            'female' => $lang === 'ru' ? 'Женский' : 'Ayol',
            default => '-',
        };
        $city = $profile->city ?? '-';
        $edu = $profile->education_level ?? '-';
        $spec = $profile->specialty ?? '-';
        $exp = $profile->experience_years . ($lang === 'ru' ? ' лет' : ' yil');
        $skills = !empty($profile->skills) ? implode(', ', $profile->skills) : '-';

        $salary = '-';
        if ($profile->expected_salary_min && $profile->expected_salary_max) {
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($profile->expected_salary_min) . ' - ' . number_format($profile->expected_salary_max) . " {$currency}";
        } elseif ($profile->expected_salary_min) {
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($profile->expected_salary_min) . "+ {$currency}";
        }

        $workTypes = [];
        foreach ($profile->work_types ?? [] as $type) {
            $workTypes[] = match ($type) {
                'full_time' => $lang === 'ru' ? 'Полный день' : 'To\'liq kun',
                'part_time' => $lang === 'ru' ? 'Полставки' : 'Yarim stavka',
                'remote' => $lang === 'ru' ? 'Удалёнка' : 'Masofaviy',
                'temporary' => $lang === 'ru' ? 'Временная' : 'Vaqtinchalik',
                default => $type,
            };
        }
        $workType = !empty($workTypes) ? implode(', ', $workTypes) : '-';

        $status = match ($profile->search_status) {
            SearchStatus::OPEN => '🟢 ' . ($lang === 'ru' ? 'Активно ищу' : 'Faol qidiraman'),
            SearchStatus::CLOSED => '🔴 ' . ($lang === 'ru' ? 'Не ищу' : 'Qidirmayman'),
            SearchStatus::PASSIVE => '🟡 ' . ($lang === 'ru' ? 'Рассмотрю предложения' : 'Takliflarni ko\'rib chiqaman'),
            default => '-',
        };

        if ($lang === 'ru') {
            $text = "📝 *Ваше резюме*\n\n"
                . "👤 Имя: {$name}\n"
                . "📅 Дата рождения: {$birth}\n"
                . "👤 Пол: {$gender}\n"
                . "📍 Город: {$city}\n"
                . "🎓 Образование: {$edu}\n"
                . "💼 Специальность: {$spec}\n"
                . "⏱ Опыт: {$exp}\n"
                . "🛠 Навыки: {$skills}\n"
                . "💰 Зарплата: {$salary}\n"
                . "🏢 Тип работы: {$workType}\n\n"
                . "📊 Статус поиска: {$status}";
        } else {
            $text = "📝 *Sizning rezumengiz*\n\n"
                . "👤 Ism: {$name}\n"
                . "📅 Tug'ilgan sana: {$birth}\n"
                . "👤 Jins: {$gender}\n"
                . "📍 Shahar: {$city}\n"
                . "🎓 Ta'lim: {$edu}\n"
                . "💼 Mutaxassislik: {$spec}\n"
                . "⏱ Tajriba: {$exp}\n"
                . "🛠 Ko'nikmalar: {$skills}\n"
                . "💰 Maosh: {$salary}\n"
                . "🏢 Ish turi: {$workType}\n\n"
                . "📊 Qidiruv holati: {$status}";
        }

        if ($profile->bio) {
            $text .= "\n\n📋 " . ($lang === 'ru' ? 'О себе' : 'O\'zim haqimda') . ": {$profile->bio}";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    '✏️ ' . ($lang === 'ru' ? 'Редактировать' : 'Tahrirlash'),
                    callback_data: 'resume:edit'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $profile->search_status === SearchStatus::OPEN
                        ? '⏸ ' . ($lang === 'ru' ? 'Приостановить поиск' : 'Qidiruvni to\'xtatish')
                        : '▶️ ' . ($lang === 'ru' ? 'Активировать поиск' : 'Qidiruvni faollashtirish'),
                    callback_data: 'resume:toggle_search'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '🔗 ' . ($lang === 'ru' ? 'Поделиться' : 'Ulashish'),
                    switch_inline_query: 'resume'
                )
            )
            ->addRow(
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
}
