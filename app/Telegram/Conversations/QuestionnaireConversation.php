<?php

namespace App\Telegram\Conversations;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Services\ScoringService;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

/**
 * QuestionnaireConversation
 * Allows candidates to fill questionnaires via Telegram bot
 */
class QuestionnaireConversation extends Conversation
{
    protected string $lang = 'uz';
    public ?string $applicationId = null;
    protected array $answers = [];
    protected array $questions = [];
    protected int $currentIndex = 0;

    public function start(Nutgram $bot, ?string $applicationId = null): void
    {
        $telegramUser = $bot->user();
        $user = User::where('telegram_id', $telegramUser->id)->first();

        if (!$user) {
            $bot->sendMessage(text: 'Avval /start buyrug\'ini yuboring.');
            $this->end();
            return;
        }

        $this->lang = $user->language?->value ?? 'uz';
        $this->applicationId = $applicationId ?? $this->applicationId;

        $application = Application::with('vacancy.employer')->find($this->applicationId);

        if (!$application) {
            $bot->sendMessage(text: $this->t('❌ Ariza topilmadi.', '❌ Заявка не найдена.'));
            $this->end();
            return;
        }

        // Check if application belongs to user
        if ($application->worker?->user_id !== $user->id) {
            $bot->sendMessage(text: $this->t('❌ Bu ariza sizga tegishli emas.', '❌ Эта заявка вам не принадлежит.'));
            $this->end();
            return;
        }

        // Check if already completed
        if ($application->questionnaire_completed) {
            $bot->sendMessage(text: $this->t('✅ Siz allaqachon savolnomani to\'ldirdingiz!', '✅ Вы уже заполнили анкету!'));
            $this->end();
            return;
        }

        // Load questions
        $this->questions = Question::where('vacancy_id', $application->vacancy_id)
            ->orderBy('sort_order')
            ->get()
            ->toArray();

        if (empty($this->questions)) {
            $bot->sendMessage(text: $this->t('Bu vakansiya uchun savollar mavjud emas.', 'Для этой вакансии нет вопросов.'));
            $this->end();
            return;
        }

        // Load existing answers if any
        $this->answers = $application->questionnaire_answers ?? [];
        $this->currentIndex = count($this->answers);

        $vacancyTitle = $application->vacancy->title();
        $total = count($this->questions);

        $intro = $this->t(
            "📋 *Savolnoma*\n\n*Vakansiya:* {$vacancyTitle}\n\n{$total} ta savolga javob bering.\nHar qanday vaqtda /cancel bilan bekor qilishingiz mumkin.\n\n_Tayyor bo'lsangiz, \"Boshlash\" tugmasini bosing._",
            "📋 *Анкета*\n\n*Вакансия:* {$vacancyTitle}\n\nОтветьте на {$total} вопросов.\nВ любое время можете отменить командой /cancel.\n\n_Когда будете готовы, нажмите \"Начать\"._"
        );

        $bot->sendMessage(
            text: $intro,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $this->t('▶️ Boshlash', '▶️ Начать'),
                        callback_data: 'quest_start:yes'
                    )
                ),
        );

        $this->next('handleStart');
    }

    public function handleStart(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if ($cb && $cb->data === 'quest_start:yes') {
            $bot->answerCallbackQuery();
            $bot->editMessageText(
                text: $this->t('✅ Boshlaymiz!', '✅ Начинаем!'),
                message_id: $cb->message->message_id,
            );

            $this->askQuestion($bot);
        }
    }

    protected function askQuestion(Nutgram $bot): void
    {
        if ($this->currentIndex >= count($this->questions)) {
            $this->completeQuestionnaire($bot);
            return;
        }

        $question = $this->questions[$this->currentIndex];
        $total = count($this->questions);
        $progress = $this->currentIndex + 1;

        $text = "*{$progress}/{$total}*\n\n";
        $text .= $question['question_' . $this->lang] ?? $question['question_uz'] ?? $question['text'];

        if (!empty($question['description'])) {
            $text .= "\n\n_" . ($question['description_' . $this->lang] ?? $question['description']) . "_";
        }

        $type = $question['type'];

        if ($type === 'single_choice' || $type === 'knockout') {
            $keyboard = $this->buildOptionsKeyboard($question);
            $bot->sendMessage(
                text: $text,
                parse_mode: ParseMode::MARKDOWN_LEGACY,
                reply_markup: $keyboard,
            );
            $this->next('handleSingleChoice');

        } elseif ($type === 'multi_select') {
            $keyboard = $this->buildMultiSelectKeyboard($question);
            $bot->sendMessage(
                text: $text . "\n\n" . $this->t('(Bir nechta variant tanlang, keyin ✅ bosing)', '(Выберите несколько, затем нажмите ✅)'),
                parse_mode: ParseMode::MARKDOWN_LEGACY,
                reply_markup: $keyboard,
            );
            Cache::put("quest_multi_{$bot->userId()}_{$this->currentIndex}", [], now()->addMinutes(30));
            $this->next('handleMultiSelect');

        } elseif ($type === 'number_range') {
            $config = $question['scoring_config'] ?? [];
            $min = $config['min'] ?? 0;
            $max = $config['max'] ?? 100;
            $text .= "\n\n" . $this->t("Raqam kiriting ({$min} - {$max}):", "Введите число ({$min} - {$max}):");

            $bot->sendMessage(text: $text, parse_mode: ParseMode::MARKDOWN_LEGACY);
            $this->next('handleNumberRange');

        } elseif ($type === 'open_text') {
            $text .= "\n\n" . $this->t('Javobingizni yozing:', 'Напишите ваш ответ:');
            $bot->sendMessage(text: $text, parse_mode: ParseMode::MARKDOWN_LEGACY);
            $this->next('handleOpenText');

        } elseif ($type === 'file_upload') {
            $text .= "\n\n" . $this->t('Faylni yuboring (hujjat, rasm):', 'Отправьте файл (документ, изображение):');
            $bot->sendMessage(text: $text, parse_mode: ParseMode::MARKDOWN_LEGACY);
            $this->next('handleFileUpload');

        } else {
            // Unknown type, skip
            $this->currentIndex++;
            $this->askQuestion($bot);
        }
    }

    public function handleSingleChoice(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'quest_opt:')) {
            return;
        }

        $optionId = str_replace('quest_opt:', '', $cb->data);
        $bot->answerCallbackQuery();

        $question = $this->questions[$this->currentIndex];
        $option = QuestionOption::find($optionId);
        $answerValue = $option ? $option->value : $optionId;

        $this->answers[$question['id']] = $answerValue;

        $bot->editMessageText(
            text: '✅ ' . ($option ? $option->{'label_' . $this->lang} : $answerValue),
            message_id: $cb->message->message_id,
        );

        // Check if knockout failed
        if ($question['is_knockout'] ?? false) {
            $scoringService = app(ScoringService::class);
            $questionModel = Question::find($question['id']);
            if ($scoringService->isKnockoutFailed($questionModel, $answerValue)) {
                $this->handleKnockoutFailed($bot);
                return;
            }
        }

        $this->currentIndex++;
        $this->askQuestion($bot);
    }

    public function handleMultiSelect(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        if (!$cb || !str_starts_with($cb->data ?? '', 'quest_ms:')) {
            return;
        }

        $action = str_replace('quest_ms:', '', $cb->data);

        $cacheKey = "quest_multi_{$bot->userId()}_{$this->currentIndex}";
        $selected = Cache::get($cacheKey, []);

        if ($action === 'done') {
            if (empty($selected)) {
                $bot->answerCallbackQuery(text: $this->t('❌ Kamida bitta variant tanlang', '❌ Выберите хотя бы один'));
                return;
            }

            $bot->answerCallbackQuery();
            $question = $this->questions[$this->currentIndex];
            $this->answers[$question['id']] = $selected;

            $bot->editMessageText(
                text: '✅ ' . implode(', ', $selected),
                message_id: $cb->message->message_id,
            );

            Cache::forget($cacheKey);
            $this->currentIndex++;
            $this->askQuestion($bot);
            return;
        }

        // Toggle option
        if (in_array($action, $selected)) {
            $selected = array_values(array_diff($selected, [$action]));
            $bot->answerCallbackQuery(text: '➖ ' . $action);
        } else {
            $selected[] = $action;
            $bot->answerCallbackQuery(text: '✅ ' . $action);
        }

        Cache::put($cacheKey, $selected, now()->addMinutes(30));
    }

    public function handleNumberRange(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (!is_numeric($text)) {
            $bot->sendMessage(text: $this->t('❌ Raqam kiriting:', '❌ Введите число:'));
            return;
        }

        $value = (int) $text;
        $question = $this->questions[$this->currentIndex];
        $config = $question['scoring_config'] ?? [];
        $min = $config['min'] ?? 0;
        $max = $config['max'] ?? 100;

        if ($value < $min || $value > $max) {
            $bot->sendMessage(text: $this->t("❌ {$min} va {$max} orasida bo'lishi kerak:", "❌ Должно быть между {$min} и {$max}:"));
            return;
        }

        $this->answers[$question['id']] = $value;
        $this->currentIndex++;
        $this->askQuestion($bot);
    }

    public function handleOpenText(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $text = trim($bot->message()->text ?? '');
        if (empty($text)) {
            $bot->sendMessage(text: $this->t('❌ Javob yozing:', '❌ Напишите ответ:'));
            return;
        }

        $question = $this->questions[$this->currentIndex];
        $this->answers[$question['id']] = $text;
        $this->currentIndex++;
        $this->askQuestion($bot);
    }

    public function handleFileUpload(Nutgram $bot): void
    {
        if ($this->checkCancel($bot)) return;

        $message = $bot->message();
        $fileId = null;

        if ($message->document) {
            $fileId = $message->document->file_id;
        } elseif ($message->photo) {
            $fileId = end($message->photo)->file_id;
        } elseif ($message->video) {
            $fileId = $message->video->file_id;
        } else {
            $bot->sendMessage(text: $this->t('❌ Fayl yuboring:', '❌ Отправьте файл:'));
            return;
        }

        $question = $this->questions[$this->currentIndex];
        $this->answers[$question['id']] = $fileId;
        $this->currentIndex++;
        $this->askQuestion($bot);
    }

    protected function completeQuestionnaire(Nutgram $bot): void
    {
        $application = Application::find($this->applicationId);
        if (!$application) {
            $bot->sendMessage(text: $this->t('❌ Xatolik yuz berdi.', '❌ Произошла ошибка.'));
            $this->end();
            return;
        }

        // Calculate score
        $scoringService = app(ScoringService::class);
        $totalScore = 0;
        $maxScore = 0;

        foreach ($this->questions as $q) {
            $question = Question::find($q['id']);
            $answer = $this->answers[$q['id']] ?? null;

            if ($question && $answer !== null) {
                $score = $scoringService->scoreAnswer($question, $answer);
                $totalScore += $score;
                $maxScore += $question->weight;
            }
        }

        $percentage = $maxScore > 0 ? round(($totalScore / $maxScore) * 100) : 0;

        // Update application
        $application->update([
            'questionnaire_answers' => $this->answers,
            'questionnaire_score' => $totalScore,
            'questionnaire_max_score' => $maxScore,
            'questionnaire_completed' => true,
            'questionnaire_completed_at' => now(),
        ]);

        // Auto-advance if score is good
        if ($percentage >= 70) {
            $application->moveToStage(ApplicationStage::SHORTLISTED);
        }

        $congratsText = $this->t(
            "🎉 *Savolnoma yakunlandi!*\n\nSizning natijangiz: *{$totalScore}/{$maxScore}* ({$percentage}%)\n\nIsh beruvchi tez orada siz bilan bog'lanadi.\n\n📌 /menu — Bosh menyu",
            "🎉 *Анкета завершена!*\n\nВаш результат: *{$totalScore}/{$maxScore}* ({$percentage}%)\n\nРаботодатель скоро свяжется с вами.\n\n📌 /menu — Главное меню"
        );

        $bot->sendMessage(
            text: $congratsText,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
        );

        $this->end();
    }

    protected function handleKnockoutFailed(Nutgram $bot): void
    {
        $application = Application::find($this->applicationId);
        if ($application) {
            $application->moveToStage(ApplicationStage::REJECTED);
            $application->update([
                'questionnaire_answers' => $this->answers,
                'questionnaire_completed' => true,
                'questionnaire_completed_at' => now(),
            ]);
        }

        $bot->sendMessage(
            text: $this->t(
                "❌ Afsuski, siz bu vakansiya talablariga mos kelmadingiz.\n\n📌 /menu — Bosh menyu",
                "❌ К сожалению, вы не соответствуете требованиям этой вакансии.\n\n📌 /menu — Главное меню"
            ),
        );

        $this->end();
    }

    protected function buildOptionsKeyboard(array $question): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        $options = QuestionOption::where('question_id', $question['id'])
            ->orderBy('sort_order')
            ->get();

        foreach ($options as $option) {
            $label = $option->{'label_' . $this->lang} ?? $option->label_uz ?? $option->label;
            $keyboard->addRow(
                InlineKeyboardButton::make($label, callback_data: 'quest_opt:' . $option->id)
            );
        }

        return $keyboard;
    }

    protected function buildMultiSelectKeyboard(array $question): InlineKeyboardMarkup
    {
        $keyboard = InlineKeyboardMarkup::make();

        $options = QuestionOption::where('question_id', $question['id'])
            ->orderBy('sort_order')
            ->get();

        $row = [];
        foreach ($options as $i => $option) {
            $label = $option->{'label_' . $this->lang} ?? $option->label_uz ?? $option->label;
            $row[] = InlineKeyboardButton::make($label, callback_data: 'quest_ms:' . $option->value);

            if (count($row) === 2 || $i === $options->count() - 1) {
                $keyboard->addRow(...$row);
                $row = [];
            }
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '✅ ' . $this->t('Tayyor', 'Готово'),
                callback_data: 'quest_ms:done'
            )
        );

        return $keyboard;
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
