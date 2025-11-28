<?php

namespace App\Listeners;

use App\Events\LeadAssignedEvent;
use App\Mail\LeadAssignedMail;
use Illuminate\Support\Facades\Mail;

class SendLeadAssignedNotification
{
    public function handle(LeadAssignedEvent $event)
    {
        Mail::to($event->agent->email)->send(new LeadAssignedMail($event->inquiry, $event->agent));
    }
}