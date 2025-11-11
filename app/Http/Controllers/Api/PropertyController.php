<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    // Browse all published properties
    public function index(Request $request)
    {
        $userId = auth()->guard('sanctum')->check() ? auth()->guard('sanctum')->id() : null;
        $properties = $this->propertyService->getAllPublishedProperties($userId);

        return response()->json([
            'success' => true,
            'message' => 'Properties retrieved successfully',
            'data' => [
                'properties' => $properties->items(),
                'pagination' => [
                    'total' => $properties->total(),
                    'per_page' => $properties->perPage(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                ],
            ],
        ]);
    }

    // Search properties with filters
    public function search(Request $request)
    {
        $filters = $request->only([
            'location', 'city', 'state', 'zipcode',
            'min_price', 'max_price',
            'bedrooms', 'exact_bedrooms', 'bathrooms', 'exact_bathrooms',
            'property_type', 'type',
            'min_area', 'max_area',
            'amenities', 'amenities_match',
            'keyword', 'agent_id',
            'with_images', 'with_primary_image',
            'featured',
            'created_after', 'created_before',
            'sort_by', 'sort_order',
            'per_page',
        ]);

        $userId = auth()->guard('sanctum')->check() ? auth()->guard('sanctum')->id() : null;
        $properties = $this->propertyService->searchProperties($filters, $userId);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    // View single property details
    public function show(Request $request, $id)
    {
        try {
            $userId = auth()->guard('sanctum')->check() ? auth()->guard('sanctum')->id() : null;
            $property = $this->propertyService->getPublicPropertyById($id, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Property details retrieved successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}