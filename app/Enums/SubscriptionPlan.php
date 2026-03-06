<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case FREE = 'free';
    case WORKER_PREMIUM = 'worker_premium';
    case BUSINESS = 'business';
    case RECRUITER_PRO = 'recruiter_pro';
    case AGENCY = 'agency';
    case CORPORATE = 'corporate';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Bepul',
            self::WORKER_PREMIUM => 'Ishchi Premium',
            self::BUSINESS => 'Biznes',
            self::RECRUITER_PRO => 'Recruiter Pro',
            self::AGENCY => 'Agency',
            self::CORPORATE => 'Korporativ',
        };
    }

    public function price(): int
    {
        return match ($this) {
            self::FREE => 0,
            self::WORKER_PREMIUM => 25_000,
            self::BUSINESS => 99_000,
            self::RECRUITER_PRO => 499_000,
            self::AGENCY => 999_000,
            self::CORPORATE => 249_000,
        };
    }

    public function maxVacancies(): ?int
    {
        return match ($this) {
            self::FREE => 1,
            self::WORKER_PREMIUM => null,
            self::BUSINESS => 10,
            self::RECRUITER_PRO => null,
            self::AGENCY => null,
            self::CORPORATE => null,
        };
    }

    /**
     * Full limits definition for each plan.
     */
    public function limits(): array
    {
        return match ($this) {
            self::FREE => [
                'max_vacancies' => 1,
                'max_questionnaires' => 1,
                'max_template_messages' => 3,
                'ai_translation' => true,
                'talent_pool' => false,
                'analytics' => false,
                'vacancy_top' => false,
                'vacancy_urgent' => false,
            ],
            self::WORKER_PREMIUM => [
                'max_vacancies' => null,
                'max_questionnaires' => null,
                'max_template_messages' => null,
                'ai_translation' => false,
                'talent_pool' => false,
                'analytics' => false,
                'vacancy_top' => false,
                'vacancy_urgent' => false,
            ],
            self::BUSINESS => [
                'max_vacancies' => 10,
                'max_questionnaires' => 5,
                'max_template_messages' => 10,
                'ai_translation' => true,
                'talent_pool' => false,
                'analytics' => true,
                'vacancy_top' => true,
                'vacancy_urgent' => false,
            ],
            self::RECRUITER_PRO => [
                'max_vacancies' => null,
                'max_questionnaires' => null,
                'max_template_messages' => null,
                'ai_translation' => true,
                'talent_pool' => true,
                'analytics' => true,
                'vacancy_top' => true,
                'vacancy_urgent' => true,
            ],
            self::AGENCY => [
                'max_vacancies' => null,
                'max_questionnaires' => null,
                'max_template_messages' => null,
                'ai_translation' => true,
                'talent_pool' => true,
                'analytics' => true,
                'vacancy_top' => true,
                'vacancy_urgent' => true,
            ],
            self::CORPORATE => [
                'max_vacancies' => null,
                'max_questionnaires' => null,
                'max_template_messages' => null,
                'ai_translation' => true,
                'talent_pool' => true,
                'analytics' => true,
                'vacancy_top' => true,
                'vacancy_urgent' => true,
            ],
        };
    }

    /**
     * Human-readable features list for display.
     */
    public function features(): array
    {
        return match ($this) {
            self::FREE => [
                '1 ta vakansiya',
                '1 ta savolnoma',
                '3 ta xabar shabloni',
            ],
            self::BUSINESS => [
                '10 ta vakansiya',
                '5 ta savolnoma',
                '10 ta xabar shabloni',
                'AI tarjima',
                'Asosiy analitika',
                'Vakansiyani ko\'tarish',
            ],
            self::RECRUITER_PRO => [
                'Cheksiz vakansiyalar',
                'Cheksiz savolnomalar',
                'AI tarjima',
                'Talent Pool',
                'Kengaytirilgan analitika',
                'Vakansiyani ko\'tarish',
                'Shoshilinch e\'lon',
            ],
            self::AGENCY => [
                'Barcha Pro imkoniyatlari',
                'Jamoa boshqaruvi',
                'API kirish',
                'White label',
                'Shaxsiy menejer',
            ],
            default => [],
        };
    }

    /**
     * Check if a plan is for recruiter (not worker).
     */
    public function isRecruiterPlan(): bool
    {
        return in_array($this, [self::FREE, self::BUSINESS, self::RECRUITER_PRO, self::AGENCY, self::CORPORATE]);
    }
}
