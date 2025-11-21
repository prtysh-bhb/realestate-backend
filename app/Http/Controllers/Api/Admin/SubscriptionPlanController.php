<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    /**
     * List all subscription plans
     */
    public function index(Request $request)
    {
        $query = SubscriptionPlan::query();

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $plans = $query->orderBy('sort_order', 'asc')
            ->orderBy('price', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Subscription plans retrieved successfully',
            'data' => [
                'plans' => $plans,
            ],
        ]);
    }

    /**
     * View single plan
     */
    public function show($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Plan retrieved successfully',
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

    /**
     * Create new subscription plan
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:subscription_plans,name',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'nullable|array',
            'property_limit' => 'required|integer|min:0',
            'featured_limit' => 'required|integer|min:0',
            'image_limit' => 'required|integer|min:1',
            'video_allowed' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = $request->all();
            $data['slug'] = Str::slug($request->name);

            $plan = SubscriptionPlan::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan created successfully',
                'data' => [
                    'plan' => $plan,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update subscription plan
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255|unique:subscription_plans,name,' . $id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'duration_days' => 'sometimes|integer|min:1',
            'features' => 'nullable|array',
            'property_limit' => 'sometimes|integer|min:0',
            'featured_limit' => 'sometimes|integer|min:0',
            'image_limit' => 'sometimes|integer|min:1',
            'video_allowed' => 'nullable|boolean',
            'priority_support' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $plan = SubscriptionPlan::findOrFail($id);

            $data = $request->all();
            
            // Update slug if name changed
            if ($request->has('name')) {
                $data['slug'] = Str::slug($request->name);
            }

            $plan->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Plan updated successfully',
                'data' => [
                    'plan' => $plan->fresh(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete subscription plan
     */
    public function destroy($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);

            // Delete the plan
            $plan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Plan deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete plan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle plan active status
     */
    public function toggleStatus($id)
    {
        try {
            $plan = SubscriptionPlan::findOrFail($id);
            $plan->is_active = !$plan->is_active;
            $plan->save();

            return response()->json([
                'success' => true,
                'message' => 'Plan status updated successfully',
                'data' => [
                    'plan' => $plan,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }
}