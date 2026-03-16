<?php

namespace App\Telegram\Conversations;

use App\Models\User;
use App\Services\FileUploadService;
use App\Services\LinkedInPdfParserService;
use SergiX44\Nutgram\Conversations\Conversation;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class LinkedInPdfConversation extends Conversation
{
    protected string $lang = 'uz';

    public function start(Nutgram $bot): void
    {
        $user = User::where('telegram_id', $bot->user()->id)->first();
        $this->lang = $user?->language?->value ?? 'uz';

        $text = $this->lang === 'ru'
            ? "📄 *Импорт из LinkedIn PDF*\n\nОтправьте PDF файл вашего LinkedIn профиля.\n\n📌 *Как скачать:*\n1. Откройте LinkedIn\n2. Перейдите в свой профиль\n3. Нажмите «More» → «Save to PDF»\n4. Отправьте файл сюда\n\nОтправьте /cancel для отмены."
            : "📄 *LinkedIn PDF import*\n\nLinkedIn profilingizning PDF faylini yuboring.\n\n📌 *Qanday yuklab olish:*\n1. LinkedIn ni oching\n2. Profilingizga o'ting\n3. \"More\" → \"Save to PDF\" bosing\n4. Faylni bu yerga yuboring\n\nBekor qilish uchun /cancel yuboring.";

        $bot->sendMessage(text: $text, parse_mode: ParseMode::MARKDOWN_LEGACY);
        $this->next('handleDocument');
    }

    public function handleDocument(Nutgram $bot): void
    {
        $message = $bot->message();
        if (!$message) {
            $this->end();
            return;
        }

        $text = $message->text ?? '';
        if ($text === '/cancel') {
            $bot->sendMessage(text: $this->lang === 'ru' ? '❌ Отменено.' : '❌ Bekor qilindi.');
            $this->end();
            return;
        }

        $document = $message->document;
        if (!$document) {
            $bot->sendMessage(text: $this->lang === 'ru'
                ? '❌ Пожалуйста, отправьте PDF файл.'
                : '❌ PDF fayl yuboring.');
            return;
        }

        if ($document->mime_type !== 'application/pdf') {
            $bot->sendMessage(text: $this->lang === 'ru'
                ? '❌ Только PDF файлы. Отправьте LinkedIn PDF.'
                : '❌ Faqat PDF fayllar. LinkedIn PDF yuboring.');
            return;
        }

        $bot->sendMessage(text: $this->lang === 'ru'
            ? '⏳ Файл обрабатывается... Пожалуйста, подождите.'
            : '⏳ Fayl tahlil qilinmoqda... Kuting.');

        $fileService = app(FileUploadService::class);
        $uploaded = $fileService->uploadFromTelegram($document->file_id, 'linkedin_pdfs');

        if (!$uploaded) {
            $bot->sendMessage(text: $this->lang === 'ru'
                ? '❌ Не удалось загрузить файл.'
                : '❌ Faylni yuklab bo\'lmadi.');
            $this->end();
            return;
        }

        $parserService = app(LinkedInPdfParserService::class);
        $parsedData = $parserService->parse($uploaded['path']);

        if (empty($parsedData)) {
            $bot->sendMessage(text: $this->lang === 'ru'
                ? '❌ Не удалось разобрать PDF. Убедитесь, что это LinkedIn PDF.'
                : '❌ PDF ni tahlil qilib bo\'lmadi. Bu LinkedIn PDF ekanligiga ishonch hosil qiling.');
            $this->end();
            return;
        }

        $mapped = $parserService->mapToWorkerProfile($parsedData);
        $preview = $this->buildPreview($mapped, $parsedData);

        $user = User::where('telegram_id', $bot->user()->id)->first();
        cache()->put("linkedin_import_{$user->id}", [
            'mapped' => $mapped,
            'raw' => $parsedData,
        ], now()->addMinutes(30));

        $bot->sendMessage(
            text: $preview,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make(
                        '✅ ' . ($this->lang === 'ru' ? 'Применить все' : 'Hammasini qo\'llash'),
                        callback_data: 'linkedin_import:apply_all'
                    )
                )
                ->addRow(
                    InlineKeyboardButton::make(
                        '❌ ' . ($this->lang === 'ru' ? 'Отмена' : 'Bekor qilish'),
                        callback_data: 'linkedin_import:cancel'
                    )
                ),
        );

        $this->end();
    }

    private function buildPreview(array $mapped, array $raw): string
    {
        $lines = [$this->lang === 'ru' ? "📋 *Найденные данные:*\n" : "📋 *Topilgan ma'lumotlar:*\n"];

        $fieldLabels = $this->lang === 'ru'
            ? [
                'full_name' => 'Имя',
                'specialty' => 'Специальность',
                'city' => 'Город',
                'education_level' => 'Образование',
                'experience_years' => 'Опыт',
                'skills' => 'Навыки',
                'bio' => 'О себе',
                'linkedin_url' => 'LinkedIn',
            ]
            : [
                'full_name' => 'Ism',
                'specialty' => 'Mutaxassislik',
                'city' => 'Shahar',
                'education_level' => 'Ta\'lim',
                'experience_years' => 'Tajriba',
                'skills' => 'Ko\'nikmalar',
                'bio' => 'O\'zi haqida',
                'linkedin_url' => 'LinkedIn',
            ];

        foreach ($mapped as $key => $value) {
            $label = $fieldLabels[$key] ?? $key;
            if ($key === 'skills' && is_array($value)) {
                $value = implode(', ', array_slice($value, 0, 8));
                if (count($mapped['skills'] ?? []) > 8) {
                    $value .= '...';
                }
            }
            if ($key === 'experience_years') {
                $value .= $this->lang === 'ru' ? ' лет' : ' yil';
            }
            if ($key === 'bio') {
                $value = mb_substr($value, 0, 100) . '...';
            }
            $lines[] = "• *{$label}:* {$value}";
        }

        $expCount = count($raw['work_experience'] ?? []);
        $eduCount = count($raw['education'] ?? []);
        if ($expCount) {
            $lines[] = "\n" . ($this->lang === 'ru'
                ? "💼 Опыт работы: {$expCount} позиций"
                : "💼 Ish tajribasi: {$expCount} ta lavozim");
        }
        if ($eduCount) {
            $lines[] = $this->lang === 'ru'
                ? "🎓 Образование: {$eduCount} записей"
                : "🎓 Ta'lim: {$eduCount} ta yozuv";
        }

        $lines[] = "\n" . ($this->lang === 'ru'
            ? "_Нажмите «Применить все» чтобы обновить профиль_"
            : "_\"Hammasini qo'llash\" bosib profilni yangilang_");

        return implode("\n", $lines);
    }
}
