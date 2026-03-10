<?php

namespace App\Services;

use App\Enums\ApplicationStage;
use App\Models\Application;
use App\Models\Notification;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramNotificationService
{
    private string $botToken;
    private string $botUsername;
    private string $appUrl;

    public function __construct()
    {
        $this->botToken = config('nutgram.token');
        $this->botUsername = config('nutgram.bot_username', 'kadrgo_bot');
        $this->appUrl = config('app.url');
    }

    /**
     * Notify worker when their application stage changes
     */
    public function notifyApplicationStageChanged(Application $application, ApplicationStage $oldStage, ApplicationStage $newStage): void
    {
        $application->loadMissing('vacancy.employer', 'worker');
        $user = User::find($application->worker->user_id);

        if (!$user || !$this->canNotify($user)) {
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $vacancyTitle = $application->vacancy->title();

        $stageMessages = [
            'reviewed' => [
                'uz' => "Sizning \"{$vacancyTitle}\" vakansiyasiga arizangiz ko'rib chiqildi",
                'ru' => "Ваша заявка на вакансию \"{$vacancyTitle}\" рассмотрена",
            ],
            'shortlisted' => [
                'uz' => "Tabriklaymiz! Siz \"{$vacancyTitle}\" vakansiyasi bo'yicha tanlanganlar ro'yxatiga qo'shildingiz!",
                'ru' => "Поздравляем! Вы добавлены в список избранных по вакансии \"{$vacancyTitle}\"!",
            ],
            'interview' => [
                'uz' => "Siz \"{$vacancyTitle}\" vakansiyasi bo'yicha intervyuga taklif qilindingiz!",
                'ru' => "Вы приглашены на интервью по вакансии \"{$vacancyTitle}\"!",
            ],
            'offered' => [
                'uz' => "Sizga \"{$vacancyTitle}\" vakansiyasi bo'yicha ish taklif qilindi! Tabriklaymiz!",
                'ru' => "Вам предложена работа по вакансии \"{$vacancyTitle}\"! Поздравляем!",
            ],
            'hired' => [
                'uz' => "Siz \"{$vacancyTitle}\" vakansiyasiga qabul qilindingiz! Tabriklaymiz!",
                'ru' => "Вы приняты на вакансию \"{$vacancyTitle}\"! Поздравляем!",
            ],
            'rejected' => [
                'uz' => "Afsus, \"{$vacancyTitle}\" vakansiyasiga arizangiz rad etildi",
                'ru' => "К сожалению, ваша заявка на вакансию \"{$vacancyTitle}\" отклонена",
            ],
        ];

        $stageValue = $newStage->value;
        $message = $stageMessages[$stageValue][$lang] ?? $stageMessages[$stageValue]['uz'] ?? "Ariza holati o'zgardi";
        $title = $lang === 'ru' ? 'Статус заявки обновлён' : 'Ariza holati yangilandi';
        $icon = $this->getStageIcon($newStage);

        // Save to DB
        Notification::create([
            'user_id' => $user->id,
            'type' => 'application_stage_changed',
            'title' => $title,
            'message' => $message,
            'data' => [
                'application_id' => $application->id,
                'vacancy_id' => $application->vacancy_id,
                'old_stage' => $oldStage->value,
                'new_stage' => $newStage->value,
            ],
        ]);

        // Send Telegram
        $text = "{$icon} *{$title}*\n\n{$message}";
        $keyboard = [
            'inline_keyboard' => [
                [['text' => $lang === 'ru' ? '📋 Мои заявки' : '📋 Arizalarim', 'callback_data' => 'menu:apps']],
                [['text' => '🌐 Mini App', 'url' => "https://t.me/{$this->botUsername}/app"]],
            ],
        ];

        $this->sendTelegram($user->telegram_id, $text, $keyboard);
    }

    /**
     * Notify employer when a new application is received
     */
    public function notifyNewApplication(Application $application): void
    {
        $application->loadMissing('vacancy.employer', 'worker');
        $employer = $application->vacancy->employer;
        $user = User::find($employer->user_id);

        if (!$user || !$this->canNotify($user)) {
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $workerName = $application->worker->full_name ?? 'Nomzod';
        $vacancyTitle = $application->vacancy->title();

        $title = $lang === 'ru' ? 'Новая заявка' : 'Yangi ariza';
        $message = $lang === 'ru'
            ? "{$workerName} подал(а) заявку на вакансию \"{$vacancyTitle}\""
            : "{$workerName} \"{$vacancyTitle}\" vakansiyasiga ariza yubordi";

        Notification::create([
            'user_id' => $user->id,
            'type' => 'new_application',
            'title' => $title,
            'message' => $message,
            'data' => [
                'application_id' => $application->id,
                'vacancy_id' => $application->vacancy_id,
            ],
        ]);

        $text = "📩 *{$title}*\n\n{$message}";
        $keyboard = [
            'inline_keyboard' => [
                [['text' => $lang === 'ru' ? '👤 Посмотреть' : "👤 Ko'rish", 'url' => "{$this->appUrl}/recruiter/vacancies/{$application->vacancy_id}/applications"]],
                [['text' => '🌐 Mini App', 'url' => "https://t.me/{$this->botUsername}/app"]],
            ],
        ];

        $this->sendTelegram($user->telegram_id, $text, $keyboard);
    }

    /**
     * Notify employer when vacancy is moderated (approved/rejected)
     */
    public function notifyVacancyModerated(Vacancy $vacancy, bool $approved, ?string $reason = null): void
    {
        $vacancy->loadMissing('employer');
        $user = User::find($vacancy->employer->user_id);

        if (!$user || !$this->canNotify($user)) {
            return;
        }

        $lang = $user->language?->value ?? 'uz';

        if ($approved) {
            $title = $lang === 'ru' ? 'Вакансия одобрена' : 'Vakansiya tasdiqlandi';
            $message = $lang === 'ru'
                ? "Ваша вакансия \"{{$vacancy->title()}\" одобрена и опубликована!"
                : "\"{{$vacancy->title()}\" vakansiyangiz tasdiqlandi va e'lon qilindi!";
            $icon = '✅';
        } else {
            $title = $lang === 'ru' ? 'Вакансия отклонена' : 'Vakansiya rad etildi';
            $message = $lang === 'ru'
                ? "Ваша вакансия \"{{$vacancy->title()}\" отклонена."
                : "\"{{$vacancy->title()}\" vakansiyangiz rad etildi.";
            if ($reason) {
                $reasonLabel = $lang === 'ru' ? 'Причина' : 'Sabab';
                $message .= "\n\n📝 {$reasonLabel}: {$reason}";
            }
            $icon = '❌';
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => $approved ? 'vacancy_approved' : 'vacancy_rejected',
            'title' => $title,
            'message' => $message,
            'data' => [
                'vacancy_id' => $vacancy->id,
                'approved' => $approved,
                'reason' => $reason,
            ],
        ]);

        $text = "{$icon} *{$title}*\n\n{$message}";
        $keyboard = [
            'inline_keyboard' => [
                [['text' => $lang === 'ru' ? '📢 Мои вакансии' : "📢 Vakansiyalarim", 'url' => "{$this->appUrl}/recruiter/vacancies"]],
            ],
        ];

        $this->sendTelegram($user->telegram_id, $text, $keyboard);
    }

    /**
     * Notify worker about a matching vacancy
     */
    public function notifyMatchingVacancy(WorkerProfile $worker, Vacancy $vacancy, float $matchScore): void
    {
        $user = User::find($worker->user_id);

        if (!$user || !$this->canNotify($user)) {
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $score = round($matchScore);

        $title = $lang === 'ru' ? 'Подходящая вакансия' : 'Sizga mos vakansiya';
        $message = $lang === 'ru'
            ? "Вакансия \"{{$vacancy->title()}\" подходит вам на {$score}%"
            : "\"{{$vacancy->title()}\" vakansiyasi sizga {$score}% mos keladi";

        $salary = '';
        if ($vacancy->salary_type === 'negotiable') {
            $salary = $lang === 'ru' ? 'Договорная' : 'Kelishiladi';
        } elseif ($vacancy->salary_min && $vacancy->salary_max) {
            $currency = $lang === 'ru' ? 'сум' : "so'm";
            $salary = number_format($vacancy->salary_min) . ' - ' . number_format($vacancy->salary_max) . " {$currency}";
        }

        $details = "\n\n🏢 " . ($vacancy->employer->company_name ?? '-');
        $details .= "\n📍 " . ($vacancy->city ?? '-');
        if ($salary) {
            $details .= "\n💰 {$salary}";
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => 'matching_vacancy',
            'title' => $title,
            'message' => $message,
            'data' => [
                'vacancy_id' => $vacancy->id,
                'match_score' => $matchScore,
            ],
        ]);

        $text = "🔔 *{$title}*\n\n{$message}{$details}";
        $keyboard = [
            'inline_keyboard' => [
                [['text' => $lang === 'ru' ? '👁 Подробнее' : "👁 Batafsil", 'callback_data' => 'search:view:' . $vacancy->id]],
                [['text' => '🌐 Mini App', 'url' => "https://t.me/{$this->botUsername}/app"]],
            ],
        ];

        $this->sendTelegram($user->telegram_id, $text, $keyboard);
    }

    /**
     * Notify user about payment status
     */
    public function notifyPayment(User $user, string $status, int $amount): void
    {
        if (!$this->canNotify($user)) {
            return;
        }

        $lang = $user->language?->value ?? 'uz';
        $formattedAmount = number_format($amount, 0, '.', ' ');
        $currency = $lang === 'ru' ? 'сум' : "so'm";

        $titles = [
            'completed' => $lang === 'ru' ? 'Оплата успешна' : "To'lov muvaffaqiyatli",
            'failed' => $lang === 'ru' ? 'Оплата не прошла' : "To'lov amalga oshmadi",
        ];

        $messages = [
            'completed' => $lang === 'ru'
                ? "Оплата {$formattedAmount} {$currency} успешно завершена"
                : "{$formattedAmount} {$currency} to'lov muvaffaqiyatli amalga oshirildi",
            'failed' => $lang === 'ru'
                ? "Оплата {$formattedAmount} {$currency} не прошла. Попробуйте ещё раз"
                : "{$formattedAmount} {$currency} to'lov amalga oshmadi. Qayta urinib ko'ring",
        ];

        $title = $titles[$status] ?? ($lang === 'ru' ? 'Статус оплаты' : "To'lov holati");
        $message = $messages[$status] ?? '';
        $icon = $status === 'completed' ? '✅' : '❌';

        Notification::create([
            'user_id' => $user->id,
            'type' => 'payment_' . $status,
            'title' => $title,
            'message' => $message,
            'data' => ['amount' => $amount, 'status' => $status],
        ]);

        $text = "{$icon} *{$title}*\n\n{$message}";
        $this->sendTelegram($user->telegram_id, $text);
    }

    private function canNotify(User $user): bool
    {
        return $user->telegram_id
            && $user->notifications_enabled
            && !$user->is_blocked;
    }

    private function getStageIcon(ApplicationStage $stage): string
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
        };
    }

    private function sendTelegram(string|int $chatId, string $text, ?array $keyboard = null): void
    {
        if (!$this->botToken || !$chatId) {
            return;
        }

        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }

        try {
            Http::post("https://api.telegram.org/bot{$this->botToken}/sendMessage", $payload);
        } catch (\Throwable $e) {
            Log::warning('Telegram notification failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
