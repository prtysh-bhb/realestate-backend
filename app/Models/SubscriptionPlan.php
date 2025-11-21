<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'features',
        'property_limit',
        'featured_limit',
        'image_limit',
        'video_allowed',
        'priority_support',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'property_limit' => 'integer',
        'featured_limit' => 'integer',
        'image_limit' => 'integer',
        'video_allowed' => 'boolean',
        'priority_support' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'duration_days' => 'integer',
    ];

    // Relationships
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }
}