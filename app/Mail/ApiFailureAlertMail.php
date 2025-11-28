<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ApiFailureAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $exception;
    public $context;

    public function __construct(\Exception $exception, array $context = [])
    {
        $this->exception = $exception;
        $this->context = $context;
    }

    public function build()
    {
        return $this->subject('API Failure Alert - ' . $this->exception->getMessage())
                    ->view('emails.api-failure');
    }
}