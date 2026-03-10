<?php

use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\WebController;
use Illuminate\Support\Facades\Route;

// ── Public Website (Blade + Tailwind) ──
Route::middleware('web.locale')->group(function () {
    Route::get('/', [WebController::class, 'home'])->name('home');
    Route::get('/vacancies', [WebController::class, 'index'])->name('vacancies.index');
    Route::get('/vacancies/{vacancy}', [WebController::class, 'show'])->name('vacancies.show');
    Route::post('/vacancies/{vacancy}/apply', [WebController::class, 'apply'])
        ->middleware('throttle:5,1')
        ->name('vacancy.apply');
    Route::post('/lang/{locale}', [WebController::class, 'setLocale'])->name('lang.switch');
});

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
