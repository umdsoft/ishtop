<?php

namespace App\Telegram\Handlers;

use App\Models\User;
use App\Models\Vacancy;
use App\Telegram\Conversations\RegistrationConversation;
use App\Telegram\Keyboards\MainMenuKeyboard;
use App\Telegram\Keyboards\PersistentMenuKeyboard;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Properties\ParseMode;
use SergiX44\Nutgram\Telegram\Types\Command\MenuButtonDefault;
use SergiX44\Nutgram\Telegram\Types\Command\MenuButtonWebApp;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;
use SergiX44\Nutgram\Telegram\Types\WebApp\WebAppInfo;

class StartHandler
{
    public function __invoke(Nutgram $bot): void
    {
        $tgUser = $bot->user();

        // 1. Telegram ma'lumotlarini darhol saqlash/yangilash
        $user = User::updateOrCreate(
            ['telegram_id' => $tgUser->id],
            [
                'first_name'     => $tgUser->first_name,
                'last_name'      => $tgUser->last_name,
                'username'       => $tgUser->username,
                'last_active_at' => now(),
            ]
        );

        // Yangi yaratilgan user uchun default til — O'zbek
        if ($user->wasRecentlyCreated && !$user->language) {
            $user->forceFill(['language' => 'uz'])->saveQuietly();
        }

        // 2. Deep link payload'ni tekshirish
        $payload = $this->extractPayload($bot);

        // 3. Agar tasdiqlangan — deep link yoki welcome
        if ($user->is_verified) {
            $userLang = $user->language?->value ?? 'uz';

            // Mini app menu tugmasini o'rnatish (pastdagi chap tugma)
            $this->setMiniAppMenuButton($bot, $tgUser->id);

            // Deep link: vacancy
            if ($payload && str_starts_with($payload, 'v_')) {
                $vacancyId = substr($payload, 2);
                $this->showVacancyDeepLink($bot, $vacancyId, $userLang);
                return;
            }

            // Oddiy welcome — persistent keyboard o'rnatish
            $termsUrl = config('app.url') . '/terms';
            $welcome = $userLang === 'ru'
                ? "👋 Здравствуйте, {$user->first_name}!\n\n*KadrGo* — Найди работу не выходя из Telegram!\n\n📱 Нажмите *Открыть приложение* для поиска работы\n\n📄 Используя бот, вы соглашаетесь с [условиями использования]({$termsUrl})"
                : "👋 Assalomu alaykum, {$user->first_name}!\n\n*KadrGo* — Kadrlar harakatda! Ish va ishchi — bir joyda.\n\n📱 Ish qidirish uchun *Ilovani ochish* tugmasini bosing\n\n📄 Botdan foydalanib, siz [foydalanish shartlari]({$termsUrl})ga rozilik bildirasiz";

            $bot->sendMessage(
                text: $welcome,
                parse_mode: ParseMode::MARKDOWN_LEGACY,
                reply_markup: PersistentMenuKeyboard::make($userLang, $tgUser->id),
            );
            return;
        }

        // 4. Ro'yxatdan o'tmagan — mini app menu tugmasini olib tashlash
        $this->removeMiniAppMenuButton($bot, $tgUser->id);

        // RegistrationConversation boshlash
        $conversation = new RegistrationConversation();
        $conversation->referralCode = $payload && str_starts_with($payload, 'ref_')
            ? substr($payload, 4)
            : null;
        $conversation->begin($bot);
    }

    public function setLanguage(Nutgram $bot): void
    {
        $cb = $bot->callbackQuery();
        $lang = str_replace('lang:', '', $cb->data);

        $user = User::where('telegram_id', $bot->user()->id)->first();

        if ($user) {
            $user->update(['language' => $lang]);
            cache()->forget("user_lang:{$bot->user()->id}");
            $bot->answerCallbackQuery();

            $msg = $lang === 'ru'
                ? '✅ Язык изменён на Русский'
                : "✅ Til O'zbekchaga o'zgartirildi";

            $bot->editMessageText(
                text: $msg,
                message_id: $cb->message->message_id,
            );
            return;
        }

        $bot->answerCallbackQuery();
        $bot->sendMessage(text: "Avval /start buyrug'ini yuboring.");
    }

    protected function showVacancyDeepLink(Nutgram $bot, string $vacancyId, string $lang): void
    {
        $vacancy = Vacancy::with('employer')->find($vacancyId);

        if (!$vacancy || !$vacancy->isActive()) {
            $text = $lang === 'ru'
                ? "❌ Вакансия не найдена или больше не активна.\n\n📌 /menu — Главное меню"
                : "❌ Vakansiya topilmadi yoki faol emas.\n\n📌 /menu — Bosh menyu";

            $bot->sendMessage(
                text: $text,
                reply_markup: MainMenuKeyboard::make($lang, $bot->user()->id),
            );
            return;
        }

        $vacancy->increment('views_count');

        $isRu = $lang === 'ru';
        $salary = $vacancy->salaryFormatted();
        $workType = $vacancy->work_type?->label() ?? '-';
        $company = $vacancy->employer?->company_name ?? '-';
        $top = $vacancy->isTopActive() ? '🔥 TOP ' : '';
        $title = $vacancy->title($lang);

        $text = "{$top}📌 *{$title}*\n\n";
        $text .= "🏢 " . ($isRu ? 'Компания' : 'Kompaniya') . ": {$company}\n";
        $text .= "📂 " . ($isRu ? 'Категория' : 'Kategoriya') . ": {$vacancy->category}\n";
        $text .= "📍 " . ($isRu ? 'Город' : 'Shahar') . ": {$vacancy->city}\n";
        $text .= "💰 " . ($isRu ? 'Зарплата' : 'Maosh') . ": {$salary}\n";
        $text .= "🏢 " . ($isRu ? 'Тип работы' : 'Ish turi') . ": {$workType}\n";

        if ($vacancy->experience_required) {
            $text .= "⏱ " . ($isRu ? 'Опыт' : 'Tajriba') . ": {$vacancy->experience_required}\n";
        }

        $desc = $vacancy->description($lang) ?: $vacancy->description_uz;
        if ($desc) {
            $text .= "\n📝 *" . ($isRu ? 'Описание' : 'Tavsif') . ":*\n{$desc}\n";
        }

        if ($vacancy->requirements($lang)) {
            $text .= "\n📋 *" . ($isRu ? 'Требования' : 'Talablar') . ":*\n" . $vacancy->requirements($lang) . "\n";
        }

        $viewsLabel = $isRu ? 'Просмотры' : "Ko'rishlar";
        $appsLabel = $isRu ? 'Заявки' : 'Arizalar';
        $text .= "\n👁 {$viewsLabel}: {$vacancy->views_count} | 📝 {$appsLabel}: {$vacancy->applications_count}";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '📝 Подать заявку' : '📝 Ariza berish',
                    callback_data: "vacancy_apply:{$vacancy->id}"
                ),
                InlineKeyboardButton::make(
                    $isRu ? '🔗 Поделиться' : '🔗 Ulashish',
                    switch_inline_query: $title
                ),
            )
            ->addRow(
                InlineKeyboardButton::make(
                    $isRu ? '🔍 Поиск вакансий' : '🔍 Vakansiya qidirish',
                    callback_data: 'menu:search'
                ),
            );

        $bot->sendMessage(
            text: $text,
            parse_mode: ParseMode::MARKDOWN_LEGACY,
            reply_markup: $keyboard,
        );
    }

    /**
     * Mini app menu tugmasini o'rnatish (pastdagi chap tugma)
     */
    public static function setMiniAppMenuButton(Nutgram $bot, int $telegramId): void
    {
        if (!config('app.miniapp_enabled', false)) {
            return;
        }

        $appUrl = config('app.url');
        $miniappUrl = $appUrl . '/miniapp';
        $token = encrypt((string) $telegramId);
        $miniappUrl .= '?auth_token=' . urlencode($token);

        try {
            $bot->setChatMenuButton(
                chat_id: $telegramId,
                menu_button: new MenuButtonWebApp('KadrGo', new WebAppInfo($miniappUrl)),
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("setChatMenuButton error: " . $e->getMessage());
        }
    }

    /**
     * Mini app menu tugmasini olib tashlash (default holatga qaytarish)
     */
    public static function removeMiniAppMenuButton(Nutgram $bot, int $telegramId): void
    {
        try {
            $bot->setChatMenuButton(
                chat_id: $telegramId,
                menu_button: new MenuButtonDefault(),
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("removeChatMenuButton error: " . $e->getMessage());
        }
    }

    private function extractPayload(Nutgram $bot): ?string
    {
        $message = $bot->message();
        if (!$message) {
            return null;
        }
        $text = $message->text ?? '';
        if (preg_match('/\/start\s+(\S+)/', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
