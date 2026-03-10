<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use App\Telegram\Keyboards\MainMenuKeyboard;
use App\Telegram\Keyboards\PersistentMenuKeyboard;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class RegistrationConversation extends Conversation
{
    public ?string $referralCode = null;

    public function start(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);

        $text = $lang === 'ru'
            ? "👋 Добро пожаловать в *KadrGo*!\n\n📱 Для регистрации отправьте свой номер телефона.\n\nНажмите кнопку ниже или введите вручную: +998XXXXXXXXX"
            : "👋 *KadrGo*ga xush kelibsiz!\n\n📱 Ro'yxatdan o'tish uchun telefon raqamingizni yuboring.\n\nQuyidagi tugmani bosing yoki qo'lda kiriting: +998XXXXXXXXX";

        $btnText = $lang === 'ru' ? '📱 Отправить номер' : '📱 Raqamni yuborish';

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make($btnText, request_contact: true)),
        );

        $this->next('handlePhone');
    }

    public function handlePhone(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);
        $contact = $bot->message()->contact;
        $text = $bot->message()->text;

        $phone = null;

        if ($contact) {
            $phone = '+' . ltrim($contact->phone_number, '+');
        } elseif ($text && preg_match('/^\+998\d{9}$/', $text)) {
            $phone = $text;
        } else {
            $errMsg = $lang === 'ru'
                ? '❌ Неверный формат. Введите номер в формате +998XXXXXXXXX или нажмите кнопку.'
                : "❌ Noto'g'ri format. +998XXXXXXXXX formatida kiriting yoki tugmani bosing.";
            $bot->sendMessage(text: $errMsg);
            return;
        }

        // Telegram orqali kelgan foydalanuvchi — OTP talab qilinmaydi
        // Darhol ro'yxatdan o'tkazamiz
        $tgUser = $bot->user();

        $referredBy = null;
        if ($this->referralCode) {
            $referrer = User::where('referral_code', $this->referralCode)->first();
            $referredBy = $referrer?->id;
        }

        $user = User::where('telegram_id', $tgUser->id)->first();
        if ($user) {
            $updateData = [
                'phone'       => $phone,
                'is_verified' => true,
            ];
            if ($referredBy) {
                $updateData['referred_by'] = $referredBy;
            }
            $user->update($updateData);
        }

        $bot->sendMessage(
            text: $lang === 'ru' ? '✅ Номер сохранён!' : '✅ Raqam saqlandi!',
            reply_markup: ReplyKeyboardRemove::make(true),
        );

        $welcome = $lang === 'ru'
            ? "🎉 *Регистрация завершена!*\n\nДобро пожаловать в KadrGo, {$user->first_name}!\n\n📌 /menu — Главное меню"
            : "🎉 *Ro'yxatdan o'tish yakunlandi!*\n\nKadrGo ga xush kelibsiz, {$user->first_name}!\n\n📌 /menu — Bosh menyu";

        $bot->sendMessage(
            text: $welcome,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: MainMenuKeyboard::make($lang, $bot->user()->id),
        );

        // Persistent keyboard (pastdagi tugmalar) ni ham o'rnatish
        $hint = $lang === 'ru'
            ? '📌 Используйте кнопки ниже для быстрого доступа'
            : '📌 Pastdagi tugmalardan foydalaning';

        $bot->sendMessage(
            text: $hint,
            reply_markup: PersistentMenuKeyboard::make($lang, $bot->user()->id),
        );

        $this->end();
    }

    private function getUserLang(Nutgram $bot): string
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        return $user?->language?->value ?? 'uz';
    }
}
