<?php

use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

// ── Language switch (POST) ──
Route::post('/lang/{locale}', [WebController::class, 'setLocale'])->name('lang.switch');

// ── SEO ──
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ── Mini App - Vue SPA ──
Route::get('/miniapp/{any?}', function () {
    return response()->file(public_path('miniapp/index.html'));
})->where('any', '.*');

// ── Recruiter Panel - Vue SPA ──
Route::get('/panel/{any?}', function () {
    return view('panel');
})->where('any', '.*');

// ── Public Website — Vue SPA (catch-all, must be LAST) ──
Route::middleware('web.locale')->group(function () {
    Route::get('/{any?}', [WebController::class, 'spa'])
        ->where('any', '^(?!panel|miniapp|api|sitemap\.xml|filament|admin|build|hot).*$')
        ->name('website.spa');
});
