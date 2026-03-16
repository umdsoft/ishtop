<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id', 'type', 'amount', 'method', 'status',
        'external_id', 'payable_type', 'payable_id', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'meta' => 'array',
            'status' => PaymentStatus::class,
            'method' => PaymentMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', PaymentStatus::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', PaymentStatus::PENDING);
    }

    public function scopeCandidateUnlocked($query, string $vacancyId, string $userId)
    {
        return $query->where('type', 'candidate_unlock')
            ->where('payable_type', Vacancy::class)
            ->where('payable_id', $vacancyId)
            ->where('user_id', $userId)
            ->completed();
    }

    public function isCompleted(): bool
    {
        return $this->status === PaymentStatus::COMPLETED;
    }

    public function markCompleted(string $externalId = null): void
    {
        $this->update([
            'status' => PaymentStatus::COMPLETED,
            'external_id' => $externalId ?? $this->external_id,
        ]);
    }

    public function amountFormatted(): string
    {
        return number_format($this->amount, 0, '.', ',') . " so'm";
    }
}
