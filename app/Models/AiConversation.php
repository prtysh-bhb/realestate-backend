<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiConversation extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'type',
        'messages',
        'extracted_data',
        'status',
    ];

    protected $casts = [
        'messages' => 'array',
        'extracted_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recommendations()
    {
        return $this->hasMany(AiRecommendation::class, 'conversation_id');
    }

    public function leads()
    {
        return $this->hasMany(AiChatLead::class, 'conversation_id');
    }
}