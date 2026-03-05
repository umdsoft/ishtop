<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiService
{
    public function generateQuestions(string $vacancyDescription, int $count = 5): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 2000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Vakansiya tavsifi: {$vacancyDescription}\n\nShu vakansiya uchun {$count} ta screening savol yarat. JSON formatda qaytar:\n[{\"type\": \"knockout|single_choice|multi_select|number_range|open_text\", \"text_uz\": \"...\", \"weight\": 0-100, \"is_knockout\": bool, \"options\": [{\"value\": \"opt_a\", \"label_uz\": \"...\"}]}]",
                ],
            ],
        ]);

        if ($response->successful()) {
            $content = $response->json('content.0.text', '[]');
            return json_decode($content, true) ?? [];
        }

        return [];
    }

    public function scoreOpenText(string $question, string $answer, string $context = ''): array
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 500,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Savol: {$question}\nJavob: {$answer}\nKontekst: {$context}\n\nBu javobni 0-100 ball bilan baholab, qisqa izoh yoz. JSON: {\"score\": 0-100, \"comment\": \"...\"}",
                ],
            ],
        ]);

        if ($response->successful()) {
            $content = $response->json('content.0.text', '{}');
            return json_decode($content, true) ?? ['score' => 0, 'comment' => ''];
        }

        return ['score' => 0, 'comment' => 'AI xatolik'];
    }

    public function generateCandidateSummary(array $profileData): string
    {
        $response = Http::withHeaders([
            'x-api-key' => config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => 'claude-haiku-4-5-20251001',
            'max_tokens' => 300,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Kandidat profili: " . json_encode($profileData) . "\n\n2-3 qatorli qisqa xulosa yoz (o'zbek tilida). Kuchli va zaif tomonlarni ko'rsat.",
                ],
            ],
        ]);

        if ($response->successful()) {
            return $response->json('content.0.text', '');
        }

        return '';
    }
}
