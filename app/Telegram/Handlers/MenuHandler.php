<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Telegram\Keyboards\MainMenuKeyboard;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MenuHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "📌 *IshTop — Bosh menyu*\n\nKerakli bo\'limni tanlang:",
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: MainMenuKeyboard::make(),
        );
    }

    public function handleCallback(Nutgram $bot): void
    {
        $action = str_replace('menu:', '', $bot->callbackQuery()->data);
        $bot->answerCallbackQuery();

        match ($action) {
            'search' => (new SearchHandler())($bot),
            'resume' => $this->resume($bot),
            'post' => $this->post($bot),
            'apps' => $this->apps($bot),
            'settings' => $this->settings($bot),
            'help' => (new HelpHandler())($bot),
            'back' => $this->backToMenu($bot),
            default => $bot->sendMessage('Tez orada...'),
        };
    }

    public function search(Nutgram $bot): void
    {
        (new SearchHandler())($bot);
    }

    public function resume(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "📝 *Rezume*\n\nRezumeni yaratish yoki tahrirlash uchun quyidagini tanlang:",
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✏️ Rezume yaratish', callback_data: 'resume:create'))
                ->addRow(InlineKeyboardButton::make("👁 Ko\'rish", callback_data: 'resume:view'))
                ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
        );
    }

    public function post(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "📢 *E\'lon berish*\n\n💰 Narx: 35,000 so\'m (15 kun)\n\nE\'lon yaratish uchun davom eting:",
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make("📝 E\'lon yaratish", callback_data: 'vacancy:create'))
                ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
        );
    }

    public function apps(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "📋 *Mening arizalarim*\n\nArizalaringizni Mini App da ko\'ring:",
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('🌐 Mini App', url: 'https://t.me/IshTopBot/app'))
                ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
        );
    }

    public function settings(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "⚙️ *Sozlamalar*",
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make("🇺🇿 O\'zbekcha", callback_data: 'lang:uz'),
                    InlineKeyboardButton::make('🇷🇺 Русский', callback_data: 'lang:ru'),
                )
                ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
        );
    }

    public function web(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "🌐 *Web Panel*\n\nIsh beruvchi sifatida web panelga kiring:\n👉 " . config('app.url') . "/recruiter",
            parse_mode: ParseMode::MARKDOWN,
        );
    }

    public function viewResume(Nutgram $bot): void
    {
        $bot->answerCallbackQuery();

        $user = User::where('telegram_id', $bot->user()->id)->first();
        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $profile = $user->workerProfile;
        if (!$profile) {
            $bot->sendMessage(
                text: "📝 Sizda rezume yo\'q. Yaratish uchun quyidagi tugmani bosing:",
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('✏️ Rezume yaratish', callback_data: 'resume:create'))
                    ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
            );
            return;
        }

        $gender = match ($profile->gender) {
            'male' => 'Erkak',
            'female' => 'Ayol',
            default => '-',
        };

        $skills = is_array($profile->skills) ? implode(', ', $profile->skills) : '-';
        $workTypes = is_array($profile->work_types)
            ? implode(', ', array_map(fn($t) => match ($t) {
                'full_time' => 'To\'liq kun',
                'part_time' => 'Yarim stavka',
                'remote' => 'Masofaviy',
                'temporary' => 'Vaqtinchalik',
                default => $t,
            }, $profile->work_types))
            : '-';

        $salary = '-';
        if ($profile->expected_salary_min && $profile->expected_salary_max) {
            $salary = number_format($profile->expected_salary_min) . ' - ' . number_format($profile->expected_salary_max) . " so\'m";
        }

        $text = "📋 *Mening rezumem*\n\n"
            . "👤 Ism: {$profile->full_name}\n"
            . "📅 Tug\'ilgan: " . ($profile->birth_date?->format('d.m.Y') ?? '-') . "\n"
            . "👤 Jins: {$gender}\n"
            . "📍 Shahar: " . ($profile->city ?? '-') . "\n"
            . "🎓 Ta\'lim: " . ($profile->education_level ?? '-') . "\n"
            . "💼 Mutaxassislik: " . ($profile->specialty ?? '-') . "\n"
            . "⏱ Tajriba: " . ($profile->experience_years ?? 0) . " yil\n"
            . "🛠 Ko\'nikmalar: {$skills}\n"
            . "🏢 Ish turi: {$workTypes}\n"
            . "💰 Maosh: {$salary}\n"
            . "📊 Status: " . ($profile->search_status?->label() ?? '-') . "\n"
            . "👁 Ko\'rishlar: {$profile->views_count}";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('✏️ Tahrirlash', callback_data: 'resume:create'))
                ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back')),
        );
    }

    protected function backToMenu(Nutgram $bot): void
    {
        $this->__invoke($bot);
    }
}
