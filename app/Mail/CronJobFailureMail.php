<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CronJobFailureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $jobName;
    public $error;

    public function __construct(string $jobName, string $error = null)
    {
        $this->jobName = $jobName;
        $this->error = $error;
    }

    public function build()
    {
        return $this->subject('Cron Job Failed - ' . $this->jobName)
                    ->view('emails.cron-failure');
    }
}