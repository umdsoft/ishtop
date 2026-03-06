<?php

namespace App\Telegram\Handlers;

use App\Enums\ApplicationStage;
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
        $lang = $this->getUserLang($bot);
        $activeCount = Vacancy::active()->count();

        $text = $lang === 'ru'
            ? "🔍 *Поиск работы*\n\n📊 Активные вакансии: {$activeCount}\n\nВыберите один из способов:"
            : "🔍 *Ish qidirish*\n\n📊 Faol vakansiyalar: {$activeCount}\n\nQuyidagi usullardan birini tanlang:";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '📂 По категории' : 'Kategoriya bo\'yicha',
                    callback_data: 'search:categories'
                ),
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '📍 По городу' : '📍 Shahar bo\'yicha',
                    callback_data: 'search:cities'
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '📋 Все вакансии' : '📋 Barcha vakansiyalar',
                    callback_data: 'search:all'
                ),
            );

        $miniAppUrl = config('app.url') . '/miniapp';
        if (!str_contains($miniAppUrl, 'localhost')) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '🌐 Искать в Mini App' : '🌐 Mini App da qidirish',
                    url: $miniAppUrl
                ),
            );
        }

        $keyboard->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'menu:back'
                ),
            );

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

    protected function showCategories(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);
        $categories = Category::active()->get();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($categories as $i => $cat) {
            $name = $lang === 'ru' ? ($cat->name_ru ?? $cat->name_uz) : $cat->name_uz;
            $label = ($cat->icon ? $cat->icon . ' ' : '') . $name;
            $row[] = InlineKeyboardButton::make($label, callback_data: 'search_cat:' . $cat->slug);
            if (count($row) === 2 || $i === $categories->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        $keyboard->addRow(InlineKeyboardButton::make(
            $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'search:main'
        ));

        $bot->editMessageText(
            text: $lang === 'ru' ? "📂 *Выберите категорию:*" : "📂 *Kategoriyani tanlang:*",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function showCities(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);
        $cities = City::active()->get();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($cities as $i => $city) {
            $count = Vacancy::active()->where('city', $city->name_uz)->count();
            $name = $lang === 'ru' ? ($city->name_ru ?? $city->name_uz) : $city->name_uz;
            $row[] = InlineKeyboardButton::make(
                $name . " ({$count})",
                callback_data: 'search_city:' . $city->name_uz
            );
            if (count($row) === 2 || $i === $cities->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        $keyboard->addRow(InlineKeyboardButton::make(
            $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'search:main'
        ));

        $bot->editMessageText(
            text: $lang === 'ru' ? "📍 *Выберите город:*" : "📍 *Shaharni tanlang:*",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
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
        $lang = $this->getUserLang($bot);
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
            $text = $lang === 'ru'
                ? "😔 Вакансии не найдены.\n\n📌 Выберите другой фильтр:"
                : "😔 Vakansiyalar topilmadi.\n\n📌 Boshqa filtrni tanlang:";

            $bot->editMessageText(
                text: $text,
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'search:main'
                    )),
            );
            return;
        }

        $countWord = $lang === 'ru' ? 'шт' : 'ta';
        $pageWord = $lang === 'ru' ? 'Страница' : 'Sahifa';
        $text = $lang === 'ru'
            ? "📋 *Вакансии* ({$total} {$countWord})\n{$pageWord}: {$page}/{$totalPages}\n\n"
            : "📋 *Vakansiyalar* ({$total} {$countWord})\n{$pageWord}: {$page}/{$totalPages}\n\n";

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

        $keyboard->addRow(InlineKeyboardButton::make(
            $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'search:main'
        ));

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function showVacancyDetail(Nutgram $bot, string $vacancyId): void
    {
        $lang = $this->getUserLang($bot);
        $vacancy = Vacancy::with('employer')->find($vacancyId);

        if (!$vacancy) {
            $bot->editMessageText(
                text: $lang === 'ru' ? '❌ Вакансия не найдена.' : '❌ Vakansiya topilmadi.',
                message_id: $bot->callbackQuery()->message->message_id,
            );
            return;
        }

        $vacancy->increment('views_count');

        $salary = $vacancy->salaryFormatted();
        $workType = $vacancy->work_type?->label() ?? '-';
        $company = $vacancy->employer?->company_name ?? '-';
        $top = $vacancy->isTopActive() ? '🔥 TOP ' : '';

        $isRu = $lang === 'ru';

        $text = "{$top}📌 *{$vacancy->title}*\n\n";
        $text .= "🏢 " . ($isRu ? 'Компания' : 'Kompaniya') . ": {$company}\n";
        $text .= "📂 " . ($isRu ? 'Категория' : 'Kategoriya') . ": {$vacancy->category}\n";
        $text .= "📍 " . ($isRu ? 'Город' : 'Shahar') . ": {$vacancy->city}\n";
        $text .= "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n";
        $text .= "🏢 " . ($isRu ? 'Тип работы' : 'Ish turi') . ": {$workType}\n";

        if ($vacancy->experience_required) {
            $text .= "⏱ " . ($isRu ? 'Опыт' : 'Tajriba') . ": {$vacancy->experience_required}\n";
        }

        $text .= "\n📝 *" . ($isRu ? 'Описание' : 'Tavsif') . ":*\n{$vacancy->description}\n";

        if ($vacancy->requirements) {
            $text .= "\n📋 *" . ($isRu ? 'Требования' : 'Talablar') . ":*\n{$vacancy->requirements}\n";
        }
        if ($vacancy->responsibilities) {
            $text .= "\n✅ *" . ($isRu ? 'Обязанности' : 'Vazifalar') . ":*\n{$vacancy->responsibilities}\n";
        }

        $viewsLabel = $isRu ? 'Просмотры' : "Ko'rishlar";
        $appsLabel = $isRu ? 'Заявки' : 'Arizalar';
        $text .= "\n👁 {$viewsLabel}: {$vacancy->views_count} | 📝 {$appsLabel}: {$vacancy->applications_count}";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📝 Подать заявку' : '📝 Ariza berish',
                    callback_data: "vacancy_apply:{$vacancy->id}"
                ),
                InlineKeyboardButton::make(
                    $isRu ? '🔗 Поделиться' : '🔗 Ulashish',
                    switch_inline_query: $vacancy->title
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '◀️ Назад' : '◀️ Orqaga',
                    callback_data: 'search:main'
                ),
            );

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function applyToVacancy(Nutgram $bot, string $vacancyId): void
    {
        $lang = $this->getUserLang($bot);
        $isRu = $lang === 'ru';
        $vacancy = Vacancy::find($vacancyId);

        if (!$vacancy || !$vacancy->isActive()) {
            $bot->editMessageText(
                text: $isRu ? '❌ Вакансия не найдена или не активна.' : '❌ Vakansiya topilmadi yoki faol emas.',
                message_id: $bot->callbackQuery()->message->message_id,
            );
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();
        if (!$user) {
            $bot->sendMessage(text: $isRu ? 'Сначала отправьте /start.' : 'Avval /start buyrug\'ini yuboring.');
            return;
        }

        $worker = $user->workerProfile;
        if (!$worker) {
            $text = $isRu
                ? "📝 Для подачи заявки сначала создайте резюме.\n\nОтправьте /resume."
                : "📝 Ariza berish uchun avval rezume yarating.\n\n/resume buyrug'ini yuboring.";

            $bot->editMessageText(
                text: $text,
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(
                        $isRu ? '📝 Создать резюме' : '📝 Rezume yaratish',
                        callback_data: 'resume:create'
                    ))
                    ->addRow(InlineKeyboardButton::make(
                        $isRu ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: "vacancy_view:{$vacancyId}"
                    )),
            );
            return;
        }

        $existing = Application::where('vacancy_id', $vacancyId)
            ->where('worker_id', $worker->id)
            ->first();

        if ($existing) {
            $bot->editMessageText(
                text: $isRu ? "ℹ️ Вы уже подали заявку на эту вакансию." : "ℹ️ Siz bu vakansiyaga allaqachon ariza bergansiz.",
                message_id: $bot->callbackQuery()->message->message_id,
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(InlineKeyboardButton::make(
                        $isRu ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: "vacancy_view:{$vacancyId}"
                    )),
            );
            return;
        }

        Application::create([
            'vacancy_id' => $vacancyId,
            'worker_id' => $worker->id,
            'stage' => ApplicationStage::NEW,
            'source' => 'telegram',
        ]);

        $vacancy->increment('applications_count');

        $text = $isRu
            ? "✅ *Заявка отправлена!*\n\n📌 {$vacancy->title}\n\nРаботодатель рассмотрит вашу заявку."
            : "✅ *Ariza yuborildi!*\n\n📌 {$vacancy->title}\n\nIsh beruvchi sizning arizangizni ko'rib chiqadi.";

        $bot->editMessageText(
            text: $text,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(InlineKeyboardButton::make(
                    $isRu ? '🔍 Вернуться к поиску' : '🔍 Qidiruvga qaytish',
                    callback_data: 'search:main'
                )),
        );
    }

    private function getUserLang(Nutgram $bot): string
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        return $user?->language?->value ?? 'uz';
    }
}
