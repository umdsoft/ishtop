<?php

namespace App\Console\Commands;

use App\Enums\VacancyStatus;
use App\Models\Vacancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendIncompleteVacancyReminderCommand extends Command
{
    protected $signature = 'reminders:incomplete-vacancies {--test : Send only to first employer for testing}';
    protected $description = 'Remind employers who have DRAFT or long-pending vacancies to complete them';

    public function handle(): int
    {
        $botToken = config('nutgram.token');

        if (!$botToken) {
            $this->error('Telegram bot token not configured.');
            return self::FAILURE;
        }

        $sent  = 0;
        $skipped = 0;

        // --- 1. DRAFT vacancies older than 1 day ---
        $draftQuery = Vacancy::where('status', VacancyStatus::DRAFT)
            ->where('created_at', '<=', now()->subDay())
            ->with('employer.user');

        if ($this->option('test')) {
            $draftQuery->limit(1);
        }

        foreach ($draftQuery->get() as $vacancy) {
            $user = $vacancy->employer?->user;
            if (!$user || !$user->telegram_id || $user->is_blocked) {
                $skipped++;
                continue;
            }

            $lang = $user->language?->value ?? 'uz';
            $this->sendTelegram($botToken, $user->telegram_id, $this->draftText($lang, $vacancy->title ?? ''));
            $sent++;
            usleep(60_000); // 60ms rate limit
        }

        // --- 2. PENDING vacancies older than 3 days (employer may have forgotten) ---
        $pendingQuery = Vacancy::where('status', VacancyStatus::PENDING)
            ->where('created_at', '<=', now()->subDays(3))
            ->with('employer.user');

        if ($this->option('test')) {
            $pendingQuery->limit(1);
        }

        foreach ($pendingQuery->get() as $vacancy) {
            $user = $vacancy->employer?->user;
            if (!$user || !$user->telegram_id || $user->is_blocked) {
                $skipped++;
                continue;
            }

            $lang = $user->language?->value ?? 'uz';
            $this->sendTelegram($botToken, $user->telegram_id, $this->pendingText($lang, $vacancy->title ?? ''));
            $sent++;
            usleep(60_000);
        }

        $this->info("Done! Sent: {$sent}, Skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function draftText(string $lang, string $title): string
    {
        $t = $title ? "\"$title\"" : '';

        if ($lang === 'ru') {
            return "⚠️ *Незавершённая вакансия*\n\n"
                . "Вакансия {$t} сохранена как черновик, но ещё не опубликована.\n\n"
                . "Завершите заполнение и отправьте на модерацию — работодатели уже ищут кандидатов!\n\n"
                . "👉 Откройте панель рекрутера, чтобы продолжить.";
        }

        return "⚠️ *To'ldirilmagan vakansiya*\n\n"
            . "Vakansiya {$t} qoralama sifatida saqlangan, lekin hali e'lon qilinmagan.\n\n"
            . "To'ldirishni yakunlang va moderatsiyaga yuboring — nomzodlar sizni kutmoqda!\n\n"
            . "👉 Davom etish uchun recruiter panelingizni oching.";
    }

    private function pendingText(string $lang, string $title): string
    {
        $t = $title ? "\"$title\"" : '';

        if ($lang === 'ru') {
            return "🕐 *Вакансия на модерации*\n\n"
                . "Вакансия {$t} находится на проверке уже более 3 дней.\n\n"
                . "Наша команда скоро её рассмотрит. Если хотите ускорить процесс — свяжитесь с поддержкой.";
        }

        return "🕐 *Vakansiya moderatsiyada*\n\n"
            . "Vakansiya {$t} 3 kundan ko'proq moderatsiyada turibdi.\n\n"
            . "Jamoamiz tez orada ko'rib chiqadi. Tezlashtirish uchun qo'llab-quvvatlash xizmatiga murojaat qiling.";
    }

    private function sendTelegram(string $token, int|string $chatId, string $text): void
    {
        try {
            $response = Http::post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id'    => $chatId,
                'text'       => $text,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->status() === 403) {
                \App\Models\User::where('telegram_id', $chatId)->update(['is_blocked' => true]);
            }
        } catch (\Throwable $e) {
            Log::warning('IncompleteVacancyReminder send failed', [
                'chat_id' => $chatId,
                'error'   => $e->getMessage(),
            ]);
        }
    }
}
