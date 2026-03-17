<?php

namespace App\Telegram\Conversations;

use App\Models\City;
use App\Models\User;
use App\Telegram\Handlers\StartHandler;
use App\Telegram\Keyboards\PersistentMenuKeyboard;
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
    public string $userLang = 'uz';
    public ?string $selectedRegion = null;
    public ?string $selectedDistrict = null;

    /**
     * Step 1: Viloyatni tanlash
     */
    public function start(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $this->userLang = $user?->language?->value ?? 'uz';
        $isRu = $this->userLang === 'ru';

        $text = $isRu
            ? "👋 Добро пожаловать в *KadrGo*!\n\n📍 Выберите свой регион:"
            : "👋 *KadrGo*ga xush kelibsiz!\n\n📍 Viloyatingizni tanlang:";

        $locations = City::cachedLocations();
        $regions = collect($locations['regions'])->pluck('key')->sort()->values();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($regions as $i => $region) {
            $label = $isRu
                ? (collect($locations['regions'])->firstWhere('key', $region)['name_ru'] ?? $region)
                : $region;

            $row[] = InlineKeyboardButton::make(
                $label,
                callback_data: 'reg_region:' . $region
            );
            if (count($row) === 2 || $i === $regions->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );

        $this->next('handleRegion');
    }

    /**
     * Step 2: Viloyat tanlandi → tumanlarni ko'rsatish
     */
    public function handleRegion(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'reg_region:')) {
            return;
        }

        $this->selectedRegion = str_replace('reg_region:', '', $cb->data);
        $bot->answerCallbackQuery();

        $isRu = $this->userLang === 'ru';
        $locations = City::cachedLocations();

        // Tanlangan viloyat nomini ko'rsatish
        $regionLabel = $isRu
            ? (collect($locations['regions'])->firstWhere('key', $this->selectedRegion)['name_ru'] ?? $this->selectedRegion)
            : $this->selectedRegion;

        // Tumanlarni ko'rsatish
        $cities = collect($locations['cities'])
            ->where('region', $this->selectedRegion)
            ->sortBy('name_uz')
            ->values();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        $total = $cities->count();
        foreach ($cities as $i => $city) {
            $name = $isRu ? ($city['name_ru'] ?? $city['name_uz']) : $city['name_uz'];
            $row[] = InlineKeyboardButton::make(
                $name,
                callback_data: 'reg_district:' . $city['name_uz']
            );
            if (count($row) === 2 || $i === $total - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        // Orqaga tugmasi
        $keyboard->addRow(InlineKeyboardButton::make(
            $isRu ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'reg_back_regions'
        ));

        $bot->editMessageText(
            text: $isRu
                ? "✅ Регион: *{$regionLabel}*\n\n🏘 Выберите район/город:"
                : "✅ Viloyat: *{$regionLabel}*\n\n🏘 Tuman/shaharni tanlang:",
            message_id: $cb->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );

        $this->next('handleDistrict');
    }

    /**
     * Step 3: Tuman tanlandi → telefon raqam so'rash
     */
    public function handleDistrict(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();

        if ($cb && $cb->data === 'reg_back_regions') {
            $bot->answerCallbackQuery();
            // Viloyatlar ro'yxatiga qaytish
            $this->showRegionsEdit($bot, $cb->message->message_id);
            $this->next('handleRegion');
            return;
        }

        if (!$cb || !str_starts_with($cb->data ?? '', 'reg_district:')) {
            return;
        }

        $this->selectedDistrict = str_replace('reg_district:', '', $cb->data);
        $bot->answerCallbackQuery();

        $isRu = $this->userLang === 'ru';

        // Tanlangan tuman nomini ko'rsatish
        $locations = City::cachedLocations();
        $city = collect($locations['cities'])->firstWhere('name_uz', $this->selectedDistrict);
        $districtLabel = $isRu ? ($city['name_ru'] ?? $this->selectedDistrict) : $this->selectedDistrict;
        $regionLabel = $isRu
            ? (collect($locations['regions'])->firstWhere('key', $this->selectedRegion)['name_ru'] ?? $this->selectedRegion)
            : $this->selectedRegion;

        $bot->editMessageText(
            text: $isRu
                ? "✅ Регион: *{$regionLabel}*\n✅ Район: *{$districtLabel}*"
                : "✅ Viloyat: *{$regionLabel}*\n✅ Tuman: *{$districtLabel}*",
            message_id: $cb->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );

        // Telefon raqam so'rash
        $text = $isRu
            ? "📱 Отправьте свой номер телефона для завершения регистрации.\n\nНажмите кнопку ниже или введите вручную: +998XXXXXXXXX"
            : "📱 Ro'yxatdan o'tishni yakunlash uchun telefon raqamingizni yuboring.\n\nQuyidagi tugmani bosing yoki qo'lda kiriting: +998XXXXXXXXX";

        $btnText = $isRu ? '📱 Отправить номер' : '📱 Raqamni yuborish';

        $bot->sendMessage(
            text: $text,
            reply_markup: ReplyKeyboardMarkup::make(resize_keyboard: true, one_time_keyboard: true)
                ->addRow(KeyboardButton::make($btnText, request_contact: true)),
        );

        $this->next('handlePhone');
    }

    /**
     * Step 4: Telefon raqam → ro'yxatdan o'tish yakunlanadi
     */
    public function handlePhone(Nutgram $bot): void
    {
        $lang = $this->userLang;
        $message = $bot->message();

        if (!$message) {
            $this->end();
            return;
        }

        $contact = $message->contact;
        $text = $message->text;

        $phone = null;

        if ($contact) {
            $phone = '+' . ltrim($contact->phone_number, '+');
        } elseif ($text && preg_match('/^\+?998\d{9}$/', $text)) {
            $phone = str_starts_with($text, '+') ? $text : '+' . $text;
        } else {
            $errMsg = $lang === 'ru'
                ? '❌ Неверный формат. Введите номер в формате +998XXXXXXXXX или нажмите кнопку.'
                : "❌ Noto'g'ri format. +998XXXXXXXXX formatida kiriting yoki tugmani bosing.";
            $bot->sendMessage(text: $errMsg);
            return;
        }

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
                'region'      => $this->selectedRegion,
                'district'    => $this->selectedDistrict,
            ];
            if ($referredBy) {
                $updateData['referred_by'] = $referredBy;
            }
            $user->update($updateData);
        }

        $bot->sendMessage(
            text: $lang === 'ru' ? '✅ Регистрация завершена!' : '✅ Ro\'yxatdan o\'tish yakunlandi!',
            reply_markup: ReplyKeyboardRemove::make(true),
        );

        // Mini app menu tugmasini o'rnatish
        StartHandler::setMiniAppMenuButton($bot, $tgUser->id);

        $welcome = $lang === 'ru'
            ? "🎉 Добро пожаловать в KadrGo, *{$user->first_name}*!\n\n📱 Нажмите кнопку *Открыть приложение* ниже для поиска работы"
            : "🎉 KadrGo ga xush kelibsiz, *{$user->first_name}*!\n\n📱 Ish qidirish uchun pastdagi *Ilovani ochish* tugmasini bosing";

        $bot->sendMessage(
            text: $welcome,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: PersistentMenuKeyboard::make($lang, $tgUser->id),
        );

        $this->end();
    }

    /**
     * Viloyatlar ro'yxatini edit message bilan ko'rsatish (orqaga tugmasi uchun)
     */
    private function showRegionsEdit(Nutgram $bot, int $messageId): void
    {
        $isRu = $this->userLang === 'ru';
        $locations = City::cachedLocations();
        $regions = collect($locations['regions'])->pluck('key')->sort()->values();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($regions as $i => $region) {
            $label = $isRu
                ? (collect($locations['regions'])->firstWhere('key', $region)['name_ru'] ?? $region)
                : $region;

            $row[] = InlineKeyboardButton::make(
                $label,
                callback_data: 'reg_region:' . $region
            );
            if (count($row) === 2 || $i === $regions->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $bot->editMessageText(
            text: $isRu
                ? "📍 Выберите свой регион:"
                : "📍 Viloyatingizni tanlang:",
            message_id: $messageId,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }
}
