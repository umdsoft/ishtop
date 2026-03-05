<?php

namespace App\Jobs;

use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackBannerImpressionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private Banner $banner,
        private ?string $userId,
        private string $placement,
        private string $ip,
    ) {}

    public function handle(BannerService $bannerService): void
    {
        $bannerService->recordImpression(
            $this->banner,
            $this->userId,
            $this->placement,
            $this->ip,
        );
    }
}
