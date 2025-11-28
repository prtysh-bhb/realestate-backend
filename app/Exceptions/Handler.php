<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use App\Mail\SystemExceptionMail;
use Illuminate\Support\Facades\Mail;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Send email for exceptions
            try {
                if ($systemEmail = config('mail.system_alert_email')) {
                    Mail::to($systemEmail)->send(new SystemExceptionMail($e));
                }
            } catch (\Exception $mailException) {
                \Log::error('Failed to send exception email: ' . $mailException->getMessage());
            }
        });
    }
}