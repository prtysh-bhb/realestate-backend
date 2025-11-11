<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = [
        'customer_id',
        'property_id',
        'agent_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'message',
        'status',
        'agent_notes',
        'contacted_at',
        'stage',
        'notes',
        'history',
    ];

    protected $casts = [
        'contacted_at' => 'datetime',
        'history' => 'array',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}