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
}
