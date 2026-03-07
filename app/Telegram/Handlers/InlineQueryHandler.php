<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Models\Vacancy;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Inline\InlineQueryResultArticle;
use SergiX44\Nutgram\Telegram\Types\Input\InputTextMessageContent;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class InlineQueryHandler
{
    protected int $perPage = 20;

    public function __invoke(Nutgram $bot): void
    {
        $query = trim($bot->inlineQuery()->query ?? '');
        $offset = (int) ($bot->inlineQuery()->offset ?? 0);
        $lang = $this->getUserLang($bot);

        $dbQuery = Vacancy::active()
            ->with('employer')
            ->latest('published_at');

        if ($query !== '') {
            $dbQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('title_uz', 'like', "%{$query}%")
                    ->orWhere('title_ru', 'like', "%{$query}%")
                    ->orWhere('description_uz', 'like', "%{$query}%")
                    ->orWhere('description_ru', 'like', "%{$query}%")
                    ->orWhere('category', 'like', "%{$query}%")
                    ->orWhere('city', 'like', "%{$query}%");
            });
        }

        $vacancies = $dbQuery
            ->skip($offset)
            ->take($this->perPage)
            ->get();

        $results = [];
        $botUsername = config('nutgram.bot_username');

        foreach ($vacancies as $vacancy) {
            $title = $vacancy->title($lang);
            $top = $vacancy->isTopActive() ? '🔥 ' : '';
            $salary = $vacancy->salaryFormatted();
            $company = $vacancy->employer?->company_name ?? '-';
            $city = $vacancy->city ?? '-';

            $description = "📍 {$city} | 💰 {$salary} | 🏢 {$company}";

            $isRu = $lang === 'ru';
            $messageText = "{$top}📌 *{$title}*\n\n"
                . "🏢 " . ($isRu ? 'Компания' : 'Kompaniya') . ": {$company}\n"
                . "📍 " . ($isRu ? 'Город' : 'Shahar') . ": {$city}\n"
                . "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n";

            if ($vacancy->work_type) {
                $messageText .= "🏢 " . ($isRu ? 'Тип' : 'Turi') . ": " . ($vacancy->work_type->label() ?? '-') . "\n";
            }

            $desc = $vacancy->description($lang) ?: $vacancy->description_uz;
            if ($desc) {
                $messageText .= "\n📝 " . mb_substr($desc, 0, 300);
                if (mb_strlen($desc) > 300) {
                    $messageText .= '...';
                }
            }

            $messageText .= "\n\n" . ($isRu ? '👉 Подробнее в боте' : "👉 Batafsil botda");

            $keyboard = InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $isRu ? '📝 Подать заявку' : '📝 Ariza berish',
                        url: "https://t.me/{$botUsername}?start=v_{$vacancy->id}"
                    )
                );

            $results[] = InlineQueryResultArticle::make(
                id: $vacancy->id,
                title: $top . $title,
                input_message_content: InputTextMessageContent::make(
                    message_text: $messageText,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                ),
                reply_markup: $keyboard,
                description: $description,
            );
        }

        $nextOffset = $vacancies->count() === $this->perPage
            ? (string) ($offset + $this->perPage)
            : '';

        $bot->answerInlineQuery(
            results: $results,
            cache_time: 60,
            is_personal: true,
            next_offset: $nextOffset,
        );
    }

    private function getUserLang(Nutgram $bot): string
    {
        $user = User::where('telegram_id', $bot->inlineQuery()->from->id)->first();
        return $user?->language?->value ?? 'uz';
    }
}
