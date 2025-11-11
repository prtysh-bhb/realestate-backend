<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminPropertyService;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    protected $propertyService;

    public function __construct(AdminPropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    // List all properties with filters
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'approval_status', 'agent_id', 'property_type', 'type']);
        $properties = $this->propertyService->getAllPropertiesForAdmin($filters);

        // Add is_favorite flag (always false for admin)
        $properties->getCollection()->transform(function ($property) {
            $property->is_favorite = false;
            return $property;
        });

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

    // View single property details
    public function show($id)
    {
        try {
            $property = $this->propertyService->getPropertyById($id);
            
            // Add is_favorite flag
            $property->is_favorite = false;

            return response()->json([
                'success' => true,
                'message' => 'Property retrieved successfully',
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

    // Approve property
    public function approve(Request $request, $id)
    {
        try {
            $property = $this->propertyService->approveProperty($id, $request->user()->id);
            
            // Add is_favorite flag
            $property->is_favorite = false;

            return response()->json([
                'success' => true,
                'message' => 'Property approved successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Reject property
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $property = $this->propertyService->rejectProperty(
                $id,
                $request->reason,
                $request->user()->id
            );
            
            // Add is_favorite flag
            $property->is_favorite = false;

            return response()->json([
                'success' => true,
                'message' => 'Property rejected successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Update property status
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,published,sold,rented',
        ]);

        try {
            $property = $this->propertyService->updatePropertyStatus($id, $request->status);
            
            // Add is_favorite flag
            $property->is_favorite = false;

            return response()->json([
                'success' => true,
                'message' => 'Property status updated successfully',
                'data' => [
                    'property' => $property,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // Get property statistics
    public function statistics()
    {
        $stats = $this->propertyService->getPropertyStatistics();

        return response()->json([
            'success' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => [
                'statistics' => $stats,
            ],
        ]);
    }
}