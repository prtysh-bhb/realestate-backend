<?php

namespace App\Mail;

use App\Models\Property;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PropertyStatusUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $property;
    public $user;

    public function __construct(Property $property, User $user)
    {
        $this->property = $property;
        $this->user = $user;
    }

    public function build()
    {
        return $this->subject('Property Status Update - ' . $this->property->title)
                    ->view('emails.property-status-update');
    }
}