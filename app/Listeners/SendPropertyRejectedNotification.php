<?php

namespace App\Listeners;

use App\Events\PropertyRejectedEvent;
use App\Services\NotificationService;

class SendPropertyRejectedNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(PropertyRejectedEvent $event)
    {
        $property = $event->property;

        $this->notificationService->send(
            $property->agent,
            'property_rejected',
            [
                'property_id' => $property->id,
                'property_title' => $property->title,
                'agent_name' => $property->agent->name,
                'reason' => $event->reason,
                'rejected_at' => now()->format('M d, Y H:i'),
                'message' => "Your property '{$property->title}' has been rejected",
                'action_url' => "/agent/properties/{$property->id}",
            ],
            sendEmail: true,
            mailClass: \App\Mail\PropertyRejectedMail::class
        );
    }
}