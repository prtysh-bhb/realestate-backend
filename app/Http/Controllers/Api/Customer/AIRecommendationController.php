<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\AI\PropertyRecommendationService;
use App\Models\AiRecommendation;
use Illuminate\Http\Request;

class AIRecommendationController extends Controller
{
    protected $service;

    public function __construct(PropertyRecommendationService $service)
    {
        $this->service = $service;
    }

    public function recommend(Request $request)
    {
        $validated = $request->validate([
            'bedrooms' => 'nullable|integer|min:1|max:10',
            'bathrooms' => 'nullable|integer|min:1|max:10',
            'area_min' => 'nullable|integer|min:0',
            'area_max' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'property_type' => 'nullable|string',
        ]);

        $result = $this->service->recommend($validated, auth()->id());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    public function history(Request $request)
    {
        try {
            $recommendations = AiRecommendation::where('user_id', auth()->id())
                ->with('conversation')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            \Log::error('Recommendation history error', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading history: ' . $e->getMessage(),
            ], 500);
        }
    }
}
