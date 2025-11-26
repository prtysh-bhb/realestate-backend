<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InquiryFollowupMail extends Mailable
{
    public $reminder;
    public $inquiry;
    public $property;
    public $agent;
    public $customer;

    public function __construct($reminder, $inquiry, $property, $agent, $customer)
    {
        $this->reminder = $reminder;
        $this->inquiry = $inquiry;
        $this->property = $property;
        $this->agent = $agent;
        $this->customer = $customer;
    }

    public function build()
    {
        return $this->subject('Follow-up on Your Property Inquiry')
            ->view('emails.reminders.inquiry-followup');
    }
}