<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \App\Events\PropertyApprovedEvent::class => [
            \App\Listeners\SendPropertyApprovedNotification::class,
        ],
        \App\Events\PropertyRejectedEvent::class => [
            \App\Listeners\SendPropertyRejectedNotification::class,
        ],
        \App\Events\InquiryReceivedEvent::class => [
            \App\Listeners\SendInquiryReceivedNotification::class,
        ],
        \App\Events\AppointmentScheduledEvent::class => [
            \App\Listeners\SendAppointmentScheduledNotification::class,
        ],
        \App\Events\PaymentSuccessEvent::class => [
            \App\Listeners\SendPaymentSuccessNotification::class,
        ],
        \App\Events\LeadAssignedEvent::class => [
            \App\Listeners\SendLeadAssignedNotification::class,
        ],
        \App\Events\PropertyStatusChangedEvent::class => [
            \App\Listeners\SendPropertyStatusUpdateNotification::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}