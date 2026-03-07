<?php

namespace App\Telegram\Handlers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class NotificationsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        try {
            $user = $this->getUser($bot);
            if (!$user) {
                $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
                return;
            }

            $this->showNotifications($bot, $user);
        } catch (\Throwable $e) {
            Log::error('NotificationsHandler error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. /menu");
        }
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        try {
            $user = $this->getUser($bot);
            if (!$user) {
                $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
                return;
            }

            if ($data === 'notif:list') {
                $this->showNotifications($bot, $user, true);
                return;
            }

            if ($data === 'notif:read_all') {
                Notification::where('user_id', $user->id)
                    ->unread()
                    ->update(['is_read' => true]);

                $lang = $user->language?->value ?? 'uz';
                $msg = $lang === 'ru' ? '✅ Все прочитаны' : '✅ Hammasi o\'qildi';
                $bot->answerCallbackQuery(text: $msg, show_alert: true);
                $this->showNotifications($bot, $user, true);
                return;
            }

            // notif:view:{id}
            if (str_starts_with($data, 'notif:view:')) {
                $notifId = str_replace('notif:view:', '', $data);
                $this->showNotificationDetail($bot, $user, $notifId);
                return;
            }
        } catch (\Throwable $e) {
            Log::error('NotificationsHandler callback error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. /menu");
        }
    }

    protected function showNotifications(Nutgram $bot, User $user, bool $edit = false): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)->unread()->count();

        if ($notifications->isEmpty()) {
            $text = $isRu
                ? "🔔 *Уведомления*\n\nУ вас пока нет уведомлений."
                : "🔔 *Bildirishnomalar*\n\nSizda hali bildirishnoma yo'q.";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $isRu ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'menu:back'
                ));

            if ($edit) {
                $bot->editMessageText(
                    text: $text,
                    message_id: $bot->callbackQuery()->message->message_id,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                    reply_markup: $keyboard,
                );
            } else {
                $bot->sendMessage(
                    text: $text,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                    reply_markup: $keyboard,
                );
            }
            return;
        }

        $unreadLabel = $unreadCount > 0
            ? ($isRu ? " | {$unreadCount} новых" : " | {$unreadCount} ta yangi")
            : '';

        $text = $isRu
            ? "🔔 *Уведомления* ({$notifications->count()}{$unreadLabel})\n\n"
            : "🔔 *Bildirishnomalar* ({$notifications->count()}{$unreadLabel})\n\n";

        foreach ($notifications->take(10) as $i => $notif) {
            $icon = $this->getNotifIcon($notif->type);
            $unread = !$notif->is_read ? '🔵 ' : '';
            $title = mb_substr($notif->title ?? '', 0, 40);
            $time = $notif->created_at?->diffForHumans() ?? '';
            $text .= "{$unread}{$icon} *{$title}*\n   {$time}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($notifications->take(8) as $notif) {
            $icon = $this->getNotifIcon($notif->type);
            $unread = !$notif->is_read ? '🔵 ' : '';
            $title = mb_substr($notif->title ?? '', 0, 25);
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$unread}{$icon} {$title}",
                    callback_data: 'notif:view:' . $notif->id
                )
            );
        }

        if ($unreadCount > 0) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '✅ Прочитать все' : '✅ Hammasini o\'qish',
                    callback_data: 'notif:read_all'
                )
            );
        }

        $keyboard->addRow(InlineKeyboardButton::make(
            $isRu ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'menu:back'
        ));

        if ($edit) {
            $bot->editMessageText(
                text: $text,
                message_id: $bot->callbackQuery()->message->message_id,
                parse_mode: ParseMode::MARKDOWN_LEGACY,
                reply_markup: $keyboard,
            );
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: ParseMode::MARKDOWN_LEGACY,
                reply_markup: $keyboard,
            );
        }
    }

    protected function showNotificationDetail(Nutgram $bot, User $user, string $notifId): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $notif = Notification::where('id', $notifId)
            ->where('user_id', $user->id)
            ->first();

        if (!$notif) {
            return;
        }

        // Mark as read
        if (!$notif->is_read) {
            $notif->markAsRead();
        }

        $icon = $this->getNotifIcon($notif->type);
        $time = $notif->created_at?->format('d.m.Y H:i') ?? '';

        $text = "{$icon} *{$notif->title}*\n\n";
        $text .= "{$notif->message}\n\n";
        $text .= "🕐 {$time}";

        $keyboard = InlineKeyboardMarkup::make();

        $keyboard->addRow(InlineKeyboardButton::make(
            $isRu ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'notif:list'
        ));

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    private function getNotifIcon(string $type): string
    {
        return match ($type) {
            'application_stage' => '📋',
            'new_application' => '📝',
            'application_withdrawn' => '🚫',
            'vacancy_moderated' => '✅',
            'matching_vacancy' => '🎯',
            'payment' => '💳',
            default => '🔔',
        };
    }

    private function getUser(Nutgram $bot): ?User
    {
        return User::where('telegram_id', $bot->user()->id)->first();
    }
}
