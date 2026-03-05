<?php

namespace App\Enums;

enum VerificationLevel: string
{
    case NEW = 'new';
    case CONFIRMED = 'confirmed';
    case VERIFIED = 'verified';
    case TOP = 'top';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Yangi',
            self::CONFIRMED => 'Tasdiqlangan',
            self::VERIFIED => 'Verified',
            self::TOP => 'Top Employer',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'gray',
            self::CONFIRMED => 'info',
            self::VERIFIED => 'success',
            self::TOP => 'warning',
        };
    }

    public function maxVacancies(): ?int
    {
        return match ($this) {
            self::NEW => 1,
            self::CONFIRMED => null,
            self::VERIFIED => null,
            self::TOP => null,
        };
    }
}
