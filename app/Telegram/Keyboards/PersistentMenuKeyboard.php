<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class PersistentMenuKeyboard
{
    public static function make(string $lang = 'uz'): ReplyKeyboardMarkup
    {
        $isRu = $lang === 'ru';

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true)
            ->addRow(
                KeyboardButton::make($isRu ? '🔍 Поиск работы' : '🔍 Ish qidirish'),
                KeyboardButton::make($isRu ? '📝 Резюме' : '📝 Rezume'),
            )
            ->addRow(
                KeyboardButton::make($isRu ? '📋 Мои заявки' : '📋 Arizalarim'),
                KeyboardButton::make($isRu ? '🤍 Сохранённые' : '🤍 Saqlanganlar'),
            );

        $appUrl = config('app.url');
        if (config('app.miniapp_enabled', false)) {
            $keyboard->addRow(
                KeyboardButton::make(
                    text: '🌐 Mini App',
                    web_app: new WebAppInfo($appUrl . '/miniapp')
                ),
            );
        }

        $keyboard->addRow(
            KeyboardButton::make($isRu ? '📌 Меню' : '📌 Menyu'),
        );

        return $keyboard;
    }
}
