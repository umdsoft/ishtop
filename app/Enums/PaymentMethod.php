<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case PAYME = 'payme';
    case CLICK = 'click';
    case UZUM = 'uzum';
    case BALANCE = 'balance';

    public function label(): string
    {
        return match ($this) {
            self::PAYME => 'Payme',
            self::CLICK => 'Click',
            self::UZUM => 'Uzum',
            self::BALANCE => 'Ichki balans',
        };
    }
}
