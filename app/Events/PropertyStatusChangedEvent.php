<?php

namespace App\Events;

use App\Models\Property;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PropertyStatusChangedEvent
{
    use Dispatchable, SerializesModels;

    public $property;
    public $oldStatus;
    public $newStatus;

    public function __construct(Property $property, string $oldStatus, string $newStatus)
    {
        $this->property = $property;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }
}