<?php

namespace App\Models;

use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'plan', 'status', 'price', 'limits',
        'features', 'starts_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'limits' => 'array',
            'features' => 'array',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'plan' => SubscriptionPlan::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function daysLeft(): int
    {
        return max(0, now()->diffInDays($this->expires_at, false));
    }
}
