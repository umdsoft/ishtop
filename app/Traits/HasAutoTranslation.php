<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait HasAutoTranslation
{
    /**
     * Vakansiya maydonlarini avtomatik tarjima qilish.
     * Faqat bitta tilda to'ldirilgan bo'lsa, ikkinchisiga tarjima qiladi.
     * Controller da $this->aiService (AiService) mavjud bo'lishi kerak.
     */
    protected function autoTranslate(array $data): array
    {
        $from = $data['language'] ?? null;

        if (!$from) {
            $hasUz = !empty($data['title_uz']) || !empty($data['description_uz']);
            $hasRu = !empty($data['title_ru']) || !empty($data['description_ru']);
            $from = $hasRu && !$hasUz ? 'ru' : 'uz';
        }

        $to = $from === 'uz' ? 'ru' : 'uz';

        $fieldsToTranslate = [];
        foreach (['title', 'description', 'requirements', 'responsibilities'] as $field) {
            $srcKey = "{$field}_{$from}";
            $dstKey = "{$field}_{$to}";
            if (!empty($data[$srcKey]) && empty($data[$dstKey])) {
                $fieldsToTranslate[$field] = $data[$srcKey];
            }
        }

        if (empty($fieldsToTranslate)) {
            return $data;
        }

        try {
            $translated = $this->aiService->translateVacancy($fieldsToTranslate, $from, $to);
            foreach ($translated as $field => $value) {
                $dstKey = "{$field}_{$to}";
                if (!empty($value)) {
                    $data[$dstKey] = $value;
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-translate failed: ' . $e->getMessage());
        }

        return $data;
    }
}
