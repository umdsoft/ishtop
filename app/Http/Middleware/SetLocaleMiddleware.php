<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale')
            ?? $request->cookie('locale')
            ?? $this->detectFromBrowser($request)
            ?? 'uz';

        if (!in_array($locale, ['uz', 'ru'])) {
            $locale = 'uz';
        }

        app()->setLocale($locale);

        return $next($request);
    }

    private function detectFromBrowser(Request $request): ?string
    {
        $accept = $request->header('Accept-Language', '');
        if (str_contains($accept, 'ru')) {
            return 'ru';
        }
        return null;
    }
}
