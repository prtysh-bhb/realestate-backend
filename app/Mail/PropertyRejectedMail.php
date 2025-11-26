<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PropertyRejectedMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Property Rejected - ' . ($this->data['property_title'] ?? ''))
            ->view('emails.notifications.property-rejected')
            ->with([
                'agentName' => $this->data['agent_name'] ?? 'Agent',
                'propertyTitle' => $this->data['property_title'] ?? 'Your Property',
                'propertyId' => $this->data['property_id'] ?? '',
                'reason' => $this->data['reason'] ?? 'Not specified',
                'rejectedAt' => $this->data['rejected_at'] ?? now()->format('M d, Y'),
                'actionUrl' => $this->data['action_url'] ?? '',
            ]);
    }
}