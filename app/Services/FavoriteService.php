<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Property;

class FavoriteService
{
    public function addToFavorites($userId, $propertyId)
    {
        // Check if property exists and is published
        $property = Property::where('id', $propertyId)
            ->where('status', 'published')
            ->where('approval_status', 'approved')
            ->first();

        if (!$property) {
            throw new \Exception('Property not found or not available');
        }

        // Check if already favorited
        $exists = Favorite::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists();

        if ($exists) {
            throw new \Exception('Property is already in your favorites');
        }

        return Favorite::create([
            'user_id' => $userId,
            'property_id' => $propertyId,
        ]);
    }

    public function removeFromFavorites($userId, $propertyId)
    {
        $favorite = Favorite::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if (!$favorite) {
            throw new \Exception('Property not found in your favorites');
        }

        $favorite->delete();
        return true;
    }

    public function getUserFavorites($userId)
    {
        return Favorite::with(['property.agent:id,name,email,avatar'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    public function checkIfFavorited($userId, $propertyId)
    {
        return Favorite::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->exists();
    }

    public function toggleFavorite($userId, $propertyId)
    {
        // Check if property exists and is published
        $property = Property::where('id', $propertyId)
            ->where('status', 'published')
            ->where('approval_status', 'approved')
            ->first();

        if (!$property) {
            throw new \Exception('Property not found or not available');
        }

        // Check if already favorited
        $favorite = Favorite::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if ($favorite) {
            // Remove from favorites
            $favorite->delete();
            return [
                'action' => 'removed',
                'is_favorite' => false,
            ];
        } else {
            // Add to favorites
            Favorite::create([
                'user_id' => $userId,
                'property_id' => $propertyId,
            ]);
            return [
                'action' => 'added',
                'is_favorite' => true,
            ];
        }
    }
}