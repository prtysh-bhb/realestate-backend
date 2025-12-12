<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Services\AI\PriceEstimationService;
use App\Models\PriceEstimate;
use Illuminate\Http\Request;

class AIPriceController extends Controller
{
    protected $service;

    public function __construct(PriceEstimationService $service)
    {
        $this->service = $service;
    }

    /**
     * POST /api/agent/ai/estimate-price
     * Get AI price estimate
     */
    public function estimate(Request $request)
    {
        $validated = $request->validate([
            'location' => 'required|string|max:255',
            'bedrooms' => 'required|integer|min:1|max:10',
            'bathrooms' => 'required|integer|min:1|max:10',
            'area' => 'required|numeric|min:100',
            'property_type' => 'required|string',
            'condition' => 'nullable|string',
            'amenities' => 'nullable|string',
            'property_id' => 'nullable|exists:properties,id',
        ]);

        $result = $this->service->estimatePrice(
            $validated,
            auth()->id(),
            $validated['property_id'] ?? null
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * GET /api/agent/ai/price-estimates
     * View agent's price estimate history
     */
    public function history()
    {
        $estimates = PriceEstimate::where('agent_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $estimates,
        ]);
    }

    /**
     * GET /api/agent/ai/price-estimates/{id}
     * View specific estimate
     */
    public function show($id)
    {
        $estimate = PriceEstimate::where('agent_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $estimate,
        ]);
    }
}