<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class AmenitiesController extends Controller
{
    /**
     * Get list of all available amenities
     */
    public function index()
    {
        $amenities = config('amenities.list', []);
        $propertyTypes = config('amenities.property_types', []);

        // Transform amenities
        $amenitiesList = [];
        foreach ($amenities as $key => $label) {
            $amenitiesList[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        // Transform property types
        $propertyTypesList = [];
        foreach ($propertyTypes as $key => $label) {
            $propertyTypesList[] = [
                'key' => $key,
                'label' => $label,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'amenities' => $amenitiesList,
                'property_types' => $propertyTypesList,
            ]
        ]);
    }
}