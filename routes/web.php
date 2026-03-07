<?php

use Illuminate\Support\Facades\Route;

// Mini App - Vue SPA (serve static index.html for all sub-routes)
Route::get('/miniapp/{any?}', function () {
    return response()->file(public_path('miniapp/index.html'));
})->where('any', '.*');

// Recruiter Panel - Vue SPA
Route::get('/{any}', function () {
    return view('panel');
})->where('any', '.*');
