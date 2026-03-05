<?php

namespace App\Models;

use App\Enums\VerificationLevel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class EmployerProfile extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id', 'company_name', 'industry', 'description', 'address',
        'phone', 'website', 'logo_url', 'cover_url', 'employees_count',
        'stir_number', 'verification_level', 'rating', 'rating_count',
        'response_time_avg', 'latitude', 'longitude',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'rating_count' => 'integer',
            'response_time_avg' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'verification_level' => VerificationLevel::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resume(): HasOne
    {
        return $this->hasOne(EmployerResume::class);
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'employer_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeVerified($query)
    {
        return $query->whereIn('verification_level', [
            VerificationLevel::VERIFIED->value,
            VerificationLevel::TOP->value,
        ]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('cover')->singleFile();
    }

    public function updateRating(): void
    {
        $this->update([
            'rating' => $this->reviews()->avg('rating') ?? 0,
            'rating_count' => $this->reviews()->count(),
        ]);
    }
}
