<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'amount_paid',
        'starts_at',
        'ends_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'subscription_id');
    }

    // Check if subscription is active
    public function isActive()
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    // Check if subscription is expired
    public function isExpired()
    {
        return $this->ends_at->isPast();
    }

    /**
     * Check if user can create more properties
     */
    public function canCreateProperty($currentCount)
    {
        // 0 means unlimited
        if ($this->plan->property_limit === 0) {
            return true;
        }

        return $currentCount < $this->plan->property_limit;
    }

    /**
     * Check if user can feature more properties
     */
    public function canFeatureProperty()
    {
        // Count current month's featured properties
        $currentFeaturedCount = \App\Models\Property::where('agent_id', $this->user_id)
            ->where('is_featured', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 0 means unlimited
        if ($this->plan->featured_limit === 0) {
            return [
                'allowed' => true,
                'remaining' => 'unlimited',
                'used' => $currentFeaturedCount,
            ];
        }

        return [
            'allowed' => $currentFeaturedCount < $this->plan->featured_limit,
            'remaining' => max(0, $this->plan->featured_limit - $currentFeaturedCount),
            'used' => $currentFeaturedCount,
            'limit' => $this->plan->featured_limit,
        ];
    }

    /**
     * Get remaining property slots
     */
    public function getRemainingPropertySlots()
    {
        $currentCount = \App\Models\Property::where('agent_id', $this->user_id)->count();

        if ($this->plan->property_limit === 0) {
            return [
                'remaining' => 'unlimited',
                'used' => $currentCount,
            ];
        }

        return [
            'remaining' => max(0, $this->plan->property_limit - $currentCount),
            'used' => $currentCount,
            'limit' => $this->plan->property_limit,
        ];
    }
}