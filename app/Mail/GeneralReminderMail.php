<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reminder;
    public $agent;

    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
        $this->agent = $reminder->agent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->reminder->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reminders.general',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}