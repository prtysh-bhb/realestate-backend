<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentReview extends Model
{
    protected $fillable = [
        'agent_id',
        'user_id',
        'rating',
        'comment',
        'is_visible'
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
