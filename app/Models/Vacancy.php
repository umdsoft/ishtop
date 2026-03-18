<?php

namespace App\Models;

use App\Enums\VacancyStatus;
use App\Enums\WorkType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Vacancy extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'employer_id', 'company_name', 'language',
        'title_uz', 'title_ru', 'slug', 'category', 'category_id',
        'description_uz', 'description_ru',
        'requirements_uz', 'requirements_ru',
        'responsibilities_uz', 'responsibilities_ru',
        'salary_min', 'salary_max', 'salary_type', 'currency',
        'work_type', 'experience_required', 'city', 'district',
        'latitude', 'longitude', 'contact_phone', 'contact_name', 'contact_email', 'contact_method',
        'status', 'is_top', 'is_urgent',
        'top_until', 'urgent_until', 'has_questionnaire',
        'published_at', 'expires_at', 'close_reason',
    ];

    protected function casts(): array
    {
        return [
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'views_count' => 'integer',
            'applications_count' => 'integer',
            'is_top' => 'boolean',
            'is_urgent' => 'boolean',
            'has_questionnaire' => 'boolean',
            'top_until' => 'datetime',
            'urgent_until' => 'datetime',
            'published_at' => 'datetime',
            'expires_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => VacancyStatus::class,
            'work_type' => WorkType::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Vacancy $vacancy) {
            if (empty($vacancy->slug)) {
                $vacancy->slug = $vacancy->generateSlug();
            }
        });
    }

    public function generateSlug(): string
    {
        $baseSlug = Str::slug($this->title_uz ?: $this->title_ru ?: 'vacancy');
        if ($baseSlug === '') {
            $baseSlug = 'vacancy';
        }

        return $baseSlug . '-' . substr($this->id, 0, 8);
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(EmployerProfile::class, 'employer_id');
    }

    public function categoryRelation(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function questionnaire(): HasOne
    {
        return $this->hasOne(Questionnaire::class);
    }

    public function interviewSlots(): HasMany
    {
        return $this->hasMany(InterviewSlot::class);
    }

    // ── Scopes ──

    public function scopeActive($query)
    {
        return $query->where('status', VacancyStatus::ACTIVE)->whereNotNull('slug');
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeSalaryRange($query, ?int $min, ?int $max)
    {
        if ($min) $query->where('salary_max', '>=', $min);
        if ($max) $query->where('salary_min', '<=', $max);
        return $query;
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

    public function scopeSearch($query, string $keyword)
    {
        return $query->whereRaw(
            "MATCH(title_uz, title_ru, description_uz, description_ru) AGAINST(? IN BOOLEAN MODE)",
            [$keyword]
        );
    }

    // ── Bilingual helpers ──

    public function title(string $lang = 'uz'): string
    {
        if ($lang === 'ru') {
            return $this->title_ru ?: $this->title_uz ?: '';
        }
        return $this->title_uz ?: $this->title_ru ?: '';
    }

    public function description(string $lang = 'uz'): string
    {
        if ($lang === 'ru') {
            return $this->description_ru ?: $this->description_uz ?: '';
        }
        return $this->description_uz ?: $this->description_ru ?: '';
    }

    public function requirements(string $lang = 'uz'): ?string
    {
        if ($lang === 'ru') {
            return $this->requirements_ru ?: $this->requirements_uz;
        }
        return $this->requirements_uz ?: $this->requirements_ru;
    }

    public function responsibilities(string $lang = 'uz'): ?string
    {
        if ($lang === 'ru') {
            return $this->responsibilities_ru ?: $this->responsibilities_uz;
        }
        return $this->responsibilities_uz ?: $this->responsibilities_ru;
    }

    // ── Helpers ──

    public function isActive(): bool
    {
        return $this->status === VacancyStatus::ACTIVE;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isTopActive(): bool
    {
        return $this->is_top && $this->top_until && $this->top_until->isFuture();
    }

    public function salaryFormatted(): string
    {
        if ($this->salary_type === 'negotiable') return 'Kelishiladi';
        if ($this->salary_min && $this->salary_max) {
            return number_format($this->salary_min) . ' - ' . number_format($this->salary_max) . " so'm";
        }
        if ($this->salary_min) return 'dan ' . number_format($this->salary_min) . " so'm";
        if ($this->salary_max) return 'gacha ' . number_format($this->salary_max) . " so'm";
        return 'Kelishiladi';
    }
}
