<?php

namespace App\Mail;

use App\Models\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryFollowupMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reminder;
    public $inquiry;
    public $property;
    public $agent;

    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
        $this->inquiry = $reminder->inquiry;
        $this->property = $reminder->property;
        $this->agent = $reminder->agent;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Follow-up on Your Property Inquiry',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reminders.inquiry-followup',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}