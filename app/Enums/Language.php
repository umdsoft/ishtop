<?php

namespace App\Enums;

enum Language: string
{
    case UZ = 'uz';
    case RU = 'ru';

    public function label(): string
    {
        return match ($this) {
            self::UZ => 'O\'zbekcha',
            self::RU => 'Русский',
        };
    }
}
