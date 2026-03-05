<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Kutilmoqda',
            self::PROCESSING => 'Jarayonda',
            self::COMPLETED => 'Muvaffaqiyatli',
            self::FAILED => 'Xatolik',
            self::REFUNDED => 'Qaytarilgan',
            self::CANCELLED => 'Bekor qilingan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::REFUNDED => 'gray',
            self::CANCELLED => 'gray',
        };
    }
}
