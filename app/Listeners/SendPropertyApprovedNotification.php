<?php

namespace App\Listeners;

use App\Events\PropertyApprovedEvent;
use App\Services\NotificationService;

class SendPropertyApprovedNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(PropertyApprovedEvent $event)
    {
        $property = $event->property;

        $this->notificationService->send(
            $property->agent,
            'property_approved',
            [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'agent_name' => $property->agent->name,
                'approved_at' => now()->format('M d, Y H:i'),
                'message' => "Your property '{$property->title}' has been approved",
                'action_url' => "/agent/properties/{$property->id}",
            ],
            sendEmail: true, // Set to true when you want to send emails
            mailClass: \App\Mail\PropertyApprovedMail::class
        );
    }
}