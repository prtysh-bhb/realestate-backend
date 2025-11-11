<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PropertyImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ImageUploadService
{
    public function uploadPropertyImage(Property $property, UploadedFile $file, bool $isPrimary = false)
    {
        // Validate file
        $validated = validator(['image' => $file], [
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120' // 5MB max
        ])->validate();

        // Generate unique filename
        $filename = uniqid('property_') . '.' . $file->getClientOriginalExtension();
        
        // Store file in storage/app/public/properties/{property_id}/
        $path = $file->storeAs(
            "properties/{$property->id}",
            $filename,
            'public'
        );

        // If this is set as primary, unset other primary images
        if ($isPrimary) {
            $property->images()->update(['is_primary' => false]);
        }

        // Create image record
        $image = PropertyImage::create([
            'property_id' => $property->id,
            'image_path' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $property->images()->count(),
        ]);

        return $image;
    }

    public function uploadMultipleImages(Property $property, array $files)
    {
        $uploadedImages = [];
        $isFirst = $property->images()->count() === 0;

        foreach ($files as $index => $file) {
            // First image is primary if no images exist
            $isPrimary = $isFirst && $index === 0;
            $uploadedImages[] = $this->uploadPropertyImage($property, $file, $isPrimary);
        }

        return $uploadedImages;
    }

    public function deletePropertyImage(PropertyImage $image)
    {
        // Delete file from storage
        Storage::disk('public')->delete($image->image_path);

        // If this was primary, make another image primary
        if ($image->is_primary) {
            $nextImage = PropertyImage::where('property_id', $image->property_id)
                ->where('id', '!=', $image->id)
                ->orderBy('sort_order')
                ->first();
            
            if ($nextImage) {
                $nextImage->update(['is_primary' => true]);
            }
        }

        // Delete database record
        $image->delete();

        return true;
    }

    public function setPrimaryImage(PropertyImage $image)
    {
        // Unset all primary images for this property
        PropertyImage::where('property_id', $image->property_id)
            ->update(['is_primary' => false]);

        // Set this image as primary
        $image->update(['is_primary' => true]);

        return $image;
    }

    public function reorderImages(Property $property, array $imageIds)
    {
        foreach ($imageIds as $index => $imageId) {
            PropertyImage::where('id', $imageId)
                ->where('property_id', $property->id)
                ->update(['sort_order' => $index]);
        }

        return $property->images()->orderBy('sort_order')->get();
    }
}