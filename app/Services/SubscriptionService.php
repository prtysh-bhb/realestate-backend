<?php

namespace App\Services;

use App\Models\User;
use App\Models\Property;

class SubscriptionService
{
    /**
     * Check if user can create property
     */
    public function canCreateProperty(User $user)
    {
        $subscription = $user->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return [
                'allowed' => false,
                'message' => 'No active subscription',
            ];
        }

        $currentCount = Property::where('agent_id', $user->id)->count();

        if ($subscription->plan->property_limit === 0) {
            return [
                'allowed' => true,
                'remaining' => 'unlimited',
                'used' => $currentCount,
            ];
        }

        $remaining = $subscription->plan->property_limit - $currentCount;

        return [
            'allowed' => $remaining > 0,
            'remaining' => max(0, $remaining),
            'used' => $currentCount,
            'limit' => $subscription->plan->property_limit,
            'message' => $remaining > 0 ? null : 'Property limit reached',
        ];
    }

    /**
     * Check if user can feature property
     */
    public function canFeatureProperty(User $user)
    {
        $subscription = $user->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return [
                'allowed' => false,
                'message' => 'No active subscription',
            ];
        }

        $currentFeaturedCount = Property::where('agent_id', $user->id)
            ->where('is_featured', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        if ($subscription->plan->featured_limit === 0) {
            return [
                'allowed' => true,
                'remaining' => 'unlimited',
                'used' => $currentFeaturedCount,
            ];
        }

        $remaining = $subscription->plan->featured_limit - $currentFeaturedCount;

        return [
            'allowed' => $remaining > 0,
            'remaining' => max(0, $remaining),
            'used' => $currentFeaturedCount,
            'limit' => $subscription->plan->featured_limit,
            'message' => $remaining > 0 ? null : 'Featured property limit reached for this month',
        ];
    }

    /**
     * Get all limits info
     */
    public function getLimitsInfo(User $user)
    {
        $subscription = $user->activeSubscription()->with('plan')->first();

        if (!$subscription) {
            return [
                'has_subscription' => false,
            ];
        }

        return [
            'has_subscription' => true,
            'subscription' => $subscription,
            'properties' => $this->canCreateProperty($user),
            'featured' => $this->canFeatureProperty($user),
            'image_limit' => $subscription->plan->image_limit,
            'video_allowed' => $subscription->plan->video_allowed,
            'priority_support' => $subscription->plan->priority_support,
        ];
    }
}