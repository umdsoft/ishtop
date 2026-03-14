<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SettingsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = $this->getUser($bot);
        if (!$user) {
            $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
            return;
        }

        $this->showSettings($bot, $user);
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        $user = $this->getUser($bot);
        if (!$user) {
            $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
            return;
        }

        // settings:language
        if ($data === 'settings:language') {
            $this->showLanguageSettings($bot, $user);
            return;
        }

        // settings:lang:uz / settings:lang:ru
        if (str_starts_with($data, 'settings:lang:')) {
            $newLang = str_replace('settings:lang:', '', $data);
            $user->update(['language' => $newLang]);
            cache()->forget("user_lang:{$bot->user()->id}");
            $msg = $newLang === 'ru' ? '✅ Язык изменён' : "✅ Til o'zgartirildi";
            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $this->showSettings($bot, $user->fresh());
            return;
        }

        // settings:toggle_notifications
        if ($data === 'settings:toggle_notifications') {
            $newValue = !$user->notifications_enabled;
            $user->update(['notifications_enabled' => $newValue]);
            $lang = $user->language?->value ?? 'uz';
            $msg = $newValue
                ? ($lang === 'ru' ? '🔔 Уведомления включены' : '🔔 Bildirishnomalar yoqildi')
                : ($lang === 'ru' ? '🔕 Уведомления отключены' : "🔕 Bildirishnomalar o'chirildi");
            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $this->showSettings($bot, $user->fresh());
            return;
        }

        // settings:topup
        if ($data === 'settings:topup') {
            $this->showTopUpOptions($bot, $user);
            return;
        }

        // settings:referral
        if ($data === 'settings:referral') {
            $this->showReferralInfo($bot, $user);
            return;
        }

        // settings:delete
        if ($data === 'settings:delete') {
            $this->showDeleteConfirmation($bot, $user);
            return;
        }

        // settings:delete:confirm
        if ($data === 'settings:delete:confirm') {
            $user->delete(); // SoftDeletes
            $lang = $user->language?->value ?? 'uz';
            $msg = $lang === 'ru'
                ? '🗑 Аккаунт удалён. Было приятно работать с вами!'
                : "🗑 Akkount o'chirildi. Siz bilan ishlash yoqimli edi!";
            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $bot->sendMessage(text: $msg);
            return;
        }

        // settings:back
        if ($data === 'settings:back') {
            $this->showSettings($bot, $user);
            return;
        }
    }

    public function handleTopup(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $amount = (int) str_replace('topup:', '', $data);
        $bot->answerCallbackQuery();

        $user = $this->getUser($bot);
        if (!$user) return;

        $lang = $user->language?->value ?? 'uz';

        $bot->answerCallbackQuery(
            text: $lang === 'ru'
                ? "Перенаправляем на оплату {$amount} сум..."
                : "{$amount} so'm to'lash sahifasiga yo'naltirilmoqda...",
            show_alert: true
        );

        $paymentUrl = config('app.url') . '/payment/create?amount=' . $amount;
        $bot->sendMessage(
            text: $lang === 'ru' ? "🔗 Оплата: {$paymentUrl}" : "🔗 To'lash: {$paymentUrl}",
        );
    }

    protected function showSettings(Nutgram $bot, User $user): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $languageLabel = $isRu ? '🇷🇺 Русский' : "🇺🇿 O'zbekcha";
        $notificationsLabel = $user->notifications_enabled
            ? ($isRu ? '🔔 Включены' : '🔔 Yoqilgan')
            : ($isRu ? '🔕 Выключены' : "🔕 O'chirilgan");

        $balance = number_format($user->balance, 0, ',', ' ');

        $text = $isRu
            ? "⚙️ *Настройки*\n\n"
                . "👤 Имя: {$user->first_name} {$user->last_name}\n"
                . "📞 Телефон: {$user->phone}\n"
                . "🌐 Язык: {$languageLabel}\n"
                . "🔔 Уведомления: {$notificationsLabel}\n"
                . "💰 Баланс: {$balance} сум\n\n"
                . "_Выберите настройку для изменения:_"
            : "⚙️ *Sozlamalar*\n\n"
                . "👤 Ism: {$user->first_name} {$user->last_name}\n"
                . "📞 Telefon: {$user->phone}\n"
                . "🌐 Til: {$languageLabel}\n"
                . "🔔 Bildirishnomalar: {$notificationsLabel}\n"
                . "💰 Balans: {$balance} so'm\n\n"
                . "_O'zgartirish uchun sozlamani tanlang:_";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    '🌐 ' . ($isRu ? 'Сменить язык' : "Tilni o'zgartirish"),
                    callback_data: 'settings:language'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $user->notifications_enabled
                        ? '🔕 ' . ($isRu ? 'Отключить уведомления' : "Bildirishnomalarni o'chirish")
                        : '🔔 ' . ($isRu ? 'Включить уведомления' : 'Bildirishnomalarni yoqish'),
                    callback_data: 'settings:toggle_notifications'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '💰 ' . ($isRu ? 'Пополнить баланс' : "Balansni to'ldirish"),
                    callback_data: 'settings:topup'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '🎁 ' . ($isRu ? 'Реферальная программа' : 'Referal dastur'),
                    callback_data: 'settings:referral'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '🗑 ' . ($isRu ? 'Удалить аккаунт' : "Akkauntni o'chirish"),
                    callback_data: 'settings:delete'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '◀️ ' . ($isRu ? 'Назад' : 'Orqaga'),
                    callback_data: 'menu:back'
                )
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function showLanguageSettings(Nutgram $bot, User $user): void
    {
        $lang = $user->language?->value ?? 'uz';

        $text = $lang === 'ru'
            ? "🌐 *Выбор языка*\n\nВыберите язык интерфейса:"
            : "🌐 *Til tanlash*\n\nInterfeys tilini tanlang:";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make("🇺🇿 O'zbekcha", callback_data: 'settings:lang:uz'),
                    InlineKeyboardButton::make('🇷🇺 Русский', callback_data: 'settings:lang:ru'),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'settings:back'
                    )
                ),
        );
    }

    protected function showTopUpOptions(Nutgram $bot, User $user): void
    {
        $lang = $user->language?->value ?? 'uz';

        $text = $lang === 'ru'
            ? "💰 *Пополнение баланса*\n\nВыберите сумму пополнения:"
            : "💰 *Balansni to'ldirish*\n\nTo'ldirish summasini tanlang:";

        $amounts = [10000, 25000, 50000, 100000, 250000, 500000];
        $keyboard = InlineKeyboardMarkup::make();

        $row = [];
        foreach ($amounts as $i => $amount) {
            $label = number_format($amount / 1000, 0) . 'K';
            $row[] = InlineKeyboardButton::make($label, callback_data: 'topup:' . $amount);

            if (count($row) === 3 || $i === count($amounts) - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                callback_data: 'settings:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function showReferralInfo(Nutgram $bot, User $user): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $referralCode = $user->referral_code;
        $botUsername = config('nutgram.bot_username', 'kadrgobot');
        $referralLink = "https://t.me/{$botUsername}?start=ref_{$referralCode}";

        $referralsCount = User::where('referred_by', $user->id)->count();
        $referralBonus = $referralsCount * 5000;

        $text = $isRu
            ? "🎁 *Реферальная программа*\n\n"
                . "Приглашайте друзей и получайте бонусы!\n\n"
                . "📊 *Ваша статистика:*\n"
                . "👥 Приглашено: {$referralsCount}\n"
                . "💰 Заработано: " . number_format($referralBonus, 0, ',', ' ') . " сум\n\n"
                . "🔗 *Ваша реферальная ссылка:*\n"
                . "`{$referralLink}`\n\n"
                . "_Нажмите на ссылку, чтобы скопировать_"
            : "🎁 *Referal dastur*\n\n"
                . "Do'stlaringizni taklif qiling va bonuslar oling!\n\n"
                . "📊 *Sizning statistikangiz:*\n"
                . "👥 Taklif qilingan: {$referralsCount}\n"
                . "💰 Ishlangan: " . number_format($referralBonus, 0, ',', ' ') . " so'm\n\n"
                . "🔗 *Sizning referal havolangiz:*\n"
                . "`{$referralLink}`\n\n"
                . "_Nusxa olish uchun havolaga bosing_";

        $shareText = $isRu ? 'Присоединяйся к KadrGo!' : "KadrGo ga qo'shil!";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        '🔗 ' . ($isRu ? 'Поделиться' : 'Ulashish'),
                        url: "https://t.me/share/url?url=" . urlencode($referralLink) . "&text=" . urlencode($shareText)
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        '◀️ ' . ($isRu ? 'Назад' : 'Orqaga'),
                        callback_data: 'settings:back'
                    )
                ),
        );
    }

    protected function showDeleteConfirmation(Nutgram $bot, User $user): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $text = $isRu
            ? "⚠️ *Удаление аккаунта*\n\nВы уверены, что хотите удалить аккаунт?\n\n❗️ Это действие необратимо!\nВсе ваши данные, резюме и заявки будут удалены."
            : "⚠️ *Akkauntni o'chirish*\n\nAkkauntni o'chirishni xohlaysizmi?\n\n❗️ Bu amalni bekor qilib bo'lmaydi!\nBarcha ma'lumotlaringiz, rezume va arizalaringiz o'chiriladi.";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        '🗑 ' . ($isRu ? 'Да, удалить' : "Ha, o'chirish"),
                        callback_data: 'settings:delete:confirm'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        '◀️ ' . ($isRu ? 'Отмена' : 'Bekor qilish'),
                        callback_data: 'settings:back'
                    )
                ),
        );
    }

    private function getUser(Nutgram $bot): ?User
    {
        return User::where('telegram_id', $bot->user()->id)->first();
    }
}
