<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * List all active subscription plans (Public - for agents to see)
     */
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Available plans retrieved successfully',
            'data' => [
                'plans' => $plans,
            ],
        ]);
    }

    /**
     * View single plan details
     */
    public function show($id)
    {
        try {
            $plan = SubscriptionPlan::where('is_active', true)
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Plan details retrieved successfully',
                'data' => [
                    'plan' => $plan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Plan not found',
            ], 404);
        }
    }
}