<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    protected $fillable = [
        'user_id',
        'conversation_id',
        'preferences',
        'recommended_properties',
        'ai_reasoning',
        'total_matches',
    ];

    protected $casts = [
        'preferences' => 'array',
        'recommended_properties' => 'array',
        'total_matches' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'conversation_id');
    }
}