<?php

namespace App\Models;

use App\Enums\BannerType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'title', 'type', 'image_url', 'image_mobile_url', 'click_url',
        'click_action', 'advertiser_name', 'advertiser_contact',
        'placement', 'categories', 'cities', 'priority',
        'max_impressions', 'max_clicks', 'impressions_count', 'clicks_count',
        'cost_type', 'cost_amount', 'total_revenue', 'status',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'placement' => 'array',
            'categories' => 'array',
            'cities' => 'array',
            'priority' => 'integer',
            'max_impressions' => 'integer',
            'max_clicks' => 'integer',
            'impressions_count' => 'integer',
            'clicks_count' => 'integer',
            'cost_amount' => 'decimal:2',
            'total_revenue' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'type' => BannerType::class,
        ];
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(BannerImpression::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForPlacement($query, string $placement)
    {
        return $query->whereJsonContains('placement', $placement);
    }

    public function isWithinLimits(): bool
    {
        if ($this->max_impressions && $this->impressions_count >= $this->max_impressions) return false;
        if ($this->max_clicks && $this->clicks_count >= $this->max_clicks) return false;
        return true;
    }

    public function ctr(): float
    {
        if ($this->impressions_count === 0) return 0;
        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }
}
