<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $inquiry;
    public $agent;

    public function __construct(Inquiry $inquiry, User $agent)
    {
        $this->inquiry = $inquiry;
        $this->agent = $agent;
    }

    public function build()
    {
        return $this->subject('New Lead Assigned - ' . $this->inquiry->property->title)
                    ->view('emails.lead-assigned');
    }
}