<?php

namespace App\Enums;

enum WorkType: string
{
    case FULL_TIME = 'full_time';
    case PART_TIME = 'part_time';
    case REMOTE = 'remote';
    case TEMPORARY = 'temporary';

    public function label(): string
    {
        return match ($this) {
            self::FULL_TIME => 'To\'liq ish kuni',
            self::PART_TIME => 'Yarim stavka',
            self::REMOTE => 'Masofaviy',
            self::TEMPORARY => 'Vaqtinchalik',
        };
    }
}
