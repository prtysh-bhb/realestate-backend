<?php

namespace App\Events;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyRejectedEvent
{
    use Dispatchable, SerializesModels;

    public $property;
    public $reason;

    public function __construct(Property $property, string $reason)
    {
        $this->property = $property;
        $this->reason = $reason;
    }
}