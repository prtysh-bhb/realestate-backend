<?php

namespace App\Services\AI;

use App\Models\Property;
use App\Models\PriceEstimate;
use Illuminate\Support\Facades\Log;

class PriceEstimationService
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function estimatePrice(array $propertyDetails, $agentId, $propertyId = null)
    {
        try {
            Log::info('Starting price estimation', ['details' => $propertyDetails]);

            // Step 1: Find comparable properties
            $comparables = $this->findComparables($propertyDetails);
            
            Log::info('Comparables found', ['count' => $comparables->count()]);

            if ($comparables->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'Not enough comparable properties found for accurate estimation.',
                ];
            }

            // Step 2: Calculate base price
            $basePrice = $this->calculateBasePrice($comparables, $propertyDetails);
            
            Log::info('Base price calculated', ['base_price' => $basePrice]);

            // Step 3: Prepare data for AI
            $comparablesData = $comparables->map(function ($property) {
                return [
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'area' => $property->area,
                    'location' => $property->location . ', ' . $property->city,
                    'price' => $property->price,
                    'property_type' => $property->property_type,
                ];
            })->toArray();

            // Step 4: Build prompt
            $prompt = $this->buildPricePrompt($propertyDetails, $comparablesData, $basePrice);

            // Step 5: Get AI response
            $aiResponse = $this->gemini->generateJSON($prompt);
            
            Log::info('AI price response', ['response' => $aiResponse]);

            if (!$aiResponse['success']) {
                return [
                    'success' => false,
                    'message' => 'AI service error',
                    'error' => $aiResponse['error'],
                ];
            }

            $priceData = $aiResponse['data'];

            // Step 6: Save estimate
            $estimate = PriceEstimate::create([
                'agent_id' => $agentId,
                'property_id' => $propertyId,
                'property_details' => $propertyDetails,
                'estimated_price' => $priceData['estimated_price'],
                'price_range_min' => $priceData['price_range']['min'],
                'price_range_max' => $priceData['price_range']['max'],
                'ai_reasoning' => $priceData['reasoning'],
                'comparables' => $comparablesData,
                'breakdown' => $priceData['breakdown'],
                'suggested_listing_price' => $priceData['suggested_listing_price'],
            ]);

            return [
                'success' => true,
                'data' => [
                    'estimated_price' => $priceData['estimated_price'],
                    'price_range' => $priceData['price_range'],
                    'breakdown' => $priceData['breakdown'],
                    'reasoning' => $priceData['reasoning'],
                    'suggested_listing_price' => $priceData['suggested_listing_price'],
                    'comparables_count' => $comparables->count(),
                    'estimate_id' => $estimate->id,
                ],
            ];

        } catch (\Exception $e) {
            Log::error('Price estimation error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    protected function findComparables(array $details)
    {
        $query = Property::where('status', 'published')
            ->where('approval_status', 'approved');

        Log::info('Finding comparables', ['details' => $details]);

        // Match location - split by comma and search both parts
        if (!empty($details['location'])) {
            $locationParts = array_map('trim', explode(',', $details['location']));
            
            $query->where(function ($q) use ($locationParts) {
                foreach ($locationParts as $part) {
                    $q->orWhere('location', 'like', '%' . $part . '%')
                    ->orWhere('city', 'like', '%' . $part . '%');
                }
            });
        }

        // Match bedrooms (exact or ±1)
        if (!empty($details['bedrooms'])) {
            $query->whereBetween('bedrooms', [
                max(1, $details['bedrooms'] - 1),
                $details['bedrooms'] + 1
            ]);
        }

        // Match property type (case insensitive)
        if (!empty($details['property_type'])) {
            $query->whereRaw('LOWER(property_type) = ?', [strtolower($details['property_type'])]);
        }

        // Match area (±30% for more results)
        if (!empty($details['area'])) {
            $areaMin = $details['area'] * 0.7;
            $areaMax = $details['area'] * 1.3;
            $query->whereBetween('area', [$areaMin, $areaMax]);
        }

        $results = $query->limit(10)->get();
        
        Log::info('Comparables query', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => $results->count()
        ]);

        return $results;
    }

    protected function calculateBasePrice($comparables, $details)
    {
        // Calculate average price per sqft
        $totalPricePerSqft = 0;
        $count = 0;

        foreach ($comparables as $property) {
            if ($property->area > 0) {
                $totalPricePerSqft += ($property->price / $property->area);
                $count++;
            }
        }

        $avgPricePerSqft = $count > 0 ? ($totalPricePerSqft / $count) : 0;

        // Calculate base price
        $area = $details['area'] ?? 1000;
        $basePrice = $avgPricePerSqft * $area;

        return round($basePrice, -3); // Round to nearest thousand
    }

    protected function buildPricePrompt(array $details, array $comparables, $basePrice)
    {
        $detailsText = "Property Details:\n";
        $detailsText .= "Location: " . ($details['location'] ?? 'Not specified') . "\n";
        $detailsText .= "Bedrooms: " . ($details['bedrooms'] ?? 'Not specified') . "\n";
        $detailsText .= "Bathrooms: " . ($details['bathrooms'] ?? 'Not specified') . "\n";
        $detailsText .= "Area: " . ($details['area'] ?? 'Not specified') . " sq ft\n";
        $detailsText .= "Property Type: " . ($details['property_type'] ?? 'Not specified') . "\n";
        $detailsText .= "Condition: " . ($details['condition'] ?? 'Not specified') . "\n";
        $detailsText .= "Amenities: " . ($details['amenities'] ?? 'Standard') . "\n\n";

        $comparablesText = "Comparable Properties:\n";
        foreach ($comparables as $i => $comp) {
            $pricePerSqft = $comp['area'] > 0 ? round($comp['price'] / $comp['area']) : 0;
            $comparablesText .= ($i + 1) . ". {$comp['bedrooms']} BHK, {$comp['area']} sqft\n";
            $comparablesText .= "   Location: {$comp['location']}\n";
            $comparablesText .= "   Price: ₹" . number_format($comp['price']) . "\n";
            $comparablesText .= "   Per sqft: ₹" . number_format($pricePerSqft) . "\n\n";
        }

        $baseText = "Calculated Base Price: ₹" . number_format($basePrice) . "\n\n";
        
        $comparablesCount = count($comparables); // FIX: Get count first

        return $detailsText . $comparablesText . $baseText . "
    Task: Provide accurate price estimate with adjustments.

    Consider:
    1. Location premium/discount
    2. Property condition (new/resale)
    3. Amenities value
    4. Market trends
    5. Property type premium

    Return JSON:
    {
    \"estimated_price\": 8500000,
    \"price_range\": {
        \"min\": 8000000,
        \"max\": 9000000
    },
    \"breakdown\": {
        \"base_price\": 8100000,
        \"location_adjustment\": 200000,
        \"condition_adjustment\": 150000,
        \"amenities_adjustment\": 50000,
        \"final_adjustment\": 0
    },
    \"reasoning\": \"Based on {$comparablesCount} comparable properties, this is a fair market price. Location commands premium due to...\",
    \"suggested_listing_price\": 8700000
    }";
    }
}