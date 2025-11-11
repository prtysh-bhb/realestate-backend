<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property; 
use App\Services\PropertyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    // List all properties of logged-in agent
    public function index(Request $request)
    {
        $properties = $this->propertyService->getAllPropertiesByAgent($request->user()->id);

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

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zipcode' => 'required|string',
            'type' => 'required|in:sale,rent',
            'property_type' => 'required|string',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        try {
            $data = $request->except(['images', 'documents']);
            $data['agent_id'] = auth()->id();
            
            if ($request->hasFile('images')) {
                $imagePaths = [];
                
                foreach ($request->file('images') as $index => $image) {
                    $filename = uniqid('property_') . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('properties', $filename, 'public');
                    $imagePaths[] = $path;
                }
                
                $data['images'] = $imagePaths;
                $data['primary_image'] = $imagePaths[0] ?? null;
            }

            // Handle document uploads
            if ($request->hasFile('documents')) {
                $documentPaths = [];
                
                foreach ($request->file('documents') as $file) {
                    $originalName = $file->getClientOriginalName();
                    $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('properties/documents', $filename, 'public');
                    
                    $documentPaths[] = [
                        'name' => $originalName,
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType(),
                        'uploaded_at' => now()->toDateTimeString(),
                    ];
                }
                
                $data['documents'] = $documentPaths;
            }

            $property = Property::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => [
                    'property' => $property
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage(),
            ], 500);
        }
}

    // View specific property
    public function show(Request $request, $id)
    {
        try {
            $property = $this->propertyService->getPropertyById($id, $request->user()->id);

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

    // Update property
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string',
            'address' => 'sometimes|string',
            'city' => 'sometimes|string',
            'state' => 'sometimes|string',
            'zipcode' => 'sometimes|string',
            'type' => 'sometimes|in:sale,rent',
            'property_type' => 'sometimes|string',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'area' => 'sometimes|numeric|min:0',
            'amenities' => 'nullable|array',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_images' => 'nullable|array', // Indices of images to remove
        ]);

        try {
            $property = Property::where('id', $id)
                ->where('agent_id', auth()->id())
                ->firstOrFail();

            $data = $request->except(['images', 'remove_images']);

            // Handle removing images
            if ($request->has('remove_images')) {
                $currentImages = $property->images ?? [];
                $removeIndices = $request->remove_images;
                
                foreach ($removeIndices as $index) {
                    if (isset($currentImages[$index])) {
                        // Delete file from storage
                        Storage::disk('public')->delete($currentImages[$index]);
                        unset($currentImages[$index]);
                    }
                }
                
                $data['images'] = array_values($currentImages); // Re-index array
                
                // Update primary image if removed
                if ($property->primary_image && !in_array($property->primary_image, $data['images'])) {
                    $data['primary_image'] = $data['images'][0] ?? null;
                }
            }

            // Handle adding new images
            if ($request->hasFile('images')) {
                $currentImages = $data['images'] ?? $property->images ?? [];
                
                foreach ($request->file('images') as $image) {
                    $filename = uniqid('property_') . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs('properties', $filename, 'public');
                    $currentImages[] = $path;
                }
                
                $data['images'] = $currentImages;
                
                // Set primary if none exists
                if (!$property->primary_image && !empty($data['images'])) {
                    $data['primary_image'] = $data['images'][0];
                }
            }

            $property->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => [
                    'property' => $property->fresh()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property: ' . $e->getMessage(),
            ], 500);
        }
    }

    // Delete property
    public function destroy(Request $request, $id)
    {
        try {
            $this->propertyService->deleteProperty($id, $request->user()->id);

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}