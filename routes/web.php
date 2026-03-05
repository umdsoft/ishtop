<?php

use Illuminate\Support\Facades\Route;

// Recruiter Panel - Vue SPA
Route::get('/{any}', function () {
    return view('panel');
})->where('any', '.*');
