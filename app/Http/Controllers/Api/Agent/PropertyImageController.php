<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;

class PropertyImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageUploadService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Upload single image
     */
    public function uploadSingle(Request $request, $propertyId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'is_primary' => 'boolean',
        ]);

        // Get property and verify ownership
        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        try {
            $image = $this->imageService->uploadPropertyImage(
                $property,
                $request->file('image'),
                $request->boolean('is_primary', false)
            );

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => [
                    'image' => [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'is_primary' => $image->is_primary,
                        'sort_order' => $image->sort_order,
                    ]
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload multiple images
     */
    public function uploadMultiple(Request $request, $propertyId)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // Get property and verify ownership
        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        try {
            $images = $this->imageService->uploadMultipleImages(
                $property,
                $request->file('images')
            );

            return response()->json([
                'success' => true,
                'message' => count($images) . ' images uploaded successfully',
                'data' => [
                    'images' => collect($images)->map(fn($img) => [
                        'id' => $img->id,
                        'url' => $img->image_url,
                        'is_primary' => $img->is_primary,
                        'sort_order' => $img->sort_order,
                    ])
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload images: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all images for a property
     */
    public function index($propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $images = $property->images()->orderBy('sort_order')->get()->map(fn($img) => [
            'id' => $img->id,
            'url' => $img->image_url,
            'is_primary' => $img->is_primary,
            'sort_order' => $img->sort_order,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'images' => $images
            ]
        ]);
    }

    /**
     * Delete image
     */
    public function destroy($propertyId, $imageId)
    {
        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        $image = PropertyImage::where('id', $imageId)
            ->where('property_id', $property->id)
            ->firstOrFail();

        try {
            $this->imageService->deletePropertyImage($image);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set primary image
     */
    public function setPrimary($propertyId, $imageId)
    {
        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        $image = PropertyImage::where('id', $imageId)
            ->where('property_id', $property->id)
            ->firstOrFail();

        try {
            $this->imageService->setPrimaryImage($image);

            return response()->json([
                'success' => true,
                'message' => 'Primary image updated successfully',
                'data' => [
                    'image' => [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'is_primary' => true,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update primary image: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder images
     */
    public function reorder(Request $request, $propertyId)
    {
        $request->validate([
            'image_ids' => 'required|array|min:1',
            'image_ids.*' => 'required|integer|exists:property_images,id',
        ]);

        $property = Property::where('id', $propertyId)
            ->where('agent_id', auth()->id())
            ->firstOrFail();

        try {
            $images = $this->imageService->reorderImages($property, $request->image_ids);

            return response()->json([
                'success' => true,
                'message' => 'Images reordered successfully',
                'data' => [
                    'images' => $images->map(fn($img) => [
                        'id' => $img->id,
                        'url' => $img->image_url,
                        'is_primary' => $img->is_primary,
                        'sort_order' => $img->sort_order,
                    ])
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reorder images: ' . $e->getMessage(),
            ], 500);
        }
    }
}