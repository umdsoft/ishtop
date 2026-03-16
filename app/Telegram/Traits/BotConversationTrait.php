<?php

namespace App\Telegram\Traits;

use SergiX44\Nutgram\Nutgram;

trait BotConversationTrait
{
    protected string $lang = 'uz';

    protected function t(string $uz, string $ru): string
    {
        return $this->lang === 'ru' ? $ru : $uz;
    }

    protected function checkCancel(Nutgram $bot): bool
    {
        $message = $bot->message();
        if (!$message) {
            $this->end();
            return true;
        }
        $text = $message->text ?? '';
        if ($text === '/cancel') {
            $bot->sendMessage(text: $this->t('❌ Bekor qilindi. /menu — Bosh menyu', '❌ Отменено. /menu — Главное меню'));
            $this->end();
            return true;
        }
        return false;
    }

    protected function detectLang(int $telegramId): string
    {
        $user = \App\Models\User::where('telegram_id', $telegramId)->first();
        return $user?->language?->value ?? 'uz';
    }
}
