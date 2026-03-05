<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Keyboards\MainMenuKeyboard;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class StartHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();

        if ($user && $user->is_verified) {
            $user->update(['last_active_at' => now()]);

            $bot->sendMessage(
                text: "👋 Assalomu alaykum, {$user->first_name}!\n\n*IshTop* — Telegramdan chiqmay ish top!\n\n📌 /menu — Bosh menyu",
                parse_mode: ParseMode::MARKDOWN,
                reply_markup: MainMenuKeyboard::make(),
            );
            return;
        }

        $referralCode = null;
        $payload = $bot->message()->text ?? '';
        if (preg_match('/\/start\s+ref_(\w+)/', $payload, $matches)) {
            $referralCode = $matches[1];
        }

        $conversation = new RegistrationConversation();
        $conversation->referralCode = $referralCode;
        $conversation->begin($bot);
    }

    public function setLanguage(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        $lang = str_replace('lang:', '', $cb->data);

        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();

        if ($user) {
            $user->update(['language' => $lang]);

            $bot->answerCallbackQuery();

            $msg = $lang === 'ru'
                ? '✅ Язык изменён на Русский'
                : "✅ Til O\'zbekchaga o\'zgartirildi";

            $bot->editMessageText(
                text: $msg,
                message_id: $cb->message->message_id,
            );
            return;
        }

        $bot->answerCallbackQuery();
        $bot->sendMessage(text: "Avval /start buyrug\'ini yuboring.");
    }
}
