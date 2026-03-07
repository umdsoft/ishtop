<?php

namespace App\Telegram\Handlers;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Models\Category;
use App\Models\City;
use App\Models\SavedItem;
use App\Models\User;
use App\Models\Vacancy;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class SearchHandler
{
    protected int $perPage = 10;

    public function __invoke(Nutgram $bot): void
    {
        $this->showMainSearch($bot);
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        try {
            if (str_starts_with($data, 'search_subcat:')) {
                $this->showSubCategories($bot, str_replace('search_subcat:', '', $data));
            } elseif (str_starts_with($data, 'search_cat:')) {
                $this->searchByCategory($bot, str_replace('search_cat:', '', $data));
            } elseif (str_starts_with($data, 'search_region:')) {
                $this->searchByRegion($bot, str_replace('search_region:', '', $data));
            } elseif (str_starts_with($data, 'search_districts:')) {
                $this->showDistricts($bot, str_replace('search_districts:', '', $data));
            } elseif (str_starts_with($data, 'search_district:')) {
                $this->searchByDistrict($bot, str_replace('search_district:', '', $data));
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
            } elseif ($data === 'search:regions') {
                $this->showRegions($bot);
            } elseif ($data === 'search:all') {
                $this->showVacancies($bot, 1, 'all', '');
            } elseif (str_starts_with($data, 'vacancy_view:')) {
                $this->showVacancyDetail($bot, str_replace('vacancy_view:', '', $data));
            } elseif (str_starts_with($data, 'vacancy_apply:')) {
                $this->applyToVacancy($bot, str_replace('vacancy_apply:', '', $data));
            }
        } catch (\Throwable $e) {
            Log::error('SearchHandler error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $bot->sendMessage(text: "Xatolik yuz berdi. /menu");
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
                    $lang === 'ru' ? '📍 По региону' : '📍 Viloyat bo\'yicha',
                    callback_data: 'search:regions'
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

    protected function getCategoryEmoji(string $slug): string
    {
        return match ($slug) {
            'it' => '💻',
            'sales' => '🛒',
            'shop-seller' => '🏪',
            'sales-manager' => '💼',
            'call-center' => '📞',
            'food' => '🍽',
            'driver' => '🚗',
            'construction' => '🔧',
            'beauty' => '💇',
            'education' => '🎓',
            'finance' => '💰',
            'marketing' => '📢',
            'logistics' => '📦',
            'security' => '🛡',
            'cleaning' => '✨',
            'admin' => '🏢',
            'production' => '⚙️',
            'other' => '📋',
            default => '📁',
        };
    }

    protected function showCategories(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);
        $categories = Category::active()->root()->withCount(['children' => fn($q) => $q->where('is_active', true)])->get();

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($categories as $cat) {
            $name = $lang === 'ru' ? ($cat->name_ru ?? $cat->name_uz) : $cat->name_uz;
            $emoji = $this->getCategoryEmoji($cat->slug);
            $count = $this->getCategoryVacancyCount($cat);
            $label = "{$emoji} {$name} ({$count})";
            $callback = $cat->children_count > 0 ? 'search_subcat:' . $cat->slug : 'search_cat:' . $cat->slug;
            $row[] = InlineKeyboardButton::make($label, callback_data: $callback);
            if (count($row) === 2) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard->addRow(...$row);
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

    protected function showSubCategories(Nutgram $bot, string $parentSlug): void
    {
        $lang = $this->getUserLang($bot);
        $parent = Category::where('slug', $parentSlug)->first();
        if (!$parent) return;

        $children = Category::active()->where('parent_id', $parent->id)->get();
        $parentEmoji = $this->getCategoryEmoji($parent->slug);
        $parentName = $lang === 'ru' ? ($parent->name_ru ?? $parent->name_uz) : $parent->name_uz;

        $keyboard = InlineKeyboardMarkup::make();

        // "Barcha" tugmasi — parent kategoriyaning barcha vakansiyalari
        $totalCount = $this->getCategoryVacancyCount($parent);
        $allLabel = $lang === 'ru'
            ? "📋 Все «{$parentName}» ({$totalCount})"
            : "📋 Barcha «{$parentName}» ({$totalCount})";
        $keyboard->addRow(InlineKeyboardButton::make($allLabel, callback_data: 'search_cat:' . $parent->slug));

        foreach ($children as $child) {
            $name = $lang === 'ru' ? ($child->name_ru ?? $child->name_uz) : $child->name_uz;
            $emoji = $this->getCategoryEmoji($child->slug);
            $count = Vacancy::where('status', 'active')->where('category', $child->slug)->count();
            $label = "{$emoji} {$name} ({$count})";
            $keyboard->addRow(InlineKeyboardButton::make($label, callback_data: 'search_cat:' . $child->slug));
        }

        $keyboard->addRow(InlineKeyboardButton::make(
            $lang === 'ru' ? '◀️ Категории' : '◀️ Kategoriyalar',
            callback_data: 'search:categories'
        ));

        $title = $lang === 'ru'
            ? "{$parentEmoji} *{$parentName}* — выберите направление:"
            : "{$parentEmoji} *{$parentName}* — yo'nalishni tanlang:";

        $bot->editMessageText(
            text: $title,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function getCategoryVacancyCount(Category $category): int
    {
        $slugs = [$category->slug];
        $children = Category::where('parent_id', $category->id)->where('is_active', true)->pluck('slug');
        $slugs = array_merge($slugs, $children->toArray());
        return Vacancy::where('status', 'active')->whereIn('category', $slugs)->count();
    }

    protected function showRegions(Nutgram $bot): void
    {
        $lang = $this->getUserLang($bot);
        $regions = City::active()
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');

        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($regions as $i => $region) {
            $count = Vacancy::active()->where('city', 'LIKE', "%{$region}%")->count();
            $row[] = InlineKeyboardButton::make(
                $region . " ({$count})",
                callback_data: 'search_region:' . $region
            );
            if (count($row) === 2 || $i === $regions->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        $keyboard->addRow(InlineKeyboardButton::make(
            $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
            callback_data: 'search:main'
        ));

        $bot->editMessageText(
            text: $lang === 'ru' ? "📍 *Выберите регион:*" : "📍 *Viloyatni tanlang:*",
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function searchByCategory(Nutgram $bot, string $slug): void
    {
        $this->showVacancies($bot, 1, 'category', $slug);
    }

    protected function searchByRegion(Nutgram $bot, string $region): void
    {
        $this->showVacancies($bot, 1, 'region', $region);
    }

    protected function showDistricts(Nutgram $bot, string $region): void
    {
        $lang = $this->getUserLang($bot);
        $isRu = $lang === 'ru';

        $cities = City::active()
            ->where('region', $region)
            ->orderBy('name_uz')
            ->get();

        $keyboard = InlineKeyboardMarkup::make();

        // "All in region" button first
        $allCount = Vacancy::active()->where('city', 'LIKE', "%{$region}%")->count();
        $keyboard->addRow(InlineKeyboardButton::make(
            ($isRu ? "📍 Все в {$region}" : "📍 Barcha {$region}") . " ({$allCount})",
            callback_data: 'search_region:' . $region
        ));

        $row = [];
        foreach ($cities as $i => $city) {
            $count = Vacancy::active()->where('city', 'LIKE', "%{$city->name_uz}%")->count();
            $name = $isRu ? ($city->name_ru ?? $city->name_uz) : $city->name_uz;
            $row[] = InlineKeyboardButton::make(
                $name . " ({$count})",
                callback_data: 'search_district:' . $region . '|' . $city->name_uz
            );
            if (count($row) === 2 || $i === $cities->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $keyboard->addRow(InlineKeyboardButton::make(
            $isRu ? '◀️ К регионам' : '◀️ Viloyatlarga',
            callback_data: 'search:regions'
        ));

        $title = $isRu ? "🏘 *{$region} — районы/города:*" : "🏘 *{$region} — tuman/shaharlar:*";

        $bot->editMessageText(
            text: $title,
            message_id: $bot->callbackQuery()->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function searchByDistrict(Nutgram $bot, string $value): void
    {
        $this->showVacancies($bot, 1, 'district', $value);
    }

    protected function showVacancies(Nutgram $bot, int $page, string $filter, string $value): void
    {
        $lang = $this->getUserLang($bot);
        $query = Vacancy::active()->with('employer')->latest('published_at');

        if ($filter === 'category') {
            $category = Category::where('slug', $value)->first();
            if ($category) {
                // Parent kategoriya bo'lsa, bolalarining vakansiyalarini ham qo'shish
                $slugs = [$category->slug];
                $childSlugs = Category::where('parent_id', $category->id)->where('is_active', true)->pluck('slug');
                $slugs = array_merge($slugs, $childSlugs->toArray());
                $query->whereIn('category', $slugs);
            }
        } elseif ($filter === 'region') {
            $query->where('city', 'LIKE', "%{$value}%");
        } elseif ($filter === 'district') {
            // value format: "regionName|cityName"
            $parts = explode('|', $value);
            $cityName = $parts[1] ?? $parts[0];
            $query->where('city', 'LIKE', "%{$cityName}%");
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
            $text .= "{$top}*{$num}. {$v->title()}*\n📍 {$v->city} | 💰 {$salary}\n🏢 {$company}\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();

        // Number buttons in rows of 5
        $numRow = [];
        foreach ($vacancies as $i => $v) {
            $num = $offset + $i + 1;
            $numRow[] = InlineKeyboardButton::make((string) $num, callback_data: "vacancy_view:{$v->id}");
            if (count($numRow) === 5) {
                $keyboard->addRow(...$numRow);
                $numRow = [];
            }
        }
        if (count($numRow) > 0) {
            $keyboard->addRow(...$numRow);
        }

        // Pagination
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

        // Sub-filter: districts within region
        if ($filter === 'region') {
            $keyboard->addRow(InlineKeyboardButton::make(
                $lang === 'ru' ? '🏘 По районам/городам' : '🏘 Tuman/shahar bo\'yicha',
                callback_data: 'search_districts:' . $value
            ));
            $keyboard->addRow(InlineKeyboardButton::make(
                $lang === 'ru' ? '◀️ К регионам' : '◀️ Viloyatlarga',
                callback_data: 'search:regions'
            ));
        } elseif ($filter === 'district') {
            // Back to region — extract region name from value
            $regionName = explode('|', $value)[0] ?? '';
            $keyboard->addRow(InlineKeyboardButton::make(
                $lang === 'ru' ? '◀️ К районам' : '◀️ Tumanlarga',
                callback_data: 'search_districts:' . $regionName
            ));
        } else {
            $keyboard->addRow(InlineKeyboardButton::make(
                $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                callback_data: 'search:main'
            ));
        }

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

        $text = "{$top}📌 *{$vacancy->title()}*\n\n";
        $text .= "🏢 " . ($isRu ? 'Компания' : 'Kompaniya') . ": {$company}\n";
        $text .= "📂 " . ($isRu ? 'Категория' : 'Kategoriya') . ": {$vacancy->category}\n";
        $text .= "📍 " . ($isRu ? 'Город' : 'Shahar') . ": {$vacancy->city}\n";
        $text .= "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n";
        $text .= "🏢 " . ($isRu ? 'Тип работы' : 'Ish turi') . ": {$workType}\n";

        if ($vacancy->experience_required) {
            $text .= "⏱ " . ($isRu ? 'Опыт' : 'Tajriba') . ": {$vacancy->experience_required}\n";
        }

        $text .= "\n📝 *" . ($isRu ? 'Описание' : 'Tavsif') . ":*\n{$vacancy->description($lang)}\n";

        if ($vacancy->requirements($lang)) {
            $text .= "\n📋 *" . ($isRu ? 'Требования' : 'Talablar') . ":*\n{$vacancy->requirements($lang)}\n";
        }
        if ($vacancy->responsibilities($lang)) {
            $text .= "\n✅ *" . ($isRu ? 'Обязанности' : 'Vazifalar') . ":*\n{$vacancy->responsibilities($lang)}\n";
        }

        $viewsLabel = $isRu ? 'Просмотры' : "Ko'rishlar";
        $appsLabel = $isRu ? 'Заявки' : 'Arizalar';
        $text .= "\n👁 {$viewsLabel}: {$vacancy->views_count} | 📝 {$appsLabel}: {$vacancy->applications_count}";

        // Check if vacancy is saved
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $isSaved = $user ? SavedItem::where('user_id', $user->id)
            ->where('saveable_type', Vacancy::class)
            ->where('saveable_id', $vacancy->id)
            ->exists() : false;

        $saveLabel = $isSaved
            ? ($isRu ? '❤️ Сохранено' : '❤️ Saqlangan')
            : ($isRu ? '🤍 Сохранить' : '🤍 Saqlash');

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📝 Подать заявку' : '📝 Ariza berish',
                    callback_data: "vacancy_apply:{$vacancy->id}"
                ),
                InlineKeyboardButton::make(
                    $saveLabel,
                    callback_data: "saved:toggle:{$vacancy->id}"
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '🔗 Поделиться' : '🔗 Ulashish',
                    switch_inline_query: $vacancy->title()
                ),
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
            ? "✅ *Заявка отправлена!*\n\n📌 {$vacancy->title('ru')}\n\nРаботодатель рассмотрит вашу заявку."
            : "✅ *Ariza yuborildi!*\n\n📌 {$vacancy->title()}\n\nIsh beruvchi sizning arizangizni ko'rib chiqadi.";

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
