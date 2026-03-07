<?php

namespace App\Jobs;

use App\Models\Vacancy;
use App\Services\TelegramNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ModerateVacancyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Vacancy $vacancy
    ) {}

    public function handle(): void
    {
        // Check for spam
        if ($this->isSpam()) {
            $this->vacancy->update(['status' => 'closed']);
            app(TelegramNotificationService::class)
                ->notifyVacancyModerated($this->vacancy, false, 'Spam yoki taqiqlangan kontent aniqlandi');
            return;
        }

        // Check for duplicate
        if ($this->isDuplicate()) {
            // Flag but don't auto-reject
            $this->vacancy->update(['status' => 'pending']);
            return;
        }
    }

    private function isSpam(): bool
    {
        $text = $this->vacancy->title() . ' ' . $this->vacancy->description();
        $suspicious = ['casino', 'forex', 'mlm', 'piramida'];

        foreach ($suspicious as $word) {
            if (stripos($text, $word) !== false) return true;
        }

        return false;
    }

    private function isDuplicate(): bool
    {
        return Vacancy::where('employer_id', $this->vacancy->employer_id)
            ->where('id', '!=', $this->vacancy->id)
            ->where('title_uz', $this->vacancy->title_uz)
            ->where('created_at', '>=', now()->subDays(7))
            ->exists();
    }
}
