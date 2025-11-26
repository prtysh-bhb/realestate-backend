<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class GeneralReminderMail extends Mailable
{
    public $reminder;
    public $agent;

    public function __construct($reminder, $agent)
    {
        $this->reminder = $reminder;
        $this->agent = $agent;
    }

    public function build()
    {
        return $this->subject($this->reminder->title ?? 'Reminder')
            ->view('emails.reminders.general');
    }
}