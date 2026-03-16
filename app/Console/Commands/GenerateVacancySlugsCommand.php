<?php

namespace App\Console\Commands;

use App\Models\Vacancy;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateVacancySlugsCommand extends Command
{
    protected $signature = 'vacancies:generate-slugs';
    protected $description = 'Generate slugs for existing vacancies that have no slug';

    public function handle(): int
    {
        $count = 0;

        Vacancy::withTrashed()
            ->whereNull('slug')
            ->chunkById(200, function ($vacancies) use (&$count) {
                foreach ($vacancies as $vacancy) {
                    $baseSlug = Str::slug($vacancy->title_uz ?: $vacancy->title_ru ?: 'vacancy');
                    if ($baseSlug === '') {
                        $baseSlug = 'vacancy';
                    }
                    $slug = $baseSlug . '-' . substr($vacancy->id, 0, 8);

                    // Unikal bo'lishini ta'minlash
                    $existing = Vacancy::withTrashed()->where('slug', $slug)->where('id', '!=', $vacancy->id)->exists();
                    if ($existing) {
                        $slug = $baseSlug . '-' . substr($vacancy->id, 0, 13);
                    }

                    $vacancy->update(['slug' => $slug]);
                    $count++;
                }
            });

        $this->info("Generated slugs for {$count} vacancies.");

        return self::SUCCESS;
    }
}
