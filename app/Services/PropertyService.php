<?php

namespace App\Services;

use App\Models\Property;

class PropertyService
{
    public function getAllPropertiesByAgent($agentId, $userId = null)
    {
        $properties = Property::where('agent_id', $agentId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Add is_favorite flag
        if ($userId) {
            $properties->getCollection()->transform(function ($property) use ($userId) {
                $property->is_favorite = $property->isFavoritedBy($userId);
                return $property;
            });
        } else {
            $properties->getCollection()->transform(function ($property) {
                $property->is_favorite = false;
                return $property;
            });
        }

        return $properties;
    }

    public function createProperty(array $data, $agentId)
    {
        $data['agent_id'] = $agentId;
        return Property::create($data);
    }

    public function getPropertyById($id, $agentId)
    {
        $property = Property::where('id', $id)
            ->where('agent_id', $agentId)
            ->first();

        if (!$property) {
            throw new \Exception('Property not found or you do not have permission to access it');
        }

        return $property;
    }

    public function updateProperty($id, array $data, $agentId)
    {
        $property = $this->getPropertyById($id, $agentId);
        $property->update($data);
        return $property;
    }

    public function deleteProperty($id, $agentId)
    {
        $property = $this->getPropertyById($id, $agentId);
        $property->delete();
        return true;
    }

    // Public browsing - ADD avatar HERE
    public function getAllPublishedProperties($userId = null)
    {
        $properties = Property::with([
            'agent:id,name,email,avatar',
            'ratingStat:id,property_id,overall_rating,avg_construction,avg_amenities,avg_management,avg_connectivity,avg_green_area,avg_locality'
            ])
            ->where('status', 'published')
            ->where('approval_status', 'approved')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        // Add is_favorite flag
        if ($userId) {
            $properties->getCollection()->transform(function ($property) use ($userId) {
                $property->is_favorite = $property->isFavoritedBy($userId);
                return $property;
            });
        } else {
            $properties->getCollection()->transform(function ($property) {
                $property->is_favorite = false;
                return $property;
            });
        }

        return $properties;
    }

    // Search - ADD avatar HERE
    public function searchProperties($filters = [], $userId = null)
    {
        $query = Property::with(['agent:id,name,email,avatar'])
            ->where('status', 'published')
            ->where('approval_status', 'approved');
        
        // General location search (searches in multiple fields)
        if (isset($filters['location']) && !empty($filters['location'])) {
            $location = $filters['location'];
            $query->where(function($q) use ($location) {
                $q->where('location', 'like', "%{$location}%")
                ->orWhere('city', 'like', "%{$location}%")
                ->orWhere('state', 'like', "%{$location}%")
                ->orWhere('address', 'like', "%{$location}%")
                ->orWhere('zipcode', 'like', "%{$location}%");
            });
        }

        // Specific city filter
        if (isset($filters['city']) && !empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        // Specific state filter
        if (isset($filters['state']) && !empty($filters['state'])) {
            $query->where('state', 'like', "%{$filters['state']}%");
        }

        // Zipcode filter
        if (isset($filters['zipcode']) && !empty($filters['zipcode'])) {
            $query->where('zipcode', $filters['zipcode']);
        }
        
        if (isset($filters['min_price']) && !empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price']) && !empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        
        // Bedrooms (minimum)
        if (isset($filters['bedrooms']) && !empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        // Exact bedrooms
        if (isset($filters['exact_bedrooms']) && !empty($filters['exact_bedrooms'])) {
            $query->where('bedrooms', $filters['exact_bedrooms']);
        }

        // Bathrooms (minimum)
        if (isset($filters['bathrooms']) && !empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        // Exact bathrooms
        if (isset($filters['exact_bathrooms']) && !empty($filters['exact_bathrooms'])) {
            $query->where('bathrooms', $filters['exact_bathrooms']);
        }

        // Property type (apartment, villa, house, etc.)
        if (isset($filters['property_type']) && !empty($filters['property_type'])) {
            if (is_array($filters['property_type'])) {
                $query->whereIn('property_type', $filters['property_type']);
            } else {
                $query->where('property_type', $filters['property_type']);
            }
        }

        // Type (sale or rent)
        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        
        if (isset($filters['min_area']) && !empty($filters['min_area'])) {
            $query->where('area', '>=', $filters['min_area']);
        }

        if (isset($filters['max_area']) && !empty($filters['max_area'])) {
            $query->where('area', '<=', $filters['max_area']);
        }

        
        if (isset($filters['amenities']) && !empty($filters['amenities'])) {
            $amenities = is_array($filters['amenities']) ? $filters['amenities'] : [$filters['amenities']];
            
            // Check if we need ALL amenities or ANY
            $matchAll = $filters['amenities_match'] ?? 'all'; // 'all' or 'any'
            
            if ($matchAll === 'all') {
                // Must have ALL specified amenities
                foreach ($amenities as $amenity) {
                    $query->whereJsonContains('amenities', $amenity);
                }
            } else {
                // Must have ANY of the specified amenities
                $query->where(function($q) use ($amenities) {
                    foreach ($amenities as $amenity) {
                        $q->orWhereJsonContains('amenities', $amenity);
                    }
                });
            }
        }
        
        if (isset($filters['keyword']) && !empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%")
                ->orWhere('location', 'like', "%{$keyword}%");
            });
        }
        
        if (isset($filters['agent_id']) && !empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }
        
        // Properties with images only
        if (isset($filters['with_images']) && $filters['with_images']) {
            $query->whereNotNull('images')
                ->where('images', '!=', '[]');
        }

        // Properties with primary image only
        if (isset($filters['with_primary_image']) && $filters['with_primary_image']) {
            $query->whereNotNull('primary_image');
        }
        
        if (isset($filters['featured']) && $filters['featured']) {
            // Uncomment if you have is_featured column
            // $query->where('is_featured', true);
        }
        
        if (isset($filters['created_after']) && !empty($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (isset($filters['created_before']) && !empty($filters['created_before'])) {
            $query->where('created_at', '<=', $filters['created_before']);
        }
        
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        // Validate sort fields
        $allowedSortFields = ['price', 'created_at', 'area', 'bedrooms', 'bathrooms', 'title'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'created_at';
        }

        // Validate sort order
        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
        
        $perPage = $filters['per_page'] ?? 12;
        $perPage = min($perPage, 100); // Max 100 per page

        $properties = $query->paginate($perPage);

        // Add is_favorite flag
        if ($userId) {
            $properties->getCollection()->transform(function ($property) use ($userId) {
                $property->is_favorite = $property->isFavoritedBy($userId);
                return $property;
            });
        } else {
            $properties->getCollection()->transform(function ($property) {
                $property->is_favorite = false;
                return $property;
            });
        }

        return $properties;
    }

    // Single property - ADD avatar HERE
    public function getPublicPropertyById($id, $userId = null)
    {
        $property = Property::with('agent:id,name,email,avatar,phone,company_name', 'ratingStat:id,property_id,overall_rating,avg_construction,avg_amenities,avg_management,avg_connectivity,avg_green_area,avg_locality')
            ->where('id', $id)
            ->where('status', 'published')
            ->where('approval_status', 'approved')
            ->first();

        if (!$property) {
            throw new \Exception('Property not found');
        }

        // Add is_favorite flag
        if ($userId) {
            $property->is_favorite = $property->isFavoritedBy($userId);
        } else {
            $property->is_favorite = false;
        }

        return $property;
    }
}