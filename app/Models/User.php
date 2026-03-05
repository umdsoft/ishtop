<?php

namespace App\Models;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, HasUuids, Notifiable, SoftDeletes;

    protected $fillable = [
        'telegram_id',
        'phone',
        'email',
        'password',
        'first_name',
        'last_name',
        'username',
        'language',
        'avatar_url',
        'is_verified',
        'is_blocked',
        'last_active_at',
        'referral_code',
        'referred_by',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'telegram_id' => 'integer',
            'is_verified' => 'boolean',
            'is_blocked' => 'boolean',
            'last_active_at' => 'datetime',
            'balance' => 'decimal:2',
            'password' => 'hashed',
            'language' => Language::class,
        ];
    }

    // ── Relationships ──

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function employerProfile(): HasOne
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function referrer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function savedItems(): HasMany
    {
        return $this->hasMany(SavedItem::class);
    }

    public function recruiterTags(): HasMany
    {
        return $this->hasMany(RecruiterTag::class);
    }

    public function messageTemplates(): HasMany
    {
        return $this->hasMany(MessageTemplate::class);
    }

    public function talentPoolEntries(): HasMany
    {
        return $this->hasMany(TalentPoolEntry::class, 'recruiter_user_id');
    }

    // ── Helpers ──

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function isWorker(): bool
    {
        return $this->workerProfile()->exists();
    }

    public function isEmployer(): bool
    {
        return $this->employerProfile()->exists();
    }

    public function activeSubscription(): ?Subscription
    {
        return $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();
    }

    public function hasPlan(string $plan): bool
    {
        $sub = $this->activeSubscription();
        return $sub && $sub->plan === $plan;
    }

    public static function generateReferralCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }
}
