<?php

namespace App\Telegram\Keyboards;

use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class MainMenuKeyboard
{
    public static function make(): InlineKeyboardMarkup
    {
        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('🔍 Ish qidirish', callback_data: 'menu:search'),
                InlineKeyboardButton::make('📝 Rezume', callback_data: 'menu:resume'),
            )
            ->addRow(
                InlineKeyboardButton::make('📢 E\'lon berish', callback_data: 'menu:post'),
                InlineKeyboardButton::make('📋 Arizalarim', callback_data: 'menu:apps'),
            )
            ->addRow(
                InlineKeyboardButton::make('🌐 Mini App', url: 'https://t.me/IshTopBot/app'),
            )
            ->addRow(
                InlineKeyboardButton::make('⚙️ Sozlamalar', callback_data: 'menu:settings'),
                InlineKeyboardButton::make('❓ Yordam', callback_data: 'menu:help'),
            );
    }
}
