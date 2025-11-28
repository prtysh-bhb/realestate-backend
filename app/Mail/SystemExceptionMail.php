<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SystemExceptionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function build()
    {
        return $this->subject('System Exception - ' . $this->exception->getMessage())
                    ->view('emails.system-exception');
    }
}