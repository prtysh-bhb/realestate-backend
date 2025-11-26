<?php

namespace App\Listeners;

use App\Events\InquiryReceivedEvent;
use App\Services\NotificationService;

class SendInquiryReceivedNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(InquiryReceivedEvent $event)
    {
        $inquiry = $event->inquiry;

        $this->notificationService->send(
            $inquiry->agent,
            'inquiry_received',
            [
                'inquiry_id' => $inquiry->id,
                'property_title' => $inquiry->property->title,
                'agent_name' => $inquiry->agent->name,
                'customer_name' => $inquiry->customer->name,
                'customer_email' => $inquiry->customer->email,
                'customer_phone' => $inquiry->customer->phone ?? 'N/A',
                'inquiry_message' => $inquiry->message,
                'created_at' => $inquiry->created_at->format('M d, Y H:i'),
                'message' => "New inquiry from {$inquiry->customer->name}",
                'action_url' => "/agent/inquiries/{$inquiry->id}",
            ],
            sendEmail: true,
            mailClass: \App\Mail\InquiryReceivedMail::class
        );
    }
}