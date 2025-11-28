<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AppointmentFollowupMail extends Mailable
{
    public $reminder;
    public $appointment;
    public $property;
    public $agent;

    public function __construct($reminder, $appointment, $property, $agent)
    {
        $this->reminder = $reminder;
        $this->appointment = $appointment;
        $this->property = $property;
        $this->agent = $agent;
    }

    public function build()
    {
        return $this->subject('Follow-up on Your Property Visit')
            ->view('emails.reminders.appointment-followup');
    }
}