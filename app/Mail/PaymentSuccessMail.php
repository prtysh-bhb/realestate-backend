<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class PaymentSuccessMail extends Mailable
{
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject('Payment Successful - ' . ($this->data['plan_name'] ?? 'Subscription'))
            ->view('emails.notifications.payment-success')
            ->with([
                'userName' => $this->data['user_name'] ?? 'User',
                'planName' => $this->data['plan_name'] ?? 'Plan',
                'paymentId' => $this->data['payment_id'] ?? '',
                'amount' => $this->data['amount'] ?? 0,
                'paymentDate' => $this->data['payment_date'] ?? now()->format('M d, Y'),
                'actionUrl' => $this->data['action_url'] ?? '',
                'invoiceUrl' => $this->data['invoice_url'] ?? '',
            ]);
    }
}