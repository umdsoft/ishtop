<?php

namespace App\Telegram\Handlers;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Models\User;
use App\Telegram\Conversations\QuestionnaireConversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AppsHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $user = $this->getUser($bot);
        if (!$user) {
            $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $workerProfile = $user->workerProfile;

        if (!$workerProfile) {
            $this->showNoProfileMessage($bot, $lang);
            return;
        }

        $applications = Application::with('vacancy.employer')
            ->where('worker_id', $workerProfile->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        if ($applications->isEmpty()) {
            $this->showNoApplications($bot, $lang);
            return;
        }

        $this->showApplicationsList($bot, $applications, $lang);
    }

    public function handleCallback(Nutgram $bot): void
    {
        $data = $bot->callbackQuery()->data ?? '';
        $bot->answerCallbackQuery();

        $user = $this->getUser($bot);
        if (!$user) {
            $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
            return;
        }

        $lang = $user->language?->value ?? 'uz';

        // app:back
        if ($data === 'app:back') {
            $this->__invoke($bot);
            return;
        }

        // app:view:{id}
        if (str_starts_with($data, 'app:view:')) {
            $appId = str_replace('app:view:', '', $data);
            $app = Application::with('vacancy.employer')->find($appId);
            if ($app) {
                $this->showApplicationDetails($bot, $app, $lang);
            }
            return;
        }

        // app:quest:{id}
        if (str_starts_with($data, 'app:quest:')) {
            $appId = str_replace('app:quest:', '', $data);
            (new QuestionnaireConversation())->begin($bot, $appId);
            return;
        }

        // app:withdraw:{id}
        if (str_starts_with($data, 'app:withdraw:')) {
            $appId = str_replace('app:withdraw:', '', $data);
            $app = Application::with('vacancy.employer')->find($appId);
            if ($app) {
                $app->update(['stage' => ApplicationStage::WITHDRAWN]);
                $msg = $lang === 'ru' ? '🚫 Заявка отозвана' : '🚫 Ariza bekor qilindi';
                $bot->answerCallbackQuery(text: $msg, show_alert: true);
                $this->showApplicationDetails($bot, $app->fresh(), $lang);
            }
            return;
        }
    }

    protected function showNoProfileMessage(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📝 *Мои заявки*\n\nЧтобы подавать заявки, сначала создайте резюме.\n\n/resume — Создать резюме"
            : "📝 *Mening arizalarim*\n\nAriza yuborish uchun avval rezume yarating.\n\n/resume — Rezume yaratish";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )
                ),
        );
    }

    protected function showNoApplications(Nutgram $bot, string $lang): void
    {
        $text = $lang === 'ru'
            ? "📝 *Мои заявки*\n\nУ вас пока нет заявок.\nНачните искать работу!\n\n/search — Поиск вакансий"
            : "📝 *Mening arizalarim*\n\nSizda hali ariza yo'q.\nIsh qidirishni boshlang!\n\n/search — Vakansiya qidirish";

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '🔍 Искать работу' : '🔍 Ish qidirish',
                        callback_data: 'menu:search'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        $lang === 'ru' ? '◀️ Назад' : '◀️ Orqaga',
                        callback_data: 'menu:back'
                    )
                ),
        );
    }

    protected function showApplicationsList(Nutgram $bot, $applications, string $lang): void
    {
        $header = $lang === 'ru'
            ? "📝 *Мои заявки* (" . $applications->count() . ")\n\n"
            : "📝 *Mening arizalarim* (" . $applications->count() . ")\n\n";

        $text = $header;

        // Group by stage
        $grouped = $applications->groupBy('stage');

        foreach ($grouped as $stage => $apps) {
            $stageEnum = ApplicationStage::tryFrom($stage);
            $statusIcon = $this->getStatusIcon($stageEnum);
            $statusLabel = $this->getStatusLabel($stageEnum, $lang);
            $text .= "{$statusIcon} *{$statusLabel}* ({$apps->count()})\n\n";
        }

        $text .= $lang === 'ru'
            ? "_Выберите заявку для подробностей:_"
            : "_Tafsilotlar uchun arizani tanlang:_";

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($applications->take(8) as $app) {
            $statusIcon = $this->getStatusIcon($app->stage);
            $vacancyTitle = mb_substr($app->vacancy->title ?? 'Vakansiya', 0, 25);

            $keyboard->addRow(
                InlineKeyboardButton::make(
                    "{$statusIcon} {$vacancyTitle}",
                    callback_data: 'app:view:' . $app->id
                )
            );
        }

        $miniAppUrl = config('app.url') . '/miniapp#/applications';
        if (!str_contains($miniAppUrl, 'localhost')) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '🌐 ' . ($lang === 'ru' ? 'Открыть Mini App' : 'Mini App ochish'),
                    url: $miniAppUrl
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'menu:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    protected function showApplicationDetails(Nutgram $bot, Application $app, string $lang): void
    {
        $vacancy = $app->vacancy;
        $statusIcon = $this->getStatusIcon($app->stage);
        $statusText = $this->getStatusLabel($app->stage, $lang);

        $salary = '-';
        if ($vacancy->salary_type === 'negotiable') {
            $salary = $lang === 'ru' ? 'Договорная' : 'Kelishiladi';
        } elseif ($vacancy->salary_min && $vacancy->salary_max) {
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($vacancy->salary_min) . ' - ' . number_format($vacancy->salary_max) . " {$currency}";
        }

        $text = "{$statusIcon} *{$vacancy->title}*\n\n"
            . "🏢 " . ($vacancy->employer->company_name ?? '-') . "\n"
            . "📍 " . ($vacancy->city ?? '-') . "\n"
            . "💰 {$salary}\n"
            . "📊 " . ($lang === 'ru' ? 'Статус' : 'Holat') . ": {$statusText}\n"
            . "📅 " . ($lang === 'ru' ? 'Подано' : 'Yuborildi') . ": " . $app->created_at->format('d.m.Y H:i') . "\n\n";

        if ($app->questionnaire_completed) {
            $percentage = $app->questionnaire_max_score > 0
                ? round(($app->questionnaire_score / $app->questionnaire_max_score) * 100)
                : 0;

            $text .= "📋 " . ($lang === 'ru' ? 'Анкета' : 'Savolnoma') . ": ✅\n";
            $text .= "📊 " . ($lang === 'ru' ? 'Результат' : 'Natija') . ": {$app->questionnaire_score}/{$app->questionnaire_max_score} ({$percentage}%)\n\n";
        } elseif (!in_array($app->stage, [ApplicationStage::REJECTED, ApplicationStage::WITHDRAWN])) {
            $text .= "⚠️ " . ($lang === 'ru' ? 'Анкета не заполнена' : "Savolnoma to'ldirilmagan") . "\n\n";
        }

        if ($app->cover_letter) {
            $text .= "✉️ " . ($lang === 'ru' ? 'Сопроводительное письмо' : "Qo'shimcha xat") . ":\n";
            $text .= mb_substr($app->cover_letter, 0, 150) . "...\n\n";
        }

        $keyboard = InlineKeyboardMarkup::make();

        if (!$app->questionnaire_completed && !in_array($app->stage, [ApplicationStage::REJECTED, ApplicationStage::WITHDRAWN])) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '📋 ' . ($lang === 'ru' ? 'Заполнить анкету' : "Savolnomani to'ldirish"),
                    callback_data: 'app:quest:' . $app->id
                )
            );
        }

        if (!in_array($app->stage, [ApplicationStage::REJECTED, ApplicationStage::HIRED, ApplicationStage::WITHDRAWN])) {
            $keyboard->addRow(
                InlineKeyboardButton::make(
                    '🚫 ' . ($lang === 'ru' ? 'Отозвать заявку' : 'Arizani bekor qilish'),
                    callback_data: 'app:withdraw:' . $app->id
                )
            );
        }

        $keyboard->addRow(
            InlineKeyboardButton::make(
                '◀️ ' . ($lang === 'ru' ? 'Назад' : 'Orqaga'),
                callback_data: 'app:back'
            )
        );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    private function getStatusIcon(?ApplicationStage $stage): string
    {
        return match ($stage) {
            ApplicationStage::NEW => '🆕',
            ApplicationStage::REVIEWED => '👀',
            ApplicationStage::SHORTLISTED => '⭐',
            ApplicationStage::INTERVIEW => '🎯',
            ApplicationStage::OFFERED => '📨',
            ApplicationStage::HIRED => '✅',
            ApplicationStage::REJECTED => '❌',
            ApplicationStage::WITHDRAWN => '🚫',
            default => '⚪',
        };
    }

    private function getStatusLabel(?ApplicationStage $stage, string $lang): string
    {
        $isRu = $lang === 'ru';
        return match ($stage) {
            ApplicationStage::NEW => $isRu ? 'Новая' : 'Yangi',
            ApplicationStage::REVIEWED => $isRu ? 'На рассмотрении' : "Ko'rib chiqilmoqda",
            ApplicationStage::SHORTLISTED => $isRu ? 'В избранном' : 'Tanlab olingan',
            ApplicationStage::INTERVIEW => $isRu ? 'Интервью' : 'Intervyu',
            ApplicationStage::OFFERED => $isRu ? 'Предложение' : 'Taklif',
            ApplicationStage::HIRED => $isRu ? 'Принята' : 'Qabul qilingan',
            ApplicationStage::REJECTED => $isRu ? 'Отклонена' : 'Rad etilgan',
            ApplicationStage::WITHDRAWN => $isRu ? 'Отозвана' : 'Bekor qilingan',
            default => '-',
        };
    }

    private function getUser(Nutgram $bot): ?User
    {
        return User::where('telegram_id', $bot->user()->id)->first();
    }
}
