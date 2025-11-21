<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'property_id',
        'agent_id',
        'customer_id',
        'inquiry_id',
        'type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'notes',
        'customer_notes',
        'agent_notes',
        'location',
        'phone_number',
        'confirmed_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'duration_minutes' => 'integer',
    ];

    // Relationships
    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
            ->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopePast($query)
    {
        return $query->where('scheduled_at', '<', now())
            ->orWhereIn('status', ['completed', 'cancelled', 'no_show']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    // Helper methods
    public function isUpcoming()
    {
        return $this->scheduled_at->isFuture() && 
               in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && 
               $this->scheduled_at->isFuture();
    }

    public function canBeRescheduled()
    {
        return in_array($this->status, ['scheduled', 'confirmed']) && 
               $this->scheduled_at->isFuture();
    }
}   