<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class HelpHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $lang = $user?->language?->value ?? 'uz';

        if ($lang === 'ru') {
            $text = "❓ *Помощь — IshTop Bot*\n\n"
                . "📌 *Команды:*\n"
                . "/start — Начало работы\n"
                . "/menu — Главное меню\n"
                . "/search — Поиск вакансий\n"
                . "/resume — Создать/обновить резюме\n"
                . "/post — Разместить вакансию\n"
                . "/apps — Мои заявки\n"
                . "/settings — Настройки\n"
                . "/help — Справка\n"
                . "/cancel — Отменить текущее действие\n\n"
                . "📱 *Mini App* — расширенный поиск, карта, фильтры\n"
                . "🌐 *Web Panel* — панель для работодателей\n\n"
                . "📞 Поддержка: @IshTopSupport";
        } else {
            $text = "❓ *Yordam — IshTop Bot*\n\n"
                . "📌 *Buyruqlar:*\n"
                . "/start — Boshlash\n"
                . "/menu — Bosh menyu\n"
                . "/search — Vakansiya qidirish\n"
                . "/resume — Rezume yaratish/yangilash\n"
                . "/post — E\'lon berish\n"
                . "/apps — Mening arizalarim\n"
                . "/settings — Sozlamalar\n"
                . "/help — Yordam\n"
                . "/cancel — Joriy amalni bekor qilish\n\n"
                . "📱 *Mini App* — kengaytirilgan qidiruv, xarita, filterlar\n"
                . "🌐 *Web Panel* — ish beruvchilar uchun panel\n\n"
                . "📞 Yordam: @IshTopSupport";
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('🌐 Mini App', url: 'https://t.me/IshTopBot/app'),
                )
                ->addRow(
                    InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back'),
                ),
        );
    }
}
