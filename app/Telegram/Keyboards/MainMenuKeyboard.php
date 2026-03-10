<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class MainMenuKeyboard
{
    public static function make(string $lang = 'uz', ?int $telegramId = null): InlineKeyboardMarkup
    {
        $isRu = $lang === 'ru';

        $keyboard = InlineKeyboardMarkup::make();

        $appUrl = config('app.url');
        if (config('app.miniapp_enabled', false)) {
            $miniappUrl = $appUrl . '/miniapp';
            if ($telegramId) {
                $token = encrypt((string) $telegramId);
                $miniappUrl .= '?auth_token=' . urlencode($token);
            }
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📱 Открыть приложение' : "📱 Ilovani ochish",
                    web_app: new WebAppInfo($miniappUrl)
                ),
            );
        }

        $keyboard
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '🔍 Поиск работы' : '🔍 Ish qidirish',
                    callback_data: 'menu:search'
                ),
                InlineKeyboardButton::make(
                    $isRu ? '📝 Резюме' : '📝 Rezume',
                    callback_data: 'menu:resume'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📢 Дать объявление' : "📢 E'lon berish",
                    callback_data: 'menu:post'
                ),
                InlineKeyboardButton::make(
                    $isRu ? '📋 Мои заявки' : '📋 Arizalarim',
                    callback_data: 'menu:apps'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '🤍 Сохранённые' : '🤍 Saqlanganlar',
                    callback_data: 'menu:saved'
                ),
                InlineKeyboardButton::make(
                    $isRu ? '🔔 Уведомления' : '🔔 Bildirishnomalar',
                    callback_data: 'menu:notifications'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '⚙️ Настройки' : '⚙️ Sozlamalar',
                    callback_data: 'menu:settings'
                ),
                InlineKeyboardButton::make(
                    $isRu ? '❓ Помощь' : '❓ Yordam',
                    callback_data: 'menu:help'
                ),
            );

        return $keyboard;
    }
}
