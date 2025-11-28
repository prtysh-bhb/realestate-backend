<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PropertyApprovedMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Property Approved - ' . ($this->data['property_title'] ?? ''))
            ->view('emails.notifications.property-approved')
            ->with([
                'agentName' => $this->data['agent_name'] ?? 'Agent',
                'propertyTitle' => $this->data['property_title'] ?? 'Your Property',
                'propertyId' => $this->data['property_id'] ?? '',
                'approvedAt' => $this->data['approved_at'] ?? now()->format('M d, Y'),
                'actionUrl' => $this->data['action_url'] ?? '',
            ]);
    }
}