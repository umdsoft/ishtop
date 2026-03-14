<?php

namespace App\Telegram\Conversations;

use App\Models\Category;
use App\Models\City;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Services\VacancyService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class PostVacancyConversation extends Conversation
{
    protected string $lang = 'uz';
    protected array $data = [];
    protected ?string $employerId = null;

    public function start(Nutgram $bot): void
    {
        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            $this->end();
            return;
        }

        $this->lang = $user->language?->value ?? 'uz';
        $employer = $user->employerProfile;

        if (!$employer) {
            $bot->sendMessage(
                text: $this->t(
                    "📢 *E\'lon berish*\n\nAvval kompaniya nomini kiriting:",
                    "📢 *Создание вакансии*\n\nСначала введите название компании:"
                ),
                parse_mode: ParseMode::MARKDOWN_LEGACY,
            );
            $this->next('handleCompanyName');
            return;
        }

        $this->employerId = $employer->id;
        $this->askTitle($bot);
    }

    public function handleCompanyName(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $name = trim($bot->message()->text ?? '');
        if (mb_strlen($name) < 2) {
            $bot->sendMessage(text: $this->t('❌ Nom juda qisqa.', '❌ Слишком короткое название.'));
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();
        $employer = EmployerProfile::create([
            'user_id' => $user->id,
            'company_name' => $name,
        ]);

        $this->employerId = $employer->id;
        $this->askTitle($bot);
    }

    protected function askTitle(Nutgram $bot): void
    {
        $bot->sendMessage(
            text: $this->t(
                "*1/8 — Vakansiya sarlavhasi:*\n(masalan: PHP dasturchi, Sotuvchi, Haydovchi)",
                "*1/8 — Название вакансии:*\n(например: PHP разработчик, Продавец, Водитель)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleTitle');
    }

    public function handleTitle(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (mb_strlen($text) < 3) {
            $bot->sendMessage(text: $this->t('❌ Sarlavha juda qisqa.', '❌ Слишком короткое название.'));
            return;
        }

        $this->data['title'] = $text;

        $categories = Category::active()->get();
        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($categories as $i => $cat) {
            $label = ($cat->icon ? $cat->icon . ' ' : '') . $cat->name($this->lang);
            $row[] = InlineKeyboardButton::make($label, callback_data: 'vac_cat:' . $cat->slug);
            if (count($row) === 2 || $i === $categories->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $bot->sendMessage(
            text: $this->t('*2/8 — Kategoriyani tanlang:*', '*2/8 — Выберите категорию:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleCategory');
    }

    public function handleCategory(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_cat:')) return;

        $slug = str_replace('vac_cat:', '', $cb->data);
        $category = Category::where('slug', $slug)->first();
        $this->data['category'] = $category ? $category->name_uz : $slug;

        $bot->answerCallbackQuery();
        $bot->editMessageText(
            text: '✅ ' . $this->data['category'],
            message_id: $cb->message->message_id,
        );

        // Avval viloyat tanlash — yagona cache dan olish
        $locations = City::cachedLocations();
        $regions = collect($locations['regions'])->pluck('key')->sort()->values();
        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($regions as $i => $region) {
            $row[] = InlineKeyboardButton::make(
                $region,
                callback_data: 'vac_reg:' . $region
            );
            if (count($row) === 2 || $i === $regions->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $bot->sendMessage(
            text: $this->t('*3/8 — Viloyatni tanlang:*', '*3/8 — Выберите регион:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleRegion');
    }

    public function handleRegion(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_reg:')) return;

        $region = str_replace('vac_reg:', '', $cb->data);
        $this->data['city'] = $region; // vacancy.city = region nomi

        $bot->answerCallbackQuery();
        $bot->editMessageText(
            text: '✅ ' . $region,
            message_id: $cb->message->message_id,
        );

        // Shu viloyatdagi shahar/tumanlarni ko'rsatish — yagona cache dan
        $locations = City::cachedLocations();
        $cities = collect($locations['cities'])->where('region', $region)->sortBy('name_uz')->values();
        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        $total = $cities->count();
        foreach ($cities as $i => $city) {
            $nameUz = $city['name_uz'];
            $name = $this->lang === 'ru' ? ($city['name_ru'] ?? $nameUz) : $nameUz;
            $label = $name . ($city['type'] ? ' (' . $city['type'] . ')' : '');
            $row[] = InlineKeyboardButton::make(
                $label,
                callback_data: 'vac_city:' . $nameUz
            );
            if (count($row) === 2 || $i === $total - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }
        // "O'tkazib yuborish" tugmasi
        $keyboard->addRow(
            InlineKeyboardButton::make(
                $this->t('⏭ O\'tkazib yuborish', '⏭ Пропустить'),
                callback_data: 'vac_city:skip'
            )
        );

        $bot->sendMessage(
            text: $this->t('*Shahar/tumanni tanlang:*', '*Выберите город/район:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleCity');
    }

    public function handleCity(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if ($cb && str_starts_with($cb->data ?? '', 'vac_city:')) {
            $cityName = str_replace('vac_city:', '', $cb->data);
            if ($cityName !== 'skip') {
                $this->data['district'] = $cityName;
            }
            $bot->answerCallbackQuery();
            $bot->editMessageText(
                text: '✅ ' . ($cityName !== 'skip' ? $cityName : $this->data['city']),
                message_id: $cb->message->message_id,
            );
        } else {
            if ($this->checkCancel($bot)) return;
            $text = trim($bot->message()->text ?? '');
            if (empty($text)) return;
            $this->data['district'] = $text;
        }

        $bot->sendMessage(
            text: $this->t('*4/8 — Vakansiya tavsifi:*', '*4/8 — Описание вакансии:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleDescription');
    }

    public function handleDescription(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (mb_strlen($text) < 10) {
            $bot->sendMessage(text: $this->t('❌ Tavsif juda qisqa (kamida 10 belgi).', '❌ Слишком короткое описание (минимум 10 символов).'));
            return;
        }

        $this->data['description'] = $text;

        $bot->sendMessage(
            text: $this->t(
                "*5/8 — Talablar:*\n(yoki \"-\" o\'tkazib yuborish)",
                "*5/8 — Требования:*\n(или \"-\" чтобы пропустить)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleRequirements');
    }

    public function handleRequirements(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        $this->data['requirements'] = ($text === '-' || empty($text)) ? null : $text;

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make($this->t('Belgilangan', 'Фиксированная'), callback_data: 'vac_sal:fixed'),
                InlineKeyboardButton::make($this->t('Diapazon', 'Диапазон'), callback_data: 'vac_sal:range'),
            )
            ->addRow(
                InlineKeyboardButton::make($this->t('Kelishiladi', 'Договорная'), callback_data: 'vac_sal:negotiable'),
            );

        $bot->sendMessage(
            text: $this->t('*6/8 — Maosh turi:*', '*6/8 — Тип зарплаты:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleSalaryType');
    }

    public function handleSalaryType(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_sal:')) return;

        $type = str_replace('vac_sal:', '', $cb->data);
        $this->data['salary_type'] = $type;
        $bot->answerCallbackQuery();

        if ($type === 'negotiable') {
            $this->data['salary_min'] = null;
            $this->data['salary_max'] = null;

            $bot->editMessageText(
                text: $this->t('✅ Maosh: Kelishiladi', '✅ Зарплата: Договорная'),
                message_id: $cb->message->message_id,
            );
            $this->askWorkType($bot);
            return;
        }

        $bot->editMessageText(
            text: '✅ ' . $type,
            message_id: $cb->message->message_id,
        );

        if ($type === 'fixed') {
            $bot->sendMessage(
                text: $this->t("💰 Maosh miqdorini kiriting (so\'m):", '💰 Введите сумму зарплаты (сум):'),
            );
        } else {
            $bot->sendMessage(
                text: $this->t("💰 Maosh diapazonini kiriting (masalan: 3000000 - 5000000):", '💰 Введите диапазон зарплаты (например: 3000000 - 5000000):'),
            );
        }
        $this->next('handleSalaryAmount');
    }

    public function handleSalaryAmount(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');

        if ($this->data['salary_type'] === 'fixed') {
            if (!is_numeric($text)) {
                $bot->sendMessage(text: $this->t('❌ Raqam kiriting:', '❌ Введите число:'));
                return;
            }
            $this->data['salary_min'] = (int) $text;
            $this->data['salary_max'] = (int) $text;
        } else {
            if (!preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $text, $m)) {
                $bot->sendMessage(text: $this->t('❌ Format: 3000000 - 5000000', '❌ Формат: 3000000 - 5000000'));
                return;
            }
            $this->data['salary_min'] = (int) $m[1];
            $this->data['salary_max'] = (int) $m[2];
        }

        $this->askWorkType($bot);
    }

    protected function askWorkType(Nutgram $bot): void
    {
        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make($this->t('To\'liq kun', 'Полный день'), callback_data: 'vac_wt:full_time'),
                InlineKeyboardButton::make($this->t('Yarim stavka', 'Полставки'), callback_data: 'vac_wt:part_time'),
            )
            ->addRow(
                InlineKeyboardButton::make($this->t('Masofaviy', 'Удалёнка'), callback_data: 'vac_wt:remote'),
                InlineKeyboardButton::make($this->t('Vaqtinchalik', 'Временная'), callback_data: 'vac_wt:temporary'),
            );

        $bot->sendMessage(
            text: $this->t('*7/8 — Ish turini tanlang:*', '*7/8 — Выберите тип работы:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleWorkType');
    }

    public function handleWorkType(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_wt:')) return;

        $this->data['work_type'] = str_replace('vac_wt:', '', $cb->data);
        $bot->answerCallbackQuery();

        $labels = [
            'full_time' => $this->t('To\'liq kun', 'Полный день'),
            'part_time' => $this->t('Yarim stavka', 'Полставки'),
            'remote' => $this->t('Masofaviy', 'Удалёнка'),
            'temporary' => $this->t('Vaqtinchalik', 'Временная'),
        ];

        $bot->editMessageText(
            text: '✅ ' . ($labels[$this->data['work_type']] ?? $this->data['work_type']),
            message_id: $cb->message->message_id,
        );

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('📱 Telegram', callback_data: 'vac_cm:telegram'),
                InlineKeyboardButton::make('📞 Telefon', callback_data: 'vac_cm:phone'),
            )
            ->addRow(
                InlineKeyboardButton::make($this->t('Ikkalasi', 'Оба'), callback_data: 'vac_cm:both'),
            );

        $bot->sendMessage(
            text: $this->t('*8/8 — Aloqa usuli:*', '*8/8 — Способ связи:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleContactMethod');
    }

    public function handleContactMethod(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_cm:')) return;

        $this->data['contact_method'] = str_replace('vac_cm:', '', $cb->data);
        $bot->answerCallbackQuery();

        $user = User::where('telegram_id', $bot->user()->id)->first();
        $summary = $this->buildSummary($user);

        $bot->editMessageText(
            text: '✅ ' . $this->data['contact_method'],
            message_id: $cb->message->message_id,
        );

        $bot->sendMessage(
            text: $summary,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('✅ ' . $this->t('Tasdiqlash', 'Подтвердить'), callback_data: 'vac_confirm:yes'),
                    InlineKeyboardButton::make('❌ ' . $this->t('Bekor qilish', 'Отменить'), callback_data: 'vac_confirm:no'),
                ),
        );
        $this->next('handleConfirm');
    }

    public function handleConfirm(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'vac_confirm:')) return;

        $action = str_replace('vac_confirm:', '', $cb->data);
        $bot->answerCallbackQuery();

        if ($action === 'no') {
            $bot->editMessageText(
                text: $this->t('❌ E\'lon bekor qilindi.', '❌ Вакансия отменена.'),
                message_id: $cb->message->message_id,
            );
            $this->end();
            return;
        }

        $user = User::where('telegram_id', $bot->user()->id)->first();
        $service = app(VacancyService::class);

        $vacancy = $service->create([
            'employer_id' => $this->employerId,
            'language' => $this->lang,
            "title_{$this->lang}" => $this->data['title'],
            'category' => $this->data['category'],
            "description_{$this->lang}" => $this->data['description'],
            "requirements_{$this->lang}" => $this->data['requirements'] ?? null,
            'salary_min' => $this->data['salary_min'] ?? null,
            'salary_max' => $this->data['salary_max'] ?? null,
            'salary_type' => $this->data['salary_type'] ?? 'negotiable',
            'work_type' => $this->data['work_type'] ?? 'full_time',
            'city' => $this->data['city'] ?? null,
            'district' => $this->data['district'] ?? null,
            'contact_phone' => $user->phone,
            'contact_method' => $this->data['contact_method'] ?? 'both',
        ]);

        $bot->editMessageText(
            text: $this->t(
                "✅ *E\'lon yaratildi!*\n\nSizning vakansiyangiz moderatsiyaga yuborildi.\nTasdiqlangandan keyin 15 kun davomida faol bo\'ladi.\n\n📌 /menu — Bosh menyu",
                "✅ *Вакансия создана!*\n\nВаша вакансия отправлена на модерацию.\nПосле подтверждения будет активна 15 дней.\n\n📌 /menu — Главное меню"
            ),
            message_id: $cb->message->message_id,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );

        $this->end();
    }

    protected function buildSummary(?User $user): string
    {
        $title = $this->data['title'] ?? '-';
        $cat = $this->data['category'] ?? '-';
        $city = $this->data['city'] ?? '-';
        $desc = mb_substr($this->data['description'] ?? '-', 0, 100) . '...';

        $salary = match ($this->data['salary_type'] ?? 'negotiable') {
            'negotiable' => $this->t('Kelishiladi', 'Договорная'),
            'fixed' => number_format($this->data['salary_min'] ?? 0) . " so\'m",
            'range' => number_format($this->data['salary_min'] ?? 0) . ' - ' . number_format($this->data['salary_max'] ?? 0) . " so\'m",
            default => '-',
        };

        $workLabels = [
            'full_time' => $this->t('To\'liq kun', 'Полный день'),
            'part_time' => $this->t('Yarim stavka', 'Полставки'),
            'remote' => $this->t('Masofaviy', 'Удалёнка'),
            'temporary' => $this->t('Vaqtinchalik', 'Временная'),
        ];
        $workType = $workLabels[$this->data['work_type'] ?? 'full_time'] ?? '-';

        return $this->t(
            "📋 *E\'lon xulosasi:*\n\n📌 {$title}\n📂 {$cat}\n📍 {$city}\n💰 {$salary}\n🏢 {$workType}\n\n📝 {$desc}\n\nTasdiqlaysizmi?",
            "📋 *Сводка вакансии:*\n\n📌 {$title}\n📂 {$cat}\n📍 {$city}\n💰 {$salary}\n🏢 {$workType}\n\n📝 {$desc}\n\nПодтверждаете?"
        );
    }

    protected function t(string $uz, string $ru): string
    {
        return $this->lang === 'ru' ? $ru : $uz;
    }

    protected function checkCancel(Nutgram $bot): bool
    {
        $text = $bot->message()->text ?? '';
        if ($text === '/cancel') {
            $bot->sendMessage(text: $this->t('❌ Bekor qilindi. /menu — Bosh menyu', '❌ Отменено. /menu — Главное меню'));
            $this->end();
            return true;
        }
        return false;
    }
}
