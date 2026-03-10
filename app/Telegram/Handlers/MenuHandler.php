<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Telegram\Keyboards\MainMenuKeyboard;
use App\Telegram\Keyboards\PersistentMenuKeyboard;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MenuHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);

        $text = $lang === 'ru'
            ? "📌 *KadrGo — Главное меню*\n\nВыберите нужный раздел:"
            : "📌 *KadrGo — Bosh menyu*\n\nKerakli bo'limni tanlang:";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: MainMenuKeyboard::make($lang, $bot->user()->id),
        );
    }

    public function handleCallback(Nutgram $bot): void
    {
        $action = str_replace('menu:', '', $bot->callbackQuery()->data);
        $bot->answerCallbackQuery();

        try {
            match ($action) {
                'search' => (new SearchHandler())($bot),
                'resume' => $this->resume($bot),
                'post' => $this->post($bot),
                'apps' => (new AppsHandler())($bot),
                'saved' => (new SavedHandler())($bot),
                'notifications' => (new NotificationsHandler())($bot),
                'settings' => (new SettingsHandler())($bot),
                'help' => (new HelpHandler())($bot),
                'back' => $this->backToMenu($bot),
                default => $bot->sendMessage(
                    $this->getUserLang($bot) === 'ru' ? 'Функция в разработке.' : 'Tez orada...'
                ),
            };
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("MenuHandler [{$action}] error: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. Iltimos qaytadan urinib ko'ring. /menu");
        }
    }

    public function resume(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);

        $text = $lang === 'ru'
            ? "📝 *Резюме*\n\nСоздайте или отредактируйте своё резюме:"
            : "📝 *Rezume*\n\nRezumeni yaratish yoki tahrirlash uchun quyidagini tanlang:";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $lang === 'ru' ? '✏️ Создать резюме' : '✏️ Rezume yaratish',
                    callback_data: 'resume:create'
                ))
                ->addRow(InlineKeyboardButton::make(
                    $lang === 'ru' ? '👁 Посмотреть' : "👁 Ko'rish",
                    callback_data: 'resume:view'
                ))
                ->addRow(InlineKeyboardButton::make(
                    $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'menu:back'
                )),
        );
    }

    public function post(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);

        $text = $lang === 'ru'
            ? "📢 *Дать объявление*\n\n💰 Цена: 35,000 сум (15 дней)\n\nНажмите для создания объявления:"
            : "📢 *E'lon berish*\n\n💰 Narx: 35,000 so'm (15 kun)\n\nE'lon yaratish uchun davom eting:";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $lang === 'ru' ? "📝 Создать объявление" : "📝 E'lon yaratish",
                    callback_data: 'vacancy:create'
                ))
                ->addRow(InlineKeyboardButton::make(
                    $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'menu:back'
                )),
        );
    }

    public function web(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);

        $text = $lang === 'ru'
            ? "🌐 *Веб панель*\n\nВойдите в панель работодателя:\n👉 " . config('app.url') . "/recruiter"
            : "🌐 *Web Panel*\n\nIsh beruvchi sifatida web panelga kiring:\n👉 " . config('app.url') . "/recruiter";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
    }

    public function viewResume(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $user = User::where('telegram_id', $bot->user()->id)->first();
        if (!$user) {
            $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $profile = $user->workerProfile;

        if (!$profile) {
            $bot->sendMessage(
                text: $lang === 'ru'
                    ? "📝 У вас нет резюме. Нажмите кнопку ниже для создания:"
                    : "📝 Sizda rezume yo'q. Yaratish uchun quyidagi tugmani bosing:",
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(
                        $lang === 'ru' ? '✏️ Создать резюме' : '✏️ Rezume yaratish',
                        callback_data: 'resume:create'
                    ))
                    ->addRow(InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )),
            );
            return;
        }

        $isRu = $lang === 'ru';

        $gender = match ($profile->gender) {
            'male' => $isRu ? 'Мужской' : 'Erkak',
            'female' => $isRu ? 'Женский' : 'Ayol',
            default => '-',
        };

        $skills = is_array($profile->skills) ? implode(', ', $profile->skills) : '-';
        $workTypes = is_array($profile->work_types)
            ? implode(', ', array_map(fn($t) => match ($t) {
                'full_time' => $isRu ? 'Полная занятость' : "To'liq kun",
                'part_time' => $isRu ? 'Частичная' : 'Yarim stavka',
                'remote' => $isRu ? 'Удалённо' : 'Masofaviy',
                'temporary' => $isRu ? 'Временная' : 'Vaqtinchalik',
                default => $t,
            }, $profile->work_types))
            : '-';

        $salary = '-';
        if ($profile->expected_salary_min && $profile->expected_salary_max) {
            $currency = $isRu ? 'сум' : "so'm";
            $salary = number_format($profile->expected_salary_min) . ' - ' . number_format($profile->expected_salary_max) . " {$currency}";
        }

        $text = ($isRu ? "📋 *Моё резюме*\n\n" : "📋 *Mening rezumem*\n\n")
            . "👤 " . ($isRu ? 'Имя' : 'Ism') . ": {$profile->full_name}\n"
            . "📅 " . ($isRu ? 'Дата рождения' : "Tug'ilgan") . ": " . ($profile->birth_date?->format('d.m.Y') ?? '-') . "\n"
            . "👤 " . ($isRu ? 'Пол' : 'Jins') . ": {$gender}\n"
            . "📍 " . ($isRu ? 'Город' : 'Shahar') . ": " . ($profile->city ?? '-') . "\n"
            . "🎓 " . ($isRu ? 'Образование' : "Ta'lim") . ": " . ($profile->education_level ?? '-') . "\n"
            . "💼 " . ($isRu ? 'Специальность' : 'Mutaxassislik') . ": " . ($profile->specialty ?? '-') . "\n"
            . "⏱ " . ($isRu ? 'Опыт' : 'Tajriba') . ": " . ($profile->experience_years ?? 0) . ($isRu ? ' лет' : ' yil') . "\n"
            . "🛠 " . ($isRu ? 'Навыки' : "Ko'nikmalar") . ": {$skills}\n"
            . "🏢 " . ($isRu ? 'Тип работы' : 'Ish turi') . ": {$workTypes}\n"
            . "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n"
            . "📊 " . ($isRu ? 'Статус' : 'Status') . ": " . ($profile->search_status?->label() ?? '-') . "\n"
            . "👁 " . ($isRu ? 'Просмотры' : "Ko'rishlar") . ": {$profile->views_count}";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $isRu ? '✏️ Редактировать' : '✏️ Tahrirlash',
                    callback_data: 'resume:create'
                ))
                ->addRow(InlineKeyboardButton::make(
                    $isRu ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'menu:back'
                )),
        );
    }

    protected function backToMenu(Nutgram $bot): void
    {
        $this->__invoke($bot);
    }

    private function getUserLang(Nutgram $bot): string
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        return $user?->language?->value ?? 'uz';
    }
}
