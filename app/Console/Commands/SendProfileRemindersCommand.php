<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendProfileRemindersCommand extends Command
{
    protected $signature = 'reminders:incomplete-profiles';
    protected $description = 'Send reminders to users who haven\'t completed their profile';

    /**
     * Reminder schedule: send on day 2, 4, 7, 12, then every 3 days.
     */
    private const SCHEDULE = [2, 4, 7, 12];
    private const RECURRING_INTERVAL = 3;

    public function handle(): int
    {
        $botToken = config('nutgram.token');

        if (!$botToken) {
            $this->error('Bot token not configured');
            return self::FAILURE;
        }

        // Users with incomplete profiles:
        // - verified, not blocked, has telegram_id
        // - no WorkerProfile OR profile missing specialty/city
        $users = User::where('is_verified', true)
            ->where('is_blocked', false)
            ->whereNotNull('telegram_id')
            ->where(function ($q) {
                $q->whereDoesntHave('workerProfile')
                    ->orWhereHas('workerProfile', function ($q) {
                        $q->where(function ($q) {
                            $q->whereNull('specialty')
                                ->orWhere('specialty', '')
                                ->orWhereNull('city')
                                ->orWhere('city', '');
                        });
                    });
            })
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            $daysSinceRegistration = (int) $user->created_at->diffInDays(now());

            // Too early — wait at least 2 days
            if ($daysSinceRegistration < 2) {
                continue;
            }

            // Check if today is a scheduled reminder day
            if (!$this->shouldSendToday($user, $daysSinceRegistration)) {
                $skipped++;
                continue;
            }

            $lang = $user->language?->value ?? 'uz';
            $name = $user->first_name ?: ($lang === 'ru' ? 'друг' : 'do\'st');
            $reminderNumber = $this->getReminderNumber($user);

            $message = $this->buildMessage($name, $lang, $reminderNumber);

            // Save notification to DB
            Notification::create([
                'user_id' => $user->id,
                'type' => 'profile_reminder',
                'title' => $lang === 'ru' ? 'Завершите анкету' : 'Anketani to\'ldiring',
                'message' => strip_tags(str_replace(['*', '_'], '', $message)),
                'data' => [
                    'reminder_number' => $reminderNumber,
                    'days_since_registration' => $daysSinceRegistration,
                ],
            ]);

            // Send Telegram message — callback opens resume builder conversation
            $keyboard = [
                'inline_keyboard' => [
                    [['text' => $lang === 'ru' ? '📝 Заполнить анкету' : '📝 Anketani to\'ldirish', 'callback_data' => 'resume:create']],
                ],
            ];

            $this->sendTelegram($botToken, $user->telegram_id, $message, $keyboard);
            $sent++;

            // Rate limiting — avoid Telegram API throttle
            usleep(100_000); // 100ms
        }

        $this->info("Sent: {$sent}, Skipped: {$skipped}, Total incomplete: {$users->count()}");

        return self::SUCCESS;
    }

    /**
     * Determine if we should send a reminder today based on the schedule.
     */
    private function shouldSendToday(User $user, int $daysSinceRegistration): bool
    {
        // Check last reminder sent
        $lastReminder = Notification::where('user_id', $user->id)
            ->where('type', 'profile_reminder')
            ->latest()
            ->first();

        if (!$lastReminder) {
            // Never sent — first scheduled day is day 2
            return $daysSinceRegistration >= 2;
        }

        $daysSinceLastReminder = (int) $lastReminder->created_at->diffInDays(now());
        $reminderCount = Notification::where('user_id', $user->id)
            ->where('type', 'profile_reminder')
            ->count();

        // Get required gap for next reminder
        $requiredGap = $this->getRequiredGap($reminderCount);

        return $daysSinceLastReminder >= $requiredGap;
    }

    /**
     * Get how many days must pass before next reminder.
     * Schedule: 2, 4, 7, 12, then +3 each time
     * Gaps:    2, 2, 3, 5, 3, 3, 3...
     */
    private function getRequiredGap(int $sentCount): int
    {
        $gaps = [2, 2, 3, 5]; // day 0→2, 2→4, 4→7, 7→12

        if ($sentCount < count($gaps)) {
            return $gaps[$sentCount];
        }

        return self::RECURRING_INTERVAL;
    }

    private function getReminderNumber(User $user): int
    {
        return Notification::where('user_id', $user->id)
            ->where('type', 'profile_reminder')
            ->count() + 1;
    }

    /**
     * Build professional marketing message based on reminder number.
     */
    private function buildMessage(string $name, string $lang, int $reminderNumber): string
    {
        $messages = $lang === 'ru'
            ? $this->russianMessages($name)
            : $this->uzbekMessages($name);

        // Cycle through messages if more reminders than message variants
        $index = min($reminderNumber, count($messages)) - 1;

        return $messages[$index];
    }

    private function uzbekMessages(string $name): array
    {
        return [
            // 1st reminder (day 2) — Gentle welcome
            "Assalomu alaykum, *{$name}*! 👋\n\n"
            . "KadrGo platformasiga xush kelibsiz! Biz sizga eng mos ish o'rinlarini topishga tayyormiz.\n\n"
            . "Buning uchun faqat bir qadam qoldi — *anketangizni to'ldiring* va ish beruvchilar sizni topsin!\n\n"
            . "⏱ Atigi 2 daqiqa vaqtingizni oladi.",

            // 2nd reminder (day 4) — Benefits
            "Salom, *{$name}*! 🌟\n\n"
            . "Bilasizmi, KadrGo orqali har kuni yangi ish o'rinlari e'lon qilinmoqda?\n\n"
            . "Anketangizni to'ldirsangiz:\n"
            . "✅ Sizga mos vakansiyalar avtomatik yuboriladi\n"
            . "✅ Ish beruvchilar sizni o'zlari topadi\n"
            . "✅ Arizalarni 1 bosish bilan yuborasiz\n\n"
            . "📝 *Hoziroq to'ldiring — imkoniyatni boy bermang!*",

            // 3rd reminder (day 7) — Social proof
            "Salom, *{$name}*! 💼\n\n"
            . "Bizning platformada yuzlab mutaxassislar allaqachon ish topishdi.\n\n"
            . "Siz ham ulardan biri bo'lishingiz mumkin! Kerak bo'lgan narsa — *anketangizni to'ldirish*.\n\n"
            . "🎯 Mutaxassisligingiz, tajribangiz va shaharingizni kiriting — biz sizga mos ish topamiz.\n\n"
            . "Boshlaymizmi? 👇",

            // 4th reminder (day 12) — Personal appeal
            "Salom, *{$name}*! 🤝\n\n"
            . "Anketangiz hali ham to'ldirilmagan. Sizga kerakli ish o'rnini topishda yordam berishni xohlaymiz.\n\n"
            . "Har bir to'ldirilgan anketa — bu yangi imkoniyat eshigi!\n\n"
            . "💡 Ko'pchilik foydalanuvchilarimiz anketani to'ldirgandan so'ng bir hafta ichida ish taklifi oladi.\n\n"
            . "Vaqtingizni boy bermang — *hoziroq boshlang!* 👇",

            // 5th+ reminder (recurring) — Motivational
            "*{$name}*, yangi ish imkoniyatlarini qidirmoqdasiz? 🔍\n\n"
            . "KadrGo platformasi sizni ish beruvchilar bilan bog'lashga tayyor — faqat anketangiz to'ldirilishi kerak.\n\n"
            . "📋 2 daqiqada to'ldiring va ish bozoriga chiqing!\n\n"
            . "Sizni kutayotgan ish o'rinlari bor! 👇",
        ];
    }

    private function russianMessages(string $name): array
    {
        return [
            // 1st reminder (day 2)
            "Здравствуйте, *{$name}*! 👋\n\n"
            . "Добро пожаловать на платформу KadrGo! Мы готовы помочь вам найти подходящую работу.\n\n"
            . "Остался всего один шаг — *заполните анкету*, и работодатели смогут вас найти!\n\n"
            . "⏱ Это займёт всего 2 минуты.",

            // 2nd reminder (day 4)
            "Привет, *{$name}*! 🌟\n\n"
            . "Знаете ли вы, что на KadrGo ежедневно публикуются новые вакансии?\n\n"
            . "Заполнив анкету, вы получите:\n"
            . "✅ Автоматический подбор подходящих вакансий\n"
            . "✅ Работодатели сами найдут ваш профиль\n"
            . "✅ Подача заявки в один клик\n\n"
            . "📝 *Заполните сейчас — не упустите возможность!*",

            // 3rd reminder (day 7)
            "Привет, *{$name}*! 💼\n\n"
            . "Сотни специалистов уже нашли работу через нашу платформу.\n\n"
            . "Вы тоже можете стать одним из них! Всё что нужно — *заполнить анкету*.\n\n"
            . "🎯 Укажите специальность, опыт и город — мы подберём для вас лучшие предложения.\n\n"
            . "Начнём? 👇",

            // 4th reminder (day 12)
            "Здравствуйте, *{$name}*! 🤝\n\n"
            . "Ваша анкета всё ещё не заполнена. Мы хотим помочь вам найти нужную работу.\n\n"
            . "Каждая заполненная анкета — это дверь к новым возможностям!\n\n"
            . "💡 Большинство наших пользователей получают предложения о работе в течение недели после заполнения анкеты.\n\n"
            . "Не теряйте время — *начните прямо сейчас!* 👇",

            // 5th+ reminder (recurring)
            "*{$name}*, ищете новые возможности для работы? 🔍\n\n"
            . "Платформа KadrGo готова связать вас с работодателями — осталось только заполнить анкету.\n\n"
            . "📋 Заполните за 2 минуты и выйдите на рынок труда!\n\n"
            . "Вас ждут подходящие вакансии! 👇",
        ];
    }

    private function sendTelegram(string $botToken, int $chatId, string $text, ?array $keyboard = null): void
    {
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }

        try {
            Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", $payload);
        } catch (\Throwable $e) {
            Log::warning('Profile reminder failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
