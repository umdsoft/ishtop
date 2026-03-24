<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Console\Command;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class BroadcastLaunchCountdown extends Command
{
    protected $signature = 'bot:broadcast-launch {--test : Send only to first user for testing}';
    protected $description = 'Send launch countdown message with profile CTA to all verified users';

    public function handle(Nutgram $bot): int
    {
        $completeCount = WorkerProfile::whereNotNull('specialty')
            ->where('specialty', '!=', '')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->count();

        $query = User::where('is_verified', true)
            ->where('is_blocked', false)
            ->whereNotNull('telegram_id');

        if ($this->option('test')) {
            $query->limit(1);
        }

        $users = $query->get();
        $botUsername = config('nutgram.bot_username', env('TELEGRAM_BOT_USERNAME', 'kadrgobot'));

        $this->info("Sending launch countdown to {$users->count()} users... [complete profiles: {$completeCount}]");

        $sent = 0;
        $failed = 0;

        foreach ($users as $user) {
            $lang = $user->language?->value ?? 'uz';
            $text = $this->buildText($lang, $user->first_name, $completeCount);
            $keyboard = $this->buildKeyboard($lang, $user, $botUsername);

            try {
                $bot->sendMessage(
                    text: $text,
                    chat_id: $user->telegram_id,
                    parse_mode: ParseMode::MARKDOWN_LEGACY,
                    reply_markup: $keyboard,
                );
                $sent++;
            } catch (\Throwable $e) {
                $failed++;
                if (str_contains($e->getMessage(), 'Forbidden') || str_contains($e->getMessage(), '403')) {
                    $user->update(['is_blocked' => true]);
                }
                $this->warn("Failed for user #{$user->id}: {$e->getMessage()}");
            }

            usleep(40000); // 40ms delay — ~25 msg/sec, stays within Telegram limits
        }

        $this->info("Done! Sent: {$sent}, Failed: {$failed}");

        return self::SUCCESS;
    }

    private function buildText(string $lang, ?string $firstName, int $n): string
    {
        $name = $firstName ?: ($lang === 'ru' ? 'друг' : 'do\'st');

        if ($lang === 'ru') {
            return "Уважаемый *{$name}*, до официального запуска KadrGo осталось *5 дней*\.

Работодатели уже сейчас просматривают кандидатов и через 5 дней начнут делать выбор\.

Кандидаты с заполненным профилем — *видны в первую очередь*\. Заполните профиль сейчас и будьте готовы\.

⚡ Уже *{$n}* кандидатов заполнили профиль\.

💡 Есть друг, который ищет работу? Перешлите ему это сообщение — он тоже зарегистрируется заранее и будет ближе к трудоустройству\.";
        }

        return "Hurmatli *{$name}*, KadrGo rasmiy ishga tushishiga *5 kun* qoldi\.

Ish beruvchilar hozirdan nomzodlarni ko'rib chiqmoqda va 5 kundan keyin tanlab olishadi\.

Profili to'liq bo'lganlar — *birinchi navbatda ko'rinadi*\. Hozir profilingizni to'ldiring va tayyor bo'ling\.

⚡ Hozircha *{$n}* ta nomzod profilini to'ldirdi\.

💡 Ish qidirayotgan do'stingiz bormi? Habarni unga yuboring — u ham erta ro'yxatdan o'tib, ish topishga yaqinlashadi\.";
    }

    private function buildKeyboard(string $lang, User $user, string $botUsername): InlineKeyboardMarkup
    {
        $appUrl = rtrim(config('app.url'), '/');
        $token = encrypt((string) $user->telegram_id);
        $miniAppUrl = $appUrl . '/miniapp?auth_token=' . urlencode($token);

        $referralCode = $user->referral_code;
        $referralLink = "https://t.me/{$botUsername}?start=ref_{$referralCode}";
        $shareText = $lang === 'ru'
            ? 'Присоединяйся к KadrGo — найди работу быстрее!'
            : "KadrGo ga qo'shil — ishni tezroq top!";
        $shareUrl = 'https://t.me/share/url?url=' . urlencode($referralLink) . '&text=' . urlencode($shareText);

        return InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '📝 Заполнить профиль' : '📝 Profilni to\'ldirish',
                    web_app: new WebAppInfo($miniAppUrl)
                )
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $lang === 'ru' ? '🤝 Пригласить друга' : '🤝 Do\'stni taklif qilish',
                    url: $shareUrl
                )
            );
    }
}
