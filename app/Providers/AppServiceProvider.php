<?php

namespace App\Providers;

use App\Models\Application;
use App\Observers\ApplicationObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Application::observe(ApplicationObserver::class);
        $this->configureRateLimiting();

        // Production da HTTPS majburlash
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // API genel - 60 req/min
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Auth endpoints — keyed by telegram_id when available
        RateLimiter::for('auth', function (Request $request) {
            $initData = $request->input('init_data', '');
            if ($initData) {
                parse_str($initData, $parsed);
                $userData = json_decode($parsed['user'] ?? '{}', true);
                if (!empty($userData['id'])) {
                    return Limit::perMinute(30)->by('tg_' . $userData['id']);
                }
            }
            return Limit::perMinute(30)->by($request->ip());
        });

        // Search - 120 req/min (high traffic)
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Vacancy creation - 5 req/hour (spam prevention)
        RateLimiter::for('vacancy-create', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        // Application submission - 10 req/hour
        RateLimiter::for('application-submit', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        // Payment - 10 req/hour
        RateLimiter::for('payment', function (Request $request) {
            return Limit::perHour(10)->by($request->user()?->id ?: $request->ip());
        });

        // Webhook - unlimited (trusted sources, signature verified)
        RateLimiter::for('webhook', function (Request $request) {
            return Limit::none();
        });

        // Recruiter API - 120 req/min
        RateLimiter::for('recruiter', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
