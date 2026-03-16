<?php

use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

// ── Language switch (POST) ──
Route::post('/lang/{locale}', [WebController::class, 'setLocale'])->name('lang.switch');

// ── SEO ──
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ── Mini App - Vue SPA ──
Route::get('/miniapp/{any?}', [WebController::class, 'miniapp'])->where('any', '.*');

// ── Recruiter Panel - Vue SPA ──
Route::get('/panel/{any?}', [WebController::class, 'panel'])->where('any', '.*');

// ── Admin Panel - Vue SPA ──
Route::get('/dash/{any?}', [WebController::class, 'dash'])->where('any', '.*');

// ── Public Website — SSR pages (SEO-optimized) ──
Route::middleware('web.locale')->group(function () {
    // Bosh sahifa (SSR)
    Route::get('/', [WebController::class, 'home'])->name('home');

    // Vakansiyalar ro'yxati (SSR)
    Route::get('/vacancies', [WebController::class, 'index'])->name('vacancies.index');

    // Vakansiya tafsiloti — slug orqali (SSR)
    Route::get('/vacancies/{vacancy:slug}', [WebController::class, 'show'])->name('vacancies.show');

    // Eski UUID URL'lardan 301 redirect
    Route::get('/vacancies/{uuid}', [WebController::class, 'legacyRedirect'])->whereUuid('uuid');
});

// ── Public Website — Vue SPA (catch-all, must be LAST) ──
Route::middleware('web.locale')->group(function () {
    Route::get('/{any}', [WebController::class, 'spa'])
        ->where('any', '^(?!panel|miniapp|api|sitemap\.xml|filament|dash|admin|build|hot|vacancies).*$')
        ->name('website.spa');
});
