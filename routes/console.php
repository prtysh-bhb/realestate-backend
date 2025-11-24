<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Schedule reminder processing every 5 minutes
Schedule::command('reminders:process')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground();

// Check expired subscriptions daily at midnight
Schedule::command('subscriptions:check-expired')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground();

// Check subscriptions expiring in 7 days - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=7')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Check subscriptions expiring in 3 days - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=3')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();

// Check subscriptions expiring in 1 day - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=1')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground();