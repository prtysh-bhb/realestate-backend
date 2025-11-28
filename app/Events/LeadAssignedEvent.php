<?php

namespace App\Events;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAssignedEvent
{
    use Dispatchable, SerializesModels;

    public $inquiry;
    public $agent;

    public function __construct(Inquiry $inquiry, User $agent)
    {
        $this->inquiry = $inquiry;
        $this->agent = $agent;
    }
}