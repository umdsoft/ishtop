<?php

namespace App\Telegram\Handlers;

use App\Models\Application;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Models\Vacancy;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SearchHandler
{
    protected int $perPage = 5;

    public function __invoke(Nutgram $bot): void
    {
        $this->showMainSearch($bot);
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        if (str_starts_with($data, 'search_cat:')) {
            $this->searchByCategory($bot, str_replace('search_cat:', '', $data));
        } elseif (str_starts_with($data, 'search_city:')) {
            $this->searchByCity($bot, str_replace('search_city:', '', $data));
        } elseif (str_starts_with($data, 'search_page:')) {
            $parts = explode(':', $data);
            $page = (int) ($parts[1] ?? 1);
            $filter = $parts[2] ?? 'all';
            $value = $parts[3] ?? '';
            $this->showVacancies($bot, $page, $filter, $value);
        } elseif ($data === 'search:main') {
            $this->showMainSearch($bot, true);
        } elseif ($data === 'search:categories') {
            $this->showCategories($bot);
        } elseif ($data === 'search:cities') {
            $this->showCities($bot);
        } elseif ($data === 'search:all') {
            $this->showVacancies($bot, 1, 'all', '');
        } elseif (str_starts_with($data, 'vacancy_view:')) {
            $this->showVacancyDetail($bot, str_replace('vacancy_view:', '', $data));
        } elseif (str_starts_with($data, 'vacancy_apply:')) {
            $this->applyToVacancy($bot, str_replace('vacancy_apply:', '', $data));
        }
    }

    protected function showMainSearch(Nutgram $bot, bool $edit = false): void
    {
        $activeCount = Vacancy::active()->count();

        $text = "🔍 *Ish qidirish*\n\n📊 Faol vakansiyalar: {$activeCount}\n\nQuyidagi usullardan birini tanlang:";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📂 Kategoriya bo\'yicha', callback_data: 'search:categories'),
                InlineKeyboardButton::make('📍 Shahar bo\'yicha', callback_data: 'search:cities'),
            )
            ->addRow(
                InlineKeyboardButton::make('📋 Barcha vakansiyalar', callback_data: 'search:all'),
            )
            ->addRow(
                InlineKeyboardButton::make('🌐 Mini App da qidirish', url: 'https://t.me/IshTopBot/app'),
            )
            ->addRow(
                InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'menu:back'),
            );

        if ($edit) {
            $bot->editMessageText(
                text: $text,
                message_id: $bot->callbackQuery()->message->message_id,
                parse_mode: ParseMode::MARKDOWN,
                reply_markup: $keyboard,
            );
        } else {
            $bot->sendMessage(
                text: $text,
                parse_mode: ParseMode::MARKDOWN,
                reply_markup: $keyboard,
            );
        }
    }

    protected function showCategories(Nutgram $bot): void
    {
        $categories = Category::active()->get();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($categories as $i => $cat) {
            $label = ($cat->icon ? $cat->icon . ' ' : '') . $cat->name_uz;
            $row[] = InlineKeyboardButton::make($label, callback_data: 'search_cat:' . $cat->slug);
            if (count($row) === 2 || $i === $categories->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        $keyboard->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'search:main'));

        $bot->editMessageText(
            text: "📂 *Kategoriyani tanlang:*",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );
    }

    protected function showCities(Nutgram $bot): void
    {
        $cities = City::active()->get();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($cities as $i => $city) {
            $count = Vacancy::active()->where('city', $city->name_uz)->count();
            $row[] = InlineKeyboardButton::make(
                $city->name_uz . " ({$count})",
                callback_data: 'search_city:' . $city->name_uz
            );
            if (count($row) === 2 || $i === $cities->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        $keyboard->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'search:main'));

        $bot->editMessageText(
            text: "📍 *Shaharni tanlang:*",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );
    }

    protected function searchByCategory(Nutgram $bot, string $slug): void
    {
        $this->showVacancies($bot, 1, 'category', $slug);
    }

    protected function searchByCity(Nutgram $bot, string $city): void
    {
        $this->showVacancies($bot, 1, 'city', $city);
    }

    protected function showVacancies(Nutgram $bot, int $page, string $filter, string $value): void
    {
        $query = Vacancy::active()->with('employer')->latest('published_at');

        if ($filter === 'category') {
            $category = Category::where('slug', $value)->first();
            if ($category) {
                $query->where('category', $category->name_uz);
            }
        } elseif ($filter === 'city') {
            $query->where('city', $value);
        }

        $total = $query->count();
        $totalPages = max(1, (int) ceil($total / $this->perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $this->perPage;

        $vacancies = $query->skip($offset)->take($this->perPage)->get();

        if ($vacancies->isEmpty()) {
            $bot->editMessageText(
                text: "😔 Vakansiyalar topilmadi.\n\n📌 Boshqa filtrni tanlang:",
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'search:main')),
            );
            return;
        }

        $text = "📋 *Vakansiyalar* ({$total} ta)\nSahifa: {$page}/{$totalPages}\n\n";

        foreach ($vacancies as $i => $v) {
            $num = $offset + $i + 1;
            $salary = $v->salaryFormatted();
            $top = $v->isTopActive() ? '🔥 ' : '';
            $company = $v->employer?->company_name ?? '-';
            $text .= "{$top}*{$num}. {$v->title}*\n📍 {$v->city} | 💰 {$salary}\n🏢 {$company}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();
        foreach ($vacancies as $v) {
            $keyboard->addRow(
                InlineKeyboardButton::make("👁 {$v->title}", callback_data: "vacancy_view:{$v->id}")
            );
        }

        $navRow = [];
        if ($page > 1) {
            $navRow[] = InlineKeyboardButton::make('◀️', callback_data: "search_page:" . ($page - 1) . ":{$filter}:{$value}");
        }
        $navRow[] = InlineKeyboardButton::make("{$page}/{$totalPages}", callback_data: 'noop');
        if ($page < $totalPages) {
            $navRow[] = InlineKeyboardButton::make('▶️', callback_data: "search_page:" . ($page + 1) . ":{$filter}:{$value}");
        }
        if (count($navRow) > 0) {
            $keyboard->addRow(...$navRow);
        }

        $keyboard->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'search:main'));

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );
    }

    protected function showVacancyDetail(Nutgram $bot, string $vacancyId): void
    {
        $vacancy = Vacancy::with('employer')->find($vacancyId);

        if (!$vacancy) {
            $bot->editMessageText(
                text: '❌ Vakansiya topilmadi.',
                message_id: $bot->callbackQuery()->message->message_id,
            );
            return;
        }

        $vacancy->increment('views_count');

        $salary = $vacancy->salaryFormatted();
        $workType = $vacancy->work_type?->label() ?? '-';
        $company = $vacancy->employer?->company_name ?? '-';
        $top = $vacancy->isTopActive() ? '🔥 TOP ' : '';

        $text = "{$top}📌 *{$vacancy->title}*\n\n";
        $text .= "🏢 Kompaniya: {$company}\n";
        $text .= "📂 Kategoriya: {$vacancy->category}\n";
        $text .= "📍 Shahar: {$vacancy->city}\n";
        $text .= "💰 Maosh: {$salary}\n";
        $text .= "🏢 Ish turi: {$workType}\n";

        if ($vacancy->experience_required) {
            $text .= "⏱ Tajriba: {$vacancy->experience_required}\n";
        }

        $text .= "\n📝 *Tavsif:*\n{$vacancy->description}\n";

        if ($vacancy->requirements) {
            $text .= "\n📋 *Talablar:*\n{$vacancy->requirements}\n";
        }
        if ($vacancy->responsibilities) {
            $text .= "\n✅ *Vazifalar:*\n{$vacancy->responsibilities}\n";
        }

        $text .= "\n👁 Ko\'rishlar: {$vacancy->views_count} | 📝 Arizalar: {$vacancy->applications_count}";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📝 Ariza berish', callback_data: "vacancy_apply:{$vacancy->id}"),
            )
            ->addRow(
                InlineKeyboardButton::make('◀️ Orqaga', callback_data: 'search:main'),
            );

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: $keyboard,
        );
    }

    protected function applyToVacancy(Nutgram $bot, string $vacancyId): void
    {
        $vacancy = Vacancy::find($vacancyId);
        if (!$vacancy || !$vacancy->isActive()) {
            $bot->editMessageText(
                text: '❌ Vakansiya topilmadi yoki faol emas.',
                message_id: $bot->callbackQuery()->message->message_id,
            );
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();
        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $worker = $user->workerProfile;
        if (!$worker) {
            $bot->editMessageText(
                text: "📝 Ariza berish uchun avval rezume yarating.\n\n/resume buyrug\'ini yuboring.",
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('📝 Rezume yaratish', callback_data: 'resume:create'))
                    ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: "vacancy_view:{$vacancyId}")),
            );
            return;
        }

        $existing = Application::where('vacancy_id', $vacancyId)
            ->where('worker_id', $worker->id)
            ->first();

        if ($existing) {
            $bot->editMessageText(
                text: "ℹ️ Siz bu vakansiyaga allaqachon ariza bergansiz.",
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make('◀️ Orqaga', callback_data: "vacancy_view:{$vacancyId}")),
            );
            return;
        }

        Application::create([
            'vacancy_id' => $vacancyId,
            'worker_id' => $worker->id,
            'stage' => 'new',
            'source' => 'telegram',
        ]);

        $vacancy->increment('applications_count');

        $bot->editMessageText(
            text: "✅ *Ariza yuborildi!*\n\n📌 {$vacancy->title}\n\nIsh beruvchi sizning arizangizni ko\'rib chiqadi.",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make('🔍 Qidiruvga qaytish', callback_data: 'search:main')),
        );
    }
}
