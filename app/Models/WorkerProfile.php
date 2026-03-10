<?php

namespace App\Models;

use App\Enums\SearchStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class WorkerProfile extends Model implements HasMedia
{
    use HasFactory, HasUuids, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'user_id', 'full_name', 'birth_date', 'gender', 'city', 'district',
        'education_level', 'specialty', 'experience_years', 'skills',
        'expected_salary_min', 'expected_salary_max', 'work_types', 'preferred_categories', 'bio', 'work_experience',
        'photo_url', 'resume_file_url', 'linkedin_url', 'linkedin_import_data',
        'linkedin_imported_at', 'search_status', 'latitude', 'longitude',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'skills' => 'array',
            'work_types' => 'array',
            'preferred_categories' => 'array',
            'work_experience' => 'array',
            'experience_years' => 'integer',
            'expected_salary_min' => 'integer',
            'expected_salary_max' => 'integer',
            'views_count' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'linkedin_import_data' => 'array',
            'linkedin_imported_at' => 'datetime',
            'search_status' => SearchStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'worker_id');
    }

    public function talentPoolEntries(): HasMany
    {
        return $this->hasMany(TalentPoolEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('search_status', SearchStatus::OPEN);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeNearby($query, float $lat, float $lng, int $radiusKm = 10)
    {
        $haversine = \App\Services\GeoService::haversineFormula();
        return $query
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw("{$haversine} <= ?", [$lat, $lng, $lat, $radiusKm])
            ->selectRaw("*, {$haversine} as distance_km", [$lat, $lng, $lat]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
        $this->addMediaCollection('resume')->singleFile();
    }

    public function getAge(): ?int
    {
        return $this->birth_date?->age;
    }
}
