<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AppointmentScheduledMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('New Appointment Scheduled')
            ->view('emails.notifications.appointment-scheduled')
            ->with([
                'agentName' => $this->data['agent_name'] ?? 'Agent',
                'appointmentType' => $this->data['appointment_type'] ?? 'appointment',
                'appointmentId' => $this->data['appointment_id'] ?? '',
                'propertyTitle' => $this->data['property_title'] ?? 'Property',
                'customerName' => $this->data['customer_name'] ?? 'Customer',
                'scheduledAt' => $this->data['scheduled_at'] ?? now()->format('M d, Y H:i'),
                'actionUrl' => $this->data['action_url'] ?? '',
            ]);
    }
}