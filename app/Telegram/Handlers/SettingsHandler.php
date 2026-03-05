<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * SettingsHandler
 * Handles /settings command - user settings and preferences
 */
class SettingsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $lang = $user->language?->value ?? 'uz';

        $this->showSettings($bot, $user, $lang);
    }

    protected function showSettings(Nutgram $bot, User $user, string $lang): void
    {
        $languageLabel = $lang === 'ru' ? '🇷🇺 Русский' : '🇺🇿 O\'zbekcha';
        $notificationsLabel = $user->notifications_enabled
            ? ($lang === 'ru' ? '🔔 Включены' : '🔔 Yoqilgan')
            : ($lang === 'ru' ? '🔕 Выключены' : '🔕 O\'chirilgan');

        $balance = number_format($user->balance, 0, ',', ' ');

        if ($lang === 'ru') {
            $text = "⚙️ *Настройки*\n\n"
                . "👤 Имя: {$user->first_name} {$user->last_name}\n"
                . "📞 Телефон: {$user->phone}\n"
                . "🌐 Язык: {$languageLabel}\n"
                . "🔔 Уведомления: {$notificationsLabel}\n"
                . "💰 Баланс: {$balance} сум\n\n"
                . "_Выберите настройку для изменения:_";
        } else {
            $text = "⚙️ *Sozlamalar*\n\n"
                . "👤 Ism: {$user->first_name} {$user->last_name}\n"
                . "📞 Telefon: {$user->phone}\n"
                . "🌐 Til: {$languageLabel}\n"
                . "🔔 Bildirishnomalar: {$notificationsLabel}\n"
                . "💰 Balans: {$balance} so'm\n\n"
                . "_O'zgartirish uchun sozlamani tanlang:_";
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    '🌐 ' . ($lang === 'ru' ? 'Сменить язык' : 'Tilni o\'zgartirish'),
                    callback_data: 'settings:language'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $user->notifications_enabled
                        ? '🔕 ' . ($lang === 'ru' ? 'Отключить уведомления' : 'Bildirishnomalarni o\'chirish')
                        : '🔔 ' . ($lang === 'ru' ? 'Включить уведомления' : 'Bildirishnomalarni yoqish'),
                    callback_data: 'settings:toggle_notifications'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '💰 ' . ($lang === 'ru' ? 'Пополнить баланс' : 'Balansni to\'ldirish'),
                    callback_data: 'settings:topup'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '🎁 ' . ($lang === 'ru' ? 'Реферальная программа' : 'Referal dastur'),
                    callback_data: 'settings:referral'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '🗑 ' . ($lang === 'ru' ? 'Удалить аккаунт' : 'Akkauntni o\'chirish'),
                    callback_data: 'settings:delete'
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                    callback_data: 'menu:back'
                )
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );

        // Handle callbacks
        $bot->onCallbackQueryData('settings:language', function (Nutgram $bot) use ($user, $lang) {
            $bot->answerCallbackQuery();
            $this->showLanguageSettings($bot, $user, $lang);
        });

        $bot->onCallbackQueryData('settings:toggle_notifications', function (Nutgram $bot) use ($user, $lang) {
            $newValue = !$user->notifications_enabled;
            $user->update(['notifications_enabled' => $newValue]);

            $msg = $newValue
                ? ($lang === 'ru' ? '🔔 Уведомления включены' : '🔔 Bildirishnomalar yoqildi')
                : ($lang === 'ru' ? '🔕 Уведомления отключены' : '🔕 Bildirishnomalar o\'chirildi');

            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $this->showSettings($bot, $user->fresh(), $lang);
        });

        $bot->onCallbackQueryData('settings:topup', function (Nutgram $bot) use ($lang) {
            $bot->answerCallbackQuery();
            $this->showTopUpOptions($bot, $lang);
        });

        $bot->onCallbackQueryData('settings:referral', function (Nutgram $bot) use ($user, $lang) {
            $bot->answerCallbackQuery();
            $this->showReferralInfo($bot, $user, $lang);
        });

        $bot->onCallbackQueryData('settings:delete', function (Nutgram $bot) use ($user, $lang) {
            $bot->answerCallbackQuery();
            $this->showDeleteConfirmation($bot, $user, $lang);
        });
    }

    protected function showLanguageSettings(Nutgram $bot, User $user, string $lang): void
    {
        $text = $lang === 'ru'
            ? "🌐 *Выбор языка*\n\nВыберите язык интерфейса:"
            : "🌐 *Til tanlash*\n\nInterfeys tilini tanlang:";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('🇺🇿 O\'zbekcha', callback_data: 'settings:lang:uz'),
                    InlineKeyboardButton::make('🇷🇺 Русский', callback_data: 'settings:lang:ru'),
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'settings:back'
                    )
                ),
        );

        $bot->onCallbackQueryData('settings:lang:uz', function (Nutgram $bot) use ($user) {
            $user->update(['language' => 'uz']);
            $bot->answerCallbackQuery(text: '✅ Til o\'zgartirildi', show_alert: true);
            $this->showSettings($bot, $user->fresh(), 'uz');
        });

        $bot->onCallbackQueryData('settings:lang:ru', function (Nutgram $bot) use ($user) {
            $user->update(['language' => 'ru']);
            $bot->answerCallbackQuery(text: '✅ Язык изменён', show_alert: true);
            $this->showSettings($bot, $user->fresh(), 'ru');
        });

        $bot->onCallbackQueryData('settings:back', function (Nutgram $bot) use ($user, $lang) {
            $bot->answerCallbackQuery();
            $this->showSettings($bot, $user, $lang);
        });
    }

    protected function showTopUpOptions(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "💰 *Пополнение баланса*\n\nВыберите сумму пополнения:"
            : "💰 *Balansni to'ldirish*\n\nTo'ldirish summasi tanlang:";

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
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );

        foreach ($amounts as $amount) {
            $bot->onCallbackQueryData('topup:' . $amount, function (Nutgram $bot) use ($amount, $lang) {
                $bot->answerCallbackQuery(
                    text: $lang === 'ru'
                        ? "Перенаправляем на оплату {$amount} сум..."
                        : "{$amount} so'm to'lash sahifasiga yo'naltirilmoqda...",
                    show_alert: true
                );

                // In real implementation, create payment and redirect to payment gateway
                $paymentUrl = env('APP_URL') . '/payment/create?amount=' . $amount;
                $bot->sendMessage(
                    text: $lang === 'ru' ? "🔗 Оплата: {$paymentUrl}" : "🔗 To'lash: {$paymentUrl}",
                );
            });
        }
    }

    protected function showReferralInfo(Nutgram $bot, User $user, string $lang): void
    {
        $referralCode = $user->referral_code;
        $botUsername = env('TELEGRAM_BOT_USERNAME', 'IshTopBot');
        $referralLink = "https://t.me/{$botUsername}?start={$referralCode}";

        $referralsCount = User::where('referred_by', $user->id)->count();
        $referralBonus = $referralsCount * 5000; // 5000 per referral

        if ($lang === 'ru') {
            $text = "🎁 *Реферальная программа*\n\n"
                . "Приглашайте друзей и получайте бонусы!\n\n"
                . "📊 *Ваша статистика:*\n"
                . "👥 Приглашено: {$referralsCount}\n"
                . "💰 Заработано: " . number_format($referralBonus, 0, ',', ' ') . " сум\n\n"
                . "🔗 *Ваша реферальная ссылка:*\n"
                . "`{$referralLink}`\n\n"
                . "_Нажмите на ссылку, чтобы скопировать_";
        } else {
            $text = "🎁 *Referal dastur*\n\n"
                . "Do'stlaringizni taklif qiling va bonuslar oling!\n\n"
                . "📊 *Sizning statistikangiz:*\n"
                . "👥 Taklif qilingan: {$referralsCount}\n"
                . "💰 Ishlagan: " . number_format($referralBonus, 0, ',', ' ') . " so'm\n\n"
                . "🔗 *Sizning referal havolangiz:*\n"
                . "`{$referralLink}`\n\n"
                . "_Nusxa olish uchun havolaga bosing_";
        }

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        '🔗 ' . ($lang === 'ru' ? 'Поделиться' : 'Ulashish'),
                        url: "https://t.me/share/url?url={$referralLink}&text=" . urlencode($lang === 'ru' ? 'Присоединяйся к IshTop!' : 'IshTop ga qo\'shil!')
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                        callback_data: 'settings:back'
                    )
                ),
        );
    }

    protected function showDeleteConfirmation(Nutgram $bot, User $user, string $lang): void
    {
        $text = $lang === 'ru'
            ? "⚠️ *Удаление аккаунта*\n\nВы уверены, что хотите удалить аккаунт?\n\n❗️ Это действие необратимо!\nВсе ваши данные, резюме и заявки будут удалены."
            : "⚠️ *Akkauntni o'chirish*\n\nAkkauntni o'chirishni xohlaysizmi?\n\n❗️ Bu amalni bekor qilib bo'lmaydi!\nBarcha ma'lumotlaringiz, rezume va arizalaringiz o'chiriladi.";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        '🗑 ' . ($lang === 'ru' ? 'Да, удалить' : 'Ha, o\'chirish'),
                        callback_data: 'settings:delete:confirm'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        '◀️ ' . ($lang === 'ru' ? 'Отмена' : 'Bekor qilish'),
                        callback_data: 'settings:back'
                    )
                ),
        );

        $bot->onCallbackQueryData('settings:delete:confirm', function (Nutgram $bot) use ($user, $lang) {
            // Soft delete or mark as deleted
            $user->update([
                'is_active' => false,
                'deleted_at' => now(),
            ]);

            $msg = $lang === 'ru'
                ? '🗑 Аккаунт удалён. Было приятно работать с вами!'
                : '🗑 Akkount o\'chirildi. Siz bilan ishlash yoqimli edi!';

            $bot->answerCallbackQuery(text: $msg, show_alert: true);
            $bot->sendMessage(text: $msg);
        });

        $bot->onCallbackQueryData('settings:back', function (Nutgram $bot) use ($user, $lang) {
            $bot->answerCallbackQuery();
            $this->showSettings($bot, $user, $lang);
        });
    }
}
