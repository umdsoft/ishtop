<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use App\Services\SmsService;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\KeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\Keyboard\ReplyKeyboardRemove;

class RegistrationConversation extends Conversation
{
    public ?string $referralCode = null;

    protected string $lang = 'uz';
    protected ?string $phone = null;
    protected ?string $firstName = null;

    public function start(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: "🇺🇿 Tilni tanlang / 🇷🇺 Выберите язык",
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make("🇺🇿 O\'zbekcha", callback_data: 'reg_lang:uz'),
                    InlineKeyboardButton::make('🇷🇺 Русский', callback_data: 'reg_lang:ru'),
                )
        );

        $this->next('handleLanguage');
    }

    public function handleLanguage(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'reg_lang:')) {
            return;
        }

        $this->lang = str_replace('reg_lang:', '', $cb->data);
        $bot->answerCallbackQuery();

        $bot->editMessageText(
            text: $this->lang === 'ru' ? '✅ Язык: Русский' : "✅ Til: O\'zbekcha",
            message_id: $cb->message->message_id,
        );

        $text = $this->lang === 'ru'
            ? "📱 Отправьте свой номер телефона для регистрации.\n\nНажмите кнопку ниже или введите вручную в формате: +998XXXXXXXXX"
            : "📱 Ro\'yxatdan o\'tish uchun telefon raqamingizni yuboring.\n\nQuyidagi tugmani bosing yoki qo\'lda kiriting: +998XXXXXXXXX";

        $btnText = $this->lang === 'ru' ? '📱 Отправить номер' : '📱 Raqamni yuborish';

        $bot->sendMessage(
            text: $text,
            reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make($btnText, request_contact: true)),
        );

        $this->next('handlePhone');
    }

    public function handlePhone(Nutgram $bot): void
    {
        $contact = $bot->message()->contact;
        $text = $bot->message()->text;

        if ($contact) {
            $this->phone = '+' . ltrim($contact->phone_number, '+');
        } elseif ($text && preg_match('/^\+998\d{9}$/', $text)) {
            $this->phone = $text;
        } else {
            $errMsg = $this->lang === 'ru'
                ? '❌ Неверный формат. Введите номер в формате +998XXXXXXXXX или нажмите кнопку.'
                : '❌ Noto\'g\'ri format. +998XXXXXXXXX formatida kiriting yoki tugmani bosing.';
            $bot->sendMessage(text: $errMsg);
            return;
        }

        $bot->sendMessage(
            text: $this->lang === 'ru' ? '⏳ Отправляем код...' : '⏳ Kod yuborilmoqda...',
            reply_markup: ReplyKeyboardRemove::make(),
        );

        $sms = app(SmsService::class);
        $code = $sms->sendOtp($this->phone);

        if (!app()->environment('production')) {
            $bot->sendMessage(text: "🔑 Test kod: {$code}");
        }

        $otpMsg = $this->lang === 'ru'
            ? "📩 Код отправлен на {$this->phone}\n\nВведите 6-значный код:"
            : "📩 {$this->phone} raqamiga kod yuborildi.\n\n6 xonali kodni kiriting:";

        $bot->sendMessage(text: $otpMsg);

        $this->next('handleOtp');
    }

    public function handleOtp(Nutgram $bot): void
    {
        $code = trim($bot->message()->text ?? '');

        if (!preg_match('/^\d{6}$/', $code)) {
            $errMsg = $this->lang === 'ru'
                ? '❌ Код должен содержать 6 цифр. Попробуйте ещё раз:'
                : '❌ Kod 6 ta raqamdan iborat bo\'lishi kerak. Qaytadan kiriting:';
            $bot->sendMessage(text: $errMsg);
            return;
        }

        $sms = app(SmsService::class);
        if (!$sms->verifyOtp($this->phone, $code)) {
            $errMsg = $this->lang === 'ru'
                ? '❌ Неверный код. Попробуйте ещё раз:'
                : '❌ Kod noto\'g\'ri. Qaytadan kiriting:';
            $bot->sendMessage(text: $errMsg);
            return;
        }

        $bot->sendMessage(
            text: $this->lang === 'ru' ? '✅ Номер подтверждён!' : '✅ Raqam tasdiqlandi!'
        );

        $nameMsg = $this->lang === 'ru'
            ? '👤 Введите ваше полное имя (Имя Фамилия):'
            : '👤 To\'liq ismingizni kiriting (Ism Familiya):';

        $bot->sendMessage(text: $nameMsg);

        $this->next('handleName');
    }

    public function handleName(Nutgram $bot): void
    {
        $name = trim($bot->message()->text ?? '');

        if (mb_strlen($name) < 2) {
            $errMsg = $this->lang === 'ru'
                ? '❌ Имя слишком короткое. Введите полное имя:'
                : '❌ Ism juda qisqa. To\'liq ismingizni kiriting:';
            $bot->sendMessage(text: $errMsg);
            return;
        }

        $parts = explode(' ', $name, 2);
        $this->firstName = $parts[0];
        $lastName = $parts[1] ?? null;

        $telegramUser = $bot->user();

        $referredBy = null;
        if ($this->referralCode) {
            $referrer = User::where('referral_code', $this->referralCode)->first();
            $referredBy = $referrer?->id;
        }

        $user = User::updateOrCreate(
            ['telegram_id' => $telegramUser->id],
            [
                'first_name' => $this->firstName,
                'last_name' => $lastName,
                'username' => $telegramUser->username,
                'phone' => $this->phone,
                'language' => $this->lang,
                'is_verified' => true,
                'referral_code' => User::generateReferralCode(),
                'referred_by' => $referredBy,
            ]
        );

        $welcome = $this->lang === 'ru'
            ? "🎉 *Регистрация завершена!*\n\nДобро пожаловать в IshTop, {$this->firstName}!\n\n📌 Отправьте /menu чтобы открыть главное меню."
            : "🎉 *Ro\'yxatdan o\'tish yakunlandi!*\n\nIshTop ga xush kelibsiz, {$this->firstName}!\n\n📌 Bosh menyuni ochish uchun /menu yuboring.";

        $bot->sendMessage(
            text: $welcome,
            parse_mode: ParseMode::MARKDOWN,
        );

        $this->end();
    }
}
