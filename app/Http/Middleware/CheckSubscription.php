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

        // Allow if no subscription required (you can change this logic)
        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'You need an active subscription to perform this action',
                'redirect' => 'subscription-plans',
            ], 403);
        }

        // Check if subscription is expired
        if ($subscription->isExpired()) {
            return response()->json([
                'success' => false,
                'message' => 'Your subscription has expired. Please renew to continue.',
                'redirect' => 'subscription-plans',
            ], 403);
        }

        // Attach subscription info to request
        $request->attributes->set('subscription', $subscription);

        return $next($request);
    }
}