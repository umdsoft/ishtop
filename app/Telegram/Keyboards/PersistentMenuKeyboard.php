<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class PersistentMenuKeyboard
{
    public static function make(string $lang = 'uz', ?int $telegramId = null, bool $isVerified = true): ReplyKeyboardMarkup
    {
        $isRu = $lang === 'ru';

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true);

        $appUrl = config('app.url');
        if ($isVerified && config('app.miniapp_enabled', false)) {
            $miniappUrl = $appUrl . '/miniapp';
            if ($telegramId) {
                $token = encrypt((string) $telegramId);
                $miniappUrl .= '?auth_token=' . urlencode($token);
            }
            $keyboard->addRow(
                KeyboardButton::make(
                    text: $isRu ? '📱 Открыть приложение' : "📱 Ilovani ochish",
                    web_app: new WebAppInfo($miniappUrl)
                ),
            );
        }

        $keyboard
            ->addRow(
                KeyboardButton::make($isRu ? '🔍 Поиск работы' : '🔍 Ish qidirish'),
                KeyboardButton::make($isRu ? '📝 Резюме' : '📝 Rezume'),
            )
            ->addRow(
                KeyboardButton::make($isRu ? '📋 Мои заявки' : '📋 Arizalarim'),
                KeyboardButton::make($isRu ? '🤍 Сохранённые' : '🤍 Saqlanganlar'),
            )
            ->addRow(
                KeyboardButton::make($isRu ? '📌 Меню' : '📌 Menyu'),
            );

        return $keyboard;
    }
}
