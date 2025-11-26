<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class InquiryReceivedMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('New Inquiry Received - ' . ($this->data['property_title'] ?? ''))
            ->view('emails.notifications.inquiry-received')
            ->with([
                'agentName' => $this->data['agent_name'] ?? 'Agent',
                'propertyTitle' => $this->data['property_title'] ?? 'Property',
                'inquiryId' => $this->data['inquiry_id'] ?? '',
                'customerName' => $this->data['customer_name'] ?? 'Customer',
                'customerEmail' => $this->data['customer_email'] ?? '',
                'customerPhone' => $this->data['customer_phone'] ?? 'N/A',
                'inquiryMessage' => $this->data['inquiry_message'] ?? '',
                'createdAt' => $this->data['created_at'] ?? now()->format('M d, Y'),
                'actionUrl' => $this->data['action_url'] ?? '',
            ]);
    }
}