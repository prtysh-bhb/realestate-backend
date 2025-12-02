<?php

namespace App\Listeners;

use App\Events\PaymentSuccessEvent;
use App\Services\NotificationService;

class SendPaymentSuccessNotification
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(PaymentSuccessEvent $event)
    {
        $payment = $event->payment;
        $subscription = $event->subscription;

        $this->notificationService->send(
            $subscription->user,
            'payment_success',
            [
                'payment_id' => $payment->id,
                'user_name' => $subscription->user->name,
                'amount' => $payment->amount,
                'plan_name' => $subscription->plan->name,
                'payment_date' => $payment->created_at->format('M d, Y H:i'),
                'message' => "Payment successful for {$subscription->plan->name} plan",
                'action_url' => "/my-subscriptions",
                'invoice_url' => "/payments/{$payment->id}/invoice",
            ],
            sendEmail: true,
            mailClass: \App\Mail\PaymentSuccessMail::class
        );
    }
}