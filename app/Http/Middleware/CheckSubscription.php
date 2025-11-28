<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Only check for agents
        if ($user->role !== 'agent') {
            return $next($request);
        }

        // Get active subscription
        $subscription = $user->activeSubscription()->with('plan')->first();

        // Check if subscription exists
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'You need an active subscription to perform this action',
                'subscription_status' => 'no_subscription',
                'redirect' => 'subscription-plans',
            ], 403);
        }

        // Check if subscription is expired (real-time check)
        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            // Update status to expired if not already
            if ($subscription->status === 'active') {
                $subscription->update(['status' => 'expired']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
                'subscription_status' => 'expired',
                'expired_at' => $subscription->ends_at,
                'redirect' => 'subscription-plans',
            ], 403);
        }

        // Attach subscription info to request
        $request->attributes->set('subscription', $subscription);

        return $next($request);
    }
}