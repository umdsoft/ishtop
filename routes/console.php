<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send profile completion reminders daily at 10:00 Tashkent time
Schedule::command('reminders:incomplete-profiles')->dailyAt('10:00')->timezone('Asia/Tashkent');
