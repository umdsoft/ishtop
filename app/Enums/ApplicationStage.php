<?php

namespace App\Enums;

enum ApplicationStage: string
{
    case NEW = 'new';
    case REVIEWED = 'reviewed';
    case SHORTLISTED = 'shortlisted';
    case INTERVIEW = 'interview';
    case OFFERED = 'offered';
    case HIRED = 'hired';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Yangi',
            self::REVIEWED => 'Ko\'rilgan',
            self::SHORTLISTED => 'Tanlangan',
            self::INTERVIEW => 'Intervyu',
            self::OFFERED => 'Taklif',
            self::HIRED => 'Qabul qilindi',
            self::REJECTED => 'Rad etildi',
            self::WITHDRAWN => 'Bekor qilingan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'info',
            self::REVIEWED => 'warning',
            self::SHORTLISTED => 'success',
            self::INTERVIEW => 'primary',
            self::OFFERED => 'success',
            self::HIRED => 'success',
            self::REJECTED => 'danger',
            self::WITHDRAWN => 'gray',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::NEW => 1,
            self::REVIEWED => 2,
            self::SHORTLISTED => 3,
            self::INTERVIEW => 4,
            self::OFFERED => 5,
            self::HIRED => 6,
            self::REJECTED => 7,
            self::WITHDRAWN => 8,
        };
    }
}
