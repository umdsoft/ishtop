<?php

namespace App\Telegram\Handlers;

use App\Models\SavedItem;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SavedHandler
{
    public function __invoke(Nutgram $bot): void
    {
        try {
            $user = $this->getUser($bot);
            if (!$user) {
                $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
                return;
            }

            $this->showSavedList($bot, $user);
        } catch (\Throwable $e) {
            Log::error('SavedHandler error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. /menu");
        }
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';

        try {
            $user = $this->getUser($bot);
            if (!$user) {
                $bot->answerCallbackQuery();
                $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
                return;
            }

            // saved:toggle:{vacancyId} — answers with show_alert inside
            if (str_starts_with($data, 'saved:toggle:')) {
                $vacancyId = str_replace('saved:toggle:', '', $data);
                $this->toggleSaved($bot, $user, $vacancyId);
                return;
            }

            $bot->answerCallbackQuery();

            if ($data === 'saved:list') {
                $this->showSavedList($bot, $user, true);
                return;
            }

            // saved:remove:{savedItemId}
            if (str_starts_with($data, 'saved:remove:')) {
                $savedItemId = str_replace('saved:remove:', '', $data);
                $this->removeSaved($bot, $user, $savedItemId);
                return;
            }

            // saved:view:{vacancyId}
            if (str_starts_with($data, 'saved:view:')) {
                $vacancyId = str_replace('saved:view:', '', $data);
                $this->showSavedVacancyDetail($bot, $user, $vacancyId);
                return;
            }
        } catch (\Throwable $e) {
            Log::error('SavedHandler callback error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. /menu");
        }
    }

    protected function showSavedList(Nutgram $bot, User $user, bool $edit = false): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $savedItems = SavedItem::where('user_id', $user->id)
            ->where('saveable_type', Vacancy::class)
            ->with('saveable.employer')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->filter(fn($item) => $item->saveable !== null);

        if ($savedItems->isEmpty()) {
            $text = $isRu
                ? "🤍 *Сохранённые вакансии*\n\nУ вас пока нет сохранённых вакансий.\n\n🔍 Найдите интересную вакансию и сохраните её!"
                : "🤍 *Saqlangan vakansiyalar*\n\nSizda hali saqlangan vakansiyalar yo'q.\n\n🔍 Qiziq vakansiya toping va saqlang!";

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $isRu ? '🔍 Искать работу' : '🔍 Ish qidirish',
                    callback_data: 'menu:search'
                ))
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

        $text = $isRu
            ? "🤍 *Сохранённые вакансии* ({$savedItems->count()})\n\n"
            : "🤍 *Saqlangan vakansiyalar* ({$savedItems->count()})\n\n";

        foreach ($savedItems->take(8) as $i => $item) {
            $vacancy = $item->saveable;
            $salary = $vacancy->salaryFormatted();
            $text .= ($i + 1) . ". *{$this->escapeMarkdown($vacancy->title())}*\n";
            $text .= "   🏢 {$this->escapeMarkdown($vacancy->employer?->company_name ?? '-')} | 💰 {$salary}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($savedItems->take(8) as $item) {
            $vacancy = $item->saveable;
            $title = mb_substr($vacancy->title(), 0, 28);
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "👁 {$title}",
                    callback_data: 'saved:view:' . $vacancy->id
                ),
                InlineKeyboardButton::make(
                    '🗑',
                    callback_data: 'saved:remove:' . $item->id
                ),
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

    public function toggleSaved(Nutgram $bot, User $user, string $vacancyId): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $vacancy = Vacancy::find($vacancyId);
        if (!$vacancy) {
            return;
        }

        $existing = SavedItem::where('user_id', $user->id)
            ->where('saveable_type', Vacancy::class)
            ->where('saveable_id', $vacancyId)
            ->first();

        if ($existing) {
            $existing->delete();
            $msg = $isRu ? '🤍 Удалено из сохранённых' : '🤍 Saqlanganlardan olib tashlandi';
        } else {
            SavedItem::create([
                'user_id' => $user->id,
                'saveable_type' => Vacancy::class,
                'saveable_id' => $vacancyId,
            ]);
            $msg = $isRu ? '❤️ Сохранено' : '❤️ Saqlandi';
        }

        $bot->answerCallbackQuery(text: $msg, show_alert: true);
    }

    protected function removeSaved(Nutgram $bot, User $user, string $savedItemId): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';

        $item = SavedItem::where('id', $savedItemId)
            ->where('user_id', $user->id)
            ->first();

        if ($item) {
            $item->delete();
        }

        $this->showSavedList($bot, $user, true);
    }

    protected function showSavedVacancyDetail(Nutgram $bot, User $user, string $vacancyId): void
    {
        $lang = $user->language?->value ?? 'uz';
        $isRu = $lang === 'ru';
        $vacancy = Vacancy::with('employer')->find($vacancyId);

        if (!$vacancy) {
            $bot->editMessageText(
                text: $isRu ? '❌ Вакансия не найдена.' : '❌ Vakansiya topilmadi.',
                message_id: $bot->callbackQuery()->message->message_id,
            );
            return;
        }

        $salary = $vacancy->salaryFormatted();
        $workType = $vacancy->work_type?->label() ?? '-';
        $company = $this->escapeMarkdown($vacancy->employer?->company_name ?? '-');

        $text = "📌 *{$this->escapeMarkdown($vacancy->title())}*\n\n";
        $text .= "🏢 " . ($isRu ? 'Компания' : 'Kompaniya') . ": {$company}\n";
        $text .= "📍 " . ($isRu ? 'Город' : 'Shahar') . ": {$vacancy->city}\n";
        $text .= "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n";
        $text .= "🏢 " . ($isRu ? 'Тип работы' : 'Ish turi') . ": {$workType}\n\n";
        $text .= "📝 *" . ($isRu ? 'Описание' : 'Tavsif') . ":*\n{$vacancy->description($lang)}\n";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📝 Подать заявку' : '📝 Ariza berish',
                    callback_data: "vacancy_apply:{$vacancy->id}"
                ),
                InlineKeyboardButton::make(
                    $isRu ? '🤍 Убрать' : '🤍 Olib tashlash',
                    callback_data: "saved:toggle:{$vacancy->id}"
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '◀️ К сохранённым' : '◀️ Saqlanganlarga',
                    callback_data: 'saved:list'
                ),
            );

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    private function getUser(Nutgram $bot): ?User
    {
        return User::where('telegram_id', $bot->user()->id)->first();
    }

    private function escapeMarkdown(string $text): string
    {
        return str_replace(['_', '*', '`', '['], ['\\_', '\\*', '\\`', '\\['], $text);
    }
}
