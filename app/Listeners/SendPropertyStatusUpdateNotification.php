<?php

namespace App\Listeners;

use App\Events\PropertyStatusChangedEvent;
use App\Mail\PropertyStatusUpdateMail;
use Illuminate\Support\Facades\Mail;

class SendPropertyStatusUpdateNotification
{
    public function handle(PropertyStatusChangedEvent $event)
    {
        // Notify users who favorited this property
        $users = $event->property->favorites()->with('user')->get()->pluck('user');
        
        foreach ($users as $user) {
            Mail::to($user->email)->send(new PropertyStatusUpdateMail($event->property, $user));
        }
    }
}