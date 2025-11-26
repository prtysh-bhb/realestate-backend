<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\UserSubscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessEvent
{
    use Dispatchable, SerializesModels;

    public $payment;
    public $subscription;

    public function __construct(Payment $payment, UserSubscription $subscription)
    {
        $this->payment = $payment;
        $this->subscription = $subscription;
    }
}