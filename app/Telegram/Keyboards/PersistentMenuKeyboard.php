<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class PersistentMenuKeyboard
{
    public static function make(string $lang = 'uz', ?int $telegramId = null, bool $isVerified = true): ReplyKeyboardMarkup|ReplyKeyboardRemove
    {
        $isRu = $lang === 'ru';

        if (!$isVerified || !config('app.miniapp_enabled', false)) {
            return ReplyKeyboardRemove::make(true);
        }

        $keyboard = ReplyKeyboardMarkup::make(resize_keyboard: true);

        $appUrl = config('app.url');
        $miniappUrl = $appUrl . '/miniapp';
        if ($telegramId) {
            $token = encrypt((string) $telegramId);
            $miniappUrl .= '?auth_token=' . urlencode($token);
        }

        $keyboard->addRow(
            KeyboardButton::make(
                text: $isRu ? '📱 Открыть приложение' : '📱 Ilovani ochish',
                web_app: new WebAppInfo($miniappUrl)
            ),
        );

        return $keyboard;
    }
}
