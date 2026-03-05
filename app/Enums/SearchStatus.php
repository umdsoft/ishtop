<?php

namespace App\Enums;

enum SearchStatus: string
{
    case OPEN = 'open';
    case PASSIVE = 'passive';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Ochiq — ish qidirmoqda',
            self::PASSIVE => 'Passiv — faqat yaxshi takliflar',
            self::CLOSED => 'Yopiq — ish qidirmayapti',
        };
    }
}
