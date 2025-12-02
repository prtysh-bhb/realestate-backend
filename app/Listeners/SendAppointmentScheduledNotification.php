<?php

namespace App\Listeners;

use App\Events\AppointmentScheduledEvent;
use App\Services\NotificationService;

class SendAppointmentScheduledNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(AppointmentScheduledEvent $event)
    {
        $appointment = $event->appointment;

        $this->notificationService->send(
            $appointment->agent,
            'appointment_scheduled',
            [
                'appointment_id' => $appointment->id,
                'appointment_type' => $appointment->type,
                'agent_name' => $appointment->agent->name,
                'property_title' => $appointment->property->title ?? 'Property',
                'scheduled_at' => $appointment->scheduled_at->format('M d, Y H:i'),
                'customer_name' => $appointment->customer->name,
                'message' => "New {$appointment->type} scheduled",
                'action_url' => "/agent/appointments",
            ],
            sendEmail: true,
            mailClass: \App\Mail\AppointmentScheduledMail::class
        );
    }
}