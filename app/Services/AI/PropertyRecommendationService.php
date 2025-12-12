<?php

namespace App\Services\AI;

use App\Models\Property;
use App\Models\AiRecommendation;
use App\Models\AiConversation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PropertyRecommendationService
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function recommend(array $preferences, $userId = null)
    {
        try {
            Log::info('Starting AI recommendation', ['preferences' => $preferences]);

            // Create conversation
            $conversation = AiConversation::create([
                'user_id' => $userId,
                'session_id' => Str::uuid(),
                'type' => 'recommendation',
                'extracted_data' => $preferences,
                'status' => 'active',
            ]);

            // Filter properties
            $properties = $this->filterProperties($preferences);
            
            Log::info('Properties found', ['count' => $properties->count()]);

            if ($properties->isEmpty()) {
                $conversation->update(['status' => 'completed']);
                return [
                    'success' => false,
                    'message' => 'No properties found matching your criteria.',
                ];
            }

            // Prepare data
            $propertiesData = $properties->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'area' => $property->area,
                    'location' => $property->location,
                    'city' => $property->city,
                    'price' => $property->price,
                    'property_type' => $property->property_type,
                ];
            })->toArray();

            // Build prompt
            $prompt = $this->buildPrompt($preferences, $propertiesData);
            
            Log::info('Calling Gemini API');

            // Get AI response
            $aiResponse = $this->gemini->generateJSON($prompt);
            
            Log::info('Gemini response', ['response' => $aiResponse]);

            if (!$aiResponse['success']) {
                $conversation->update(['status' => 'abandoned']);
                return [
                    'success' => false,
                    'message' => 'AI service error',
                    'error' => $aiResponse['error'],
                ];
            }

            $recommendations = $aiResponse['data']['recommendations'] ?? [];
            
            // Get properties
            $propertyIds = array_column($recommendations, 'property_id');
            $fullProperties = Property::whereIn('id', $propertyIds)->get()->keyBy('id');

            $result = [];
            foreach ($recommendations as $rec) {
                $property = $fullProperties[$rec['property_id']] ?? null;
                if ($property) {
                    $result[] = [
                        'property' => $property,
                        'match_score' => $rec['match_score'],
                        'reasoning' => $rec['reasoning'],
                        'highlights' => $rec['highlights'] ?? [],
                    ];
                }
            }

            // Save
            AiRecommendation::create([
                'user_id' => $userId,
                'conversation_id' => $conversation->id,
                'preferences' => $preferences,
                'recommended_properties' => $result,
                'ai_reasoning' => $aiResponse['data']['summary'] ?? '',
                'total_matches' => $properties->count(),
            ]);

            $conversation->update(['status' => 'completed']);

            return [
                'success' => true,
                'total_matches' => $properties->count(),
                'recommendations' => $result,
                'summary' => $aiResponse['data']['summary'] ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('Recommendation error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ];
        }
    }

    protected function filterProperties(array $preferences)
    {
        // Fix: Use correct status values
        $query = Property::where('status', 'published')
            ->where('approval_status', 'approved');

        Log::info('Starting filter', ['preferences' => $preferences]);

        if (!empty($preferences['bedrooms'])) {
            $query->where('bedrooms', $preferences['bedrooms']);
            Log::info('Added bedrooms filter', ['bedrooms' => $preferences['bedrooms']]);
        }

        if (!empty($preferences['bathrooms'])) {
            $query->where('bathrooms', '>=', $preferences['bathrooms']);
            Log::info('Added bathrooms filter', ['bathrooms' => $preferences['bathrooms']]);
        }

        if (!empty($preferences['area_min']) || !empty($preferences['area_max'])) {
            $query->whereBetween('area', [
                $preferences['area_min'] ?? 0,
                $preferences['area_max'] ?? 999999
            ]);
            Log::info('Added area filter');
        }

        if (!empty($preferences['location'])) {
            $query->where(function ($q) use ($preferences) {
                $q->where('location', 'like', '%' . $preferences['location'] . '%')
                ->orWhere('city', 'like', '%' . $preferences['location'] . '%');
            });
            Log::info('Added location filter', ['location' => $preferences['location']]);
        }

        if (!empty($preferences['budget_min']) || !empty($preferences['budget_max'])) {
            $query->whereBetween('price', [
                $preferences['budget_min'] ?? 0,
                $preferences['budget_max'] ?? 999999999
            ]);
            Log::info('Added budget filter');
        }

        if (!empty($preferences['property_type'])) {
            $query->whereRaw('LOWER(property_type) = ?', [strtolower($preferences['property_type'])]);
            Log::info('Added property_type filter');
        }

        $sql = $query->toSql();
        $bindings = $query->getBindings();
        Log::info('Final SQL', ['sql' => $sql, 'bindings' => $bindings]);

        $results = $query->limit(10)->get();
        
        Log::info('Query results', ['count' => $results->count()]);

        return $results;
    }

    protected function buildPrompt(array $prefs, array $props)
    {
        $prefsText = "Preferences:\n";
        $prefsText .= "Bedrooms: " . ($prefs['bedrooms'] ?? 'Any') . "\n";
        $prefsText .= "Bathrooms: " . ($prefs['bathrooms'] ?? 'Any') . "\n";
        $prefsText .= "Location: " . ($prefs['location'] ?? 'Any') . "\n";
        $prefsText .= "Budget: ₹" . number_format($prefs['budget_min'] ?? 0) . " - ₹" . number_format($prefs['budget_max'] ?? 999999999) . "\n\n";

        $propsText = "Properties:\n";
        foreach ($props as $i => $p) {
            $propsText .= ($i + 1) . ". ID: {$p['id']}\n";
            $propsText .= "   {$p['title']}\n";
            $propsText .= "   {$p['bedrooms']} BHK, {$p['bathrooms']} Bath\n";
            $propsText .= "   {$p['area']} sqft\n";
            $propsText .= "   {$p['city']}, {$p['location']}\n";
            $propsText .= "   ₹" . number_format($p['price']) . "\n\n";
        }

        return $prefsText . $propsText . "
Rank top 5 properties. Return JSON:
{
  \"recommendations\": [
    {
      \"property_id\": 3,
      \"match_score\": 95,
      \"reasoning\": \"Explanation\",
      \"highlights\": [\"Feature 1\", \"Feature 2\", \"Feature 3\"]
    }
  ],
  \"summary\": \"Overall summary\"
}";
    }
}