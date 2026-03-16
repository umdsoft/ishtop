<?php

namespace App\Models;

use App\Enums\Language;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName, HasAvatar
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
        'notifications_enabled',
        'last_active_at',
        'referral_code',
        'referred_by',
        'balance',
        'active_employer_id',
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
            'notifications_enabled' => 'boolean',
            'last_active_at' => 'datetime',
            'balance' => 'decimal:2',
            'password' => 'hashed',
            'language' => Language::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (!$user->referral_code) {
                $user->referral_code = static::generateReferralCode();
            }
        });
    }

    // ── Relationships ──

    public function workerProfile(): HasOne
    {
        return $this->hasOne(WorkerProfile::class);
    }

    public function employerProfile(): BelongsTo
    {
        return $this->belongsTo(EmployerProfile::class, 'active_employer_id');
    }

    public function employerProfiles(): HasMany
    {
        return $this->hasMany(EmployerProfile::class);
    }

    public function referrer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    public function getOrCreateEmployerProfile(?string $companyName = null): EmployerProfile
    {
        if ($this->employerProfile) {
            return $this->employerProfile;
        }
        $employer = $this->employerProfiles()->create([
            'company_name' => $companyName ?? $this->first_name ?? 'Kompaniya',
        ]);
        $this->update(['active_employer_id' => $employer->id]);
        $this->setRelation('employerProfile', $employer);
        return $employer;
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

    // ── Filament ──

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin' => $this->hasRole('admin'),
            'recruiter' => $this->employerProfiles()->exists(),
            default => false,
        };
    }

    public function getFilamentName(): string
    {
        return trim("{$this->first_name} {$this->last_name}") ?: ($this->username ?? 'User');
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url;
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
        return $this->employerProfiles()->exists();
    }

    public function switchEmployer(string $employerId): bool
    {
        $profile = $this->employerProfiles()->where('id', $employerId)->first();
        if (!$profile) {
            return false;
        }
        $this->update(['active_employer_id' => $profile->id]);
        return true;
    }

    public function activeSubscription(): ?Subscription
    {
        return once(fn() => $this->subscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first());
    }

    public function hasPlan(string $plan): bool
    {
        $sub = $this->activeSubscription();
        return $sub && $sub->plan === $plan;
    }

    public static function generateReferralCode(): string
    {
        return strtoupper(substr(str_replace('-', '', (string) \Illuminate\Support\Str::uuid()), 0, 8));
    }
}
