<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    protected $table = 'user_wallet';

    protected $fillable = [
        'user_id',
        'current_credits',
        'total_credits_purchased',
        'total_credits_spent',
    ];

    protected $casts = [
        'current_credits' => 'integer',
        'total_credits_purchased' => 'integer',
        'total_credits_spent' => 'integer',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Check if user has enough credits
    public function hasEnoughCredits(int $amount): bool
    {
        return $this->current_credits >= $amount;
    }

    // Add credits
    public function addCredits(int $amount): void
    {
        $this->increment('current_credits', $amount);
        $this->increment('total_credits_purchased', $amount);
    }

    // Deduct credits
    public function deductCredits(int $amount): void
    {
        $this->decrement('current_credits', $amount);
        $this->increment('total_credits_spent', $amount);
    }
}