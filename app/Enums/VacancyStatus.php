<?php

namespace App\Enums;

enum VacancyStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case CLOSED = 'closed';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Qoralama',
            self::PENDING => 'Moderatsiyada',
            self::ACTIVE => 'Faol',
            self::PAUSED => 'To\'xtatilgan',
            self::CLOSED => 'Yopilgan',
            self::EXPIRED => 'Muddati tugagan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::PENDING => 'warning',
            self::ACTIVE => 'success',
            self::PAUSED => 'info',
            self::CLOSED => 'danger',
            self::EXPIRED => 'danger',
        };
    }
}
