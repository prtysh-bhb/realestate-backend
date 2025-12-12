<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiChatLead extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'name',
        'email',
        'phone',
        'budget_min',
        'budget_max',
        'location_preference',
        'property_type',
        'bedrooms',
        'bathrooms',
        'move_in_date',
        'additional_notes',
        'lead_score',
        'status',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'bedrooms' => 'integer',
        'bathrooms' => 'integer',
        'move_in_date' => 'date',
        'lead_score' => 'integer',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}