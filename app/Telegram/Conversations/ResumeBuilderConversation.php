<?php

namespace App\Telegram\Conversations;

use App\Enums\SearchStatus;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Models\WorkerProfile;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class ResumeBuilderConversation extends Conversation
{
    protected string $lang = 'uz';
    protected array $data = [];

    public function start(Nutgram $bot): void
    {
        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();
        $this->lang = $user?->language?->value ?? 'uz';

        $existing = $user?->workerProfile;
        if ($existing) {
            $this->data = [
                'full_name' => $existing->full_name,
                'birth_date' => $existing->birth_date?->format('d.m.Y'),
                'gender' => $existing->gender,
                'city' => $existing->city,
                'education_level' => $existing->education_level,
                'specialty' => $existing->specialty,
                'experience_years' => $existing->experience_years,
                'skills' => $existing->skills ?? [],
                'work_types' => $existing->work_types ?? [],
                'expected_salary_min' => $existing->expected_salary_min,
                'expected_salary_max' => $existing->expected_salary_max,
                'bio' => $existing->bio,
            ];
        }

        $text = $this->t(
            "📝 *Rezume yaratish*\n\n11 ta oddiy qadam bilan rezumengizni yarating.\nIstalgan vaqtda /cancel bilan bekor qilishingiz mumkin.\n\n*1/11 — To\'liq ismingiz:*",
            "📝 *Создание резюме*\n\n11 простых шагов для создания резюме.\nВ любой момент отправьте /cancel для отмены.\n\n*1/11 — Ваше полное имя:*"
        );

        $bot->sendMessage(text: $text, parse_mode: ParseMode::MARKDOWN_LEGACY);
        $this->next('handleFullName');
    }

    public function handleFullName(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $name = trim($bot->message()->text ?? '');
        if (mb_strlen($name) < 2) {
            $bot->sendMessage(text: $this->t('❌ Ism juda qisqa. Qaytadan kiriting:', '❌ Имя слишком короткое. Введите ещё раз:'));
            return;
        }

        $this->data['full_name'] = $name;

        $bot->sendMessage(
            text: $this->t(
                "*2/11 — Tug\'ilgan sanangiz:*\nFormat: KK.OO.YYYY (masalan: 15.06.1995)",
                "*2/11 — Дата рождения:*\nФормат: ДД.ММ.ГГГГ (например: 15.06.1995)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleBirthDate');
    }

    public function handleBirthDate(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');

        if (!preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $text, $m)) {
            $bot->sendMessage(text: $this->t('❌ Noto\'g\'ri format. KK.OO.YYYY kiriting:', '❌ Неверный формат. Введите ДД.ММ.ГГГГ:'));
            return;
        }

        if (!checkdate((int) $m[2], (int) $m[1], (int) $m[3])) {
            $bot->sendMessage(text: $this->t('❌ Noto\'g\'ri sana. Qaytadan kiriting:', '❌ Неверная дата. Введите ещё раз:'));
            return;
        }

        $this->data['birth_date'] = $text;

        $bot->sendMessage(
            text: $this->t('*3/11 — Jinsingiz:*', '*3/11 — Ваш пол:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make($this->t('👨 Erkak', '👨 Мужской'), callback_data: 'resume_gender:male'),
                    InlineKeyboardButton::make($this->t('👩 Ayol', '👩 Женский'), callback_data: 'resume_gender:female'),
                ),
        );
        $this->next('handleGender');
    }

    public function handleGender(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'resume_gender:')) return;

        $this->data['gender'] = str_replace('resume_gender:', '', $cb->data);
        $bot->answerCallbackQuery();

        $genderLabel = $this->data['gender'] === 'male'
            ? $this->t('Erkak', 'Мужской')
            : $this->t('Ayol', 'Женский');

        $bot->editMessageText(
            text: "✅ {$genderLabel}",
            message_id: $cb->message->message_id,
        );

        $cities = City::active()->get();
        $keyboard = InlineKeyboardMarkup::make();
        $row = [];
        foreach ($cities as $i => $city) {
            $row[] = InlineKeyboardButton::make(
                $city->name($this->lang),
                callback_data: 'resume_city:' . $city->name_uz
            );
            if (count($row) === 2 || $i === $cities->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $bot->sendMessage(
            text: $this->t('*4/11 — Shahringiz:*', '*4/11 — Ваш город:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleCity');
    }

    public function handleCity(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if ($cb && str_starts_with($cb->data ?? '', 'resume_city:')) {
            $this->data['city'] = str_replace('resume_city:', '', $cb->data);
            $bot->answerCallbackQuery();
            $bot->editMessageText(
                text: "✅ {$this->data['city']}",
                message_id: $cb->message->message_id,
            );
        } else {
            $text = trim($bot->message()->text ?? '');
            if (empty($text)) return;
            $this->data['city'] = $text;
        }

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make($this->t("O\'rta", 'Среднее'), callback_data: 'resume_edu:secondary'),
                InlineKeyboardButton::make($this->t("O\'rta maxsus", 'Среднее спец.'), callback_data: 'resume_edu:vocational'),
            )
            ->addRow(
                InlineKeyboardButton::make($this->t('Oliy', 'Высшее'), callback_data: 'resume_edu:bachelor'),
                InlineKeyboardButton::make($this->t('Magistratura', 'Магистратура'), callback_data: 'resume_edu:master'),
            );

        $bot->sendMessage(
            text: $this->t("*5/11 — Ta\'lim darajangiz:*", '*5/11 — Уровень образования:*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleEducation');
    }

    public function handleEducation(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'resume_edu:')) return;

        $this->data['education_level'] = str_replace('resume_edu:', '', $cb->data);
        $bot->answerCallbackQuery();

        $labels = [
            'secondary' => $this->t("O\'rta", 'Среднее'),
            'vocational' => $this->t("O\'rta maxsus", 'Среднее спец.'),
            'bachelor' => $this->t('Oliy', 'Высшее'),
            'master' => $this->t('Magistratura', 'Магистратура'),
        ];

        $bot->editMessageText(
            text: '✅ ' . ($labels[$this->data['education_level']] ?? $this->data['education_level']),
            message_id: $cb->message->message_id,
        );

        $bot->sendMessage(
            text: $this->t(
                "*6/11 — Mutaxassisligingiz:*\n(masalan: Dasturchi, Buxgalter, Dizayner)",
                "*6/11 — Ваша специальность:*\n(например: Программист, Бухгалтер, Дизайнер)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleSpecialty');
    }

    public function handleSpecialty(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (empty($text)) {
            $bot->sendMessage(text: $this->t('❌ Mutaxassislikni kiriting:', '❌ Введите специальность:'));
            return;
        }

        $this->data['specialty'] = $text;

        $bot->sendMessage(
            text: $this->t('*7/11 — Tajriba (yil):*', '*7/11 — Опыт работы (лет):*'),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make($this->t('Tajribasiz', 'Без опыта'), callback_data: 'resume_exp:0'),
                    InlineKeyboardButton::make('1-2', callback_data: 'resume_exp:1'),
                )
                ->addRow(
                    InlineKeyboardButton::make('3-5', callback_data: 'resume_exp:3'),
                    InlineKeyboardButton::make('5+', callback_data: 'resume_exp:5'),
                ),
        );
        $this->next('handleExperience');
    }

    public function handleExperience(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if ($cb && str_starts_with($cb->data ?? '', 'resume_exp:')) {
            $this->data['experience_years'] = (int) str_replace('resume_exp:', '', $cb->data);
            $bot->answerCallbackQuery();
            $bot->editMessageText(
                text: '✅ ' . $this->data['experience_years'] . ' ' . $this->t('yil', 'лет'),
                message_id: $cb->message->message_id,
            );
        } else {
            if ($this->checkCancel($bot)) return;
            $text = trim($bot->message()->text ?? '');
            if (!is_numeric($text)) {
                $bot->sendMessage(text: $this->t('❌ Raqam kiriting:', '❌ Введите число:'));
                return;
            }
            $this->data['experience_years'] = (int) $text;
        }

        $bot->sendMessage(
            text: $this->t(
                "*8/11 — Ko\'nikmalaringiz:*\nVergul bilan ajratib yozing\n(masalan: PHP, Laravel, MySQL, Git)",
                "*8/11 — Ваши навыки:*\nЧерез запятую\n(например: PHP, Laravel, MySQL, Git)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleSkills');
    }

    public function handleSkills(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (empty($text)) {
            $bot->sendMessage(text: $this->t('❌ Kamida bitta ko\'nikma kiriting:', '❌ Введите хотя бы один навык:'));
            return;
        }

        $this->data['skills'] = array_map('trim', explode(',', $text));

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make($this->t('To\'liq kun', 'Полный день'), callback_data: 'resume_wt:full_time'),
                InlineKeyboardButton::make($this->t('Yarim stavka', 'Полставки'), callback_data: 'resume_wt:part_time'),
            )
            ->addRow(
                InlineKeyboardButton::make($this->t('Masofaviy', 'Удалёнка'), callback_data: 'resume_wt:remote'),
                InlineKeyboardButton::make($this->t('Vaqtinchalik', 'Временная'), callback_data: 'resume_wt:temporary'),
            )
            ->addRow(
                InlineKeyboardButton::make('✅ ' . $this->t('Tayyor', 'Готово'), callback_data: 'resume_wt:done'),
            );

        $this->data['work_types'] = [];

        $bot->sendMessage(
            text: $this->t(
                "*9/11 — Ish turi:*\nBir yoki bir nechta tanlang, keyin ✅ bosing",
                "*9/11 — Тип работы:*\nВыберите один или несколько, затем нажмите ✅"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
        $this->next('handleWorkType');
    }

    public function handleWorkType(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'resume_wt:')) return;

        $value = str_replace('resume_wt:', '', $cb->data);

        if ($value === 'done') {
            if (empty($this->data['work_types'])) {
                $this->data['work_types'] = ['full_time'];
            }
            $bot->answerCallbackQuery();

            $labels = array_map(fn($t) => match ($t) {
                'full_time' => $this->t('To\'liq kun', 'Полный день'),
                'part_time' => $this->t('Yarim stavka', 'Полставки'),
                'remote' => $this->t('Masofaviy', 'Удалёнка'),
                'temporary' => $this->t('Vaqtinchalik', 'Временная'),
                default => $t,
            }, $this->data['work_types']);

            $bot->editMessageText(
                text: '✅ ' . implode(', ', $labels),
                message_id: $cb->message->message_id,
            );

            $bot->sendMessage(
                text: $this->t(
                    "*10/11 — Kutilayotgan maosh:*\nMinimum va maksimum (so\'m)\nMasalan: 3000000 - 5000000\n\nYoki \"-\" — kelishiladi",
                    "*10/11 — Ожидаемая зарплата:*\nМин и макс (сум)\nНапример: 3000000 - 5000000\n\nИли \"-\" — договорная"
                ),
                parse_mode: ParseMode::MARKDOWN_LEGACY,
            );
            $this->next('handleSalary');
            return;
        }

        if (!in_array($value, $this->data['work_types'])) {
            $this->data['work_types'][] = $value;
            $bot->answerCallbackQuery(text: '✅ ' . $value);
        } else {
            $this->data['work_types'] = array_values(array_diff($this->data['work_types'], [$value]));
            $bot->answerCallbackQuery(text: '❌ ' . $value);
        }
    }

    public function handleSalary(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');

        if ($text === '-') {
            $this->data['expected_salary_min'] = null;
            $this->data['expected_salary_max'] = null;
        } elseif (preg_match('/^(\d+)\s*[-–]\s*(\d+)$/', $text, $m)) {
            $this->data['expected_salary_min'] = (int) $m[1];
            $this->data['expected_salary_max'] = (int) $m[2];
        } elseif (is_numeric($text)) {
            $this->data['expected_salary_min'] = (int) $text;
            $this->data['expected_salary_max'] = (int) $text;
        } else {
            $bot->sendMessage(text: $this->t(
                '❌ Noto\'g\'ri format. Masalan: 3000000 - 5000000',
                '❌ Неверный формат. Например: 3000000 - 5000000'
            ));
            return;
        }

        $bot->sendMessage(
            text: $this->t(
                "*11/11 — O\'zingiz haqingizda qisqacha:*\n(yoki \"-\" yuboring)",
                "*11/11 — Коротко о себе:*\n(или отправьте \"-\" чтобы пропустить)"
            ),
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );
        $this->next('handleBio');
    }

    public function handleBio(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        $this->data['bio'] = ($text === '-' || empty($text)) ? null : $text;

        $this->saveProfile($bot);
    }

    protected function saveProfile(Nutgram $bot): void
    {
        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();

        if (!$user) {
            $bot->sendMessage(text: $this->t('❌ Avval /start buyrug\'ini yuboring.', '❌ Сначала отправьте /start.'));
            $this->end();
            return;
        }

        $birthDate = null;
        if (!empty($this->data['birth_date'])) {
            $parts = explode('.', $this->data['birth_date']);
            $birthDate = "{$parts[2]}-{$parts[1]}-{$parts[0]}";
        }

        WorkerProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $this->data['full_name'],
                'birth_date' => $birthDate,
                'gender' => $this->data['gender'] ?? null,
                'city' => $this->data['city'] ?? null,
                'district' => null,
                'education_level' => $this->data['education_level'] ?? null,
                'specialty' => $this->data['specialty'] ?? null,
                'experience_years' => $this->data['experience_years'] ?? 0,
                'skills' => $this->data['skills'] ?? [],
                'work_types' => $this->data['work_types'] ?? [],
                'expected_salary_min' => $this->data['expected_salary_min'] ?? null,
                'expected_salary_max' => $this->data['expected_salary_max'] ?? null,
                'bio' => $this->data['bio'] ?? null,
                'search_status' => SearchStatus::OPEN,
            ]
        );

        $summary = $this->buildSummary();

        $bot->sendMessage(
            text: $summary,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make('✏️ ' . $this->t('Qayta tahrirlash', 'Редактировать'), callback_data: 'resume:create'),
                    InlineKeyboardButton::make('✅ ' . $this->t('Tayyor', 'Готово'), callback_data: 'menu:back'),
                ),
        );

        $this->end();
    }

    protected function buildSummary(): string
    {
        $name = $this->data['full_name'] ?? '-';
        $birth = $this->data['birth_date'] ?? '-';
        $gender = match ($this->data['gender'] ?? null) {
            'male' => $this->t('Erkak', 'Мужской'),
            'female' => $this->t('Ayol', 'Женский'),
            default => '-',
        };
        $city = $this->data['city'] ?? '-';
        $edu = $this->data['education_level'] ?? '-';
        $spec = $this->data['specialty'] ?? '-';
        $exp = ($this->data['experience_years'] ?? 0) . ' ' . $this->t('yil', 'лет');
        $skills = !empty($this->data['skills']) ? implode(', ', $this->data['skills']) : '-';

        $salary = '-';
        if (!empty($this->data['expected_salary_min']) && !empty($this->data['expected_salary_max'])) {
            $salary = number_format($this->data['expected_salary_min']) . ' - ' . number_format($this->data['expected_salary_max']) . " so'm";
        } elseif (!empty($this->data['expected_salary_min'])) {
            $salary = number_format($this->data['expected_salary_min']) . "+ so'm";
        }

        return $this->t(
            "🎉 *Rezume tayyor!*\n\n👤 Ism: {$name}\n📅 Tug\'ilgan: {$birth}\n👤 Jins: {$gender}\n📍 Shahar: {$city}\n🎓 Ta\'lim: {$edu}\n💼 Mutaxassislik: {$spec}\n⏱ Tajriba: {$exp}\n🛠 Ko\'nikmalar: {$skills}\n💰 Maosh: {$salary}",
            "🎉 *Резюме готово!*\n\n👤 Имя: {$name}\n📅 Дата рождения: {$birth}\n👤 Пол: {$gender}\n📍 Город: {$city}\n🎓 Образование: {$edu}\n💼 Специальность: {$spec}\n⏱ Опыт: {$exp}\n🛠 Навыки: {$skills}\n💰 Зарплата: {$salary}"
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
