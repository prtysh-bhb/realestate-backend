<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reminder;
    public $appointment;
    public $property;
    public $agent;

    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
        $this->appointment = $reminder->appointment;
        $this->property = $reminder->property;
        $this->agent = $reminder->agent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Follow-up on Your Property Visit',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reminders.appointment-followup',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}