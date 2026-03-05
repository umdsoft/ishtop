<?php

namespace App\Enums;

enum BannerType: string
{
    case HERO = 'hero';
    case CARD = 'card';
    case INLINE = 'inline';
    case POPUP = 'popup';
    case INTERSTITIAL = 'interstitial';
    case NATIVE = 'native';

    public function label(): string
    {
        return match ($this) {
            self::HERO => 'Hero banner',
            self::CARD => 'Card banner',
            self::INLINE => 'Inline banner',
            self::POPUP => 'Popup',
            self::INTERSTITIAL => 'Interstitial (to\'liq ekran)',
            self::NATIVE => 'Native ad',
        };
    }

    public function dimensions(): string
    {
        return match ($this) {
            self::HERO => '343x160px',
            self::CARD => '343x100px',
            self::INLINE => '343x80px',
            self::POPUP => 'To\'liq ekran',
            self::INTERSTITIAL => 'To\'liq ekran',
            self::NATIVE => 'Kartochka hajmi',
        };
    }
}
