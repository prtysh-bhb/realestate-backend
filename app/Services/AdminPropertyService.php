<?php

namespace App\Services;

use App\Models\Property;

class AdminPropertyService
{
    public function getAllPropertiesForAdmin($filters = [])
    {
        $query = Property::with(['agent:id,name,email,avatar'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['approval_status']) && !empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (isset($filters['agent_id']) && !empty($filters['agent_id'])) {
            $query->where('agent_id', $filters['agent_id']);
        }

        if (isset($filters['property_type']) && !empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        if (isset($filters['type']) && !empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->paginate(15);
    }

    public function getPropertyById($id)
    {
        $property = Property::with(['agent:id,name,email,avatar,phone,company_name'])
            ->findOrFail($id);

        return $property;
    }

    public function approveProperty($id, $adminId)
    {
        $property = Property::findOrFail($id);

        if ($property->approval_status === 'approved') {
            throw new \Exception('Property is already approved');
        }

        $property->update([
            'approval_status' => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return $property->fresh(['agent:id,name,email,avatar']);
    }

    public function rejectProperty($id, $reason, $adminId)
    {
        $property = Property::findOrFail($id);

        $property->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);

        return $property->fresh(['agent:id,name,email,avatar']);
    }

    public function updatePropertyStatus($id, $status)
    {
        $property = Property::findOrFail($id);

        $property->update(['status' => $status]);

        return $property->fresh(['agent:id,name,email,avatar']);
    }

    public function getPropertyStatistics()
    {
        return [
            'total' => Property::count(),
            'published' => Property::where('status', 'published')->count(),
            'draft' => Property::where('status', 'draft')->count(),
            'sold' => Property::where('status', 'sold')->count(),
            'rented' => Property::where('status', 'rented')->count(),
            'pending_approval' => Property::where('approval_status', 'pending')->count(),
            'approved' => Property::where('approval_status', 'approved')->count(),
            'rejected' => Property::where('approval_status', 'rejected')->count(),
        ];
    }
}