<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class TermsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $lang = $user?->language?->value ?? 'uz';

        $appUrl = config('app.url');
        $termsUrl = $appUrl . '/terms';
        $privacyUrl = $appUrl . '/privacy';

        if ($lang === 'ru') {
            $text = "📄 *Условия использования KadrGo*\n\n"
                . "Используя платформу KadrGo, вы соглашаетесь с нашими условиями:\n\n"
                . "• Платформа является *посредником* между работодателями и соискателями\n"
                . "• Ответственность за содержание вакансий и резюме несёт *разместивший пользователь*\n"
                . "• Запрещено размещать ложную информацию и нарушать законодательство\n"
                . "• Ваши данные защищены и не передаются третьим лицам без согласия\n\n"
                . "📌 Полные условия доступны по ссылкам ниже";
        } else {
            $text = "📄 *KadrGo foydalanish shartlari*\n\n"
                . "KadrGo platformasidan foydalanib, siz shartlarimizga rozilik bildirasiz:\n\n"
                . "• Platforma ish beruvchilar va ish izlovchilar o'rtasida *vositachi* sifatida ishlaydi\n"
                . "• Vakansiyalar va rezyumelar uchun javobgarlik *joylashtirgan foydalanuvchi* zimmasida\n"
                . "• Yolg'on ma'lumot joylashtirish va qonunbuzarlik taqiqlanadi\n"
                . "• Ma'lumotlaringiz himoyalangan va roziligingiz siz uchinchi tomonlarga berilmaydi\n\n"
                . "📌 To'liq shartlar quyidagi havolalarda";
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '📋 Условия использования' : '📋 Foydalanish shartlari',
                        url: $termsUrl,
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '🔒 Политика конфиденциальности' : '🔒 Maxfiylik siyosati',
                        url: $privacyUrl,
                    ),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back',
                    ),
                ),
        );
    }
}
