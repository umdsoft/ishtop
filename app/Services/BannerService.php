<?php

namespace App\Services;

use App\Models\Banner;
use App\Models\BannerImpression;
use App\Models\User;
use Illuminate\Support\Collection;

class BannerService
{
    public function getForPlacement(string $placement, ?User $user = null, ?string $category = null): Collection
    {
        // Premium users don't see ads
        if ($user && $user->activeSubscription()) {
            return collect();
        }

        $query = Banner::active()
            ->forPlacement($placement)
            ->where(function ($q) {
                $q->whereNull('max_impressions')
                  ->orWhereColumn('impressions_count', '<', 'max_impressions');
            })
            ->orderByDesc('priority')
            ->orderByRaw('RAND()');

        if ($category) {
            $query->where(function ($q) use ($category) {
                $q->whereNull('categories')
                  ->orWhereJsonContains('categories', $category);
            });
        }

        return $query->limit(3)->get();
    }

    public function recordImpression(Banner $banner, ?string $userId, string $placement, string $ip): void
    {
        BannerImpression::create([
            'banner_id' => $banner->id,
            'user_id' => $userId,
            'placement' => $placement,
            'action' => 'impression',
            'ip' => $ip,
        ]);

        $banner->increment('impressions_count');
    }

    public function recordClick(Banner $banner, ?string $userId, string $placement, string $ip): bool
    {
        // Fraud protection: max 3 clicks per user per banner per day
        if ($userId) {
            $todayClicks = BannerImpression::where('banner_id', $banner->id)
                ->where('user_id', $userId)
                ->where('action', 'click')
                ->where('created_at', '>=', today())
                ->count();

            if ($todayClicks >= 3) return false;
        }

        BannerImpression::create([
            'banner_id' => $banner->id,
            'user_id' => $userId,
            'placement' => $placement,
            'action' => 'click',
            'ip' => $ip,
        ]);

        $banner->increment('clicks_count');

        return true;
    }
}
