<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule to void expired reservations daily at midnight
Schedule::command('reservations:void-expired')
    ->daily()
    ->at('00:00');

// Schedule to check for approaching due dates and claim deadlines daily at 9 AM
Schedule::command('notifications:check-due-dates')
    ->daily()
    ->at('09:00');
