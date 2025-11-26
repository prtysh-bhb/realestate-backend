<?php

namespace App\Events;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyApprovedEvent
{
    use Dispatchable, SerializesModels;

    public $property;

    public function __construct(Property $property)
    {
        $this->property = $property;
    }
}