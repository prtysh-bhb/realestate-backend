<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Mail\CronJobFailureMail;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule reminder processing every 5 minutes
Schedule::command('reminders:process')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        sendCronFailureEmail('reminders:process', 'Reminder processing failed');
    });

// Check expired subscriptions daily at midnight
Schedule::command('subscriptions:check-expired')
    ->daily()
    ->at('00:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        sendCronFailureEmail('subscriptions:check-expired', 'Expired subscription check failed');
    });

// Check subscriptions expiring in 7 days - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=7')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        sendCronFailureEmail('subscriptions:check-expiring (7 days)', 'Expiring subscription check failed');
    });

// Check subscriptions expiring in 3 days - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=3')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        sendCronFailureEmail('subscriptions:check-expiring (3 days)', 'Expiring subscription check failed');
    });

// Check subscriptions expiring in 1 day - daily at 9 AM
Schedule::command('subscriptions:check-expiring --days=1')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->onFailure(function () {
        sendCronFailureEmail('subscriptions:check-expiring (1 day)', 'Expiring subscription check failed');
    });

// Helper function for cron failure emails
function sendCronFailureEmail($jobName, $error)
{
    if ($systemEmail = config('mail.system_alert_email')) {
        Mail::to($systemEmail)->send(new CronJobFailureMail($jobName, $error));
    }
}