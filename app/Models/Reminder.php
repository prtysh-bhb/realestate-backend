<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'agent_id',
        'customer_id',
        'inquiry_id',
        'property_id',
        'appointment_id',
        'title',
        'description',
        'type',
        'priority',
        'remind_at',
        'status',
        'completed_at',
        'snoozed_until',
        'notes',
        'email_sent',
        'notification_sent',
        'email_status',
        'email_error',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
        'completed_at' => 'datetime',
        'snoozed_until' => 'datetime',
        'email_sent' => 'boolean',
        'notification_sent' => 'boolean',
    ];

    // Relationships
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

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('remind_at', today())
            ->where('status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('remind_at', '<', now())
            ->where('status', 'pending');
    }

    public function scopeUpcoming($query, $days = 7)
    {
        $days = (int) $days;
        
        return $query->whereBetween('remind_at', [now(), now()->addDays($days)])
            ->where('status', 'pending');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    // Helper methods
    public function isOverdue()
    {
        return $this->remind_at->isPast() && $this->status === 'pending';
    }

    public function isDueToday()
    {
        return $this->remind_at->isToday() && $this->status === 'pending';
    }

    public function markCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function snooze($until)
    {
        $this->update([
            'status' => 'snoozed',
            'snoozed_until' => $until,
        ]);
    }
}