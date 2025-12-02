<?php

namespace App\Services;

use App\Models\PropertyReview;
use App\Models\PropertyRatingStat;
use App\Models\AgentReview;
use App\Models\User;

class ReviewService
{
    /* -----------------------------------------
     | PROPERTY REVIEWS
     ----------------------------------------- */

    public function getPropertyReviews($propertyId)
    {
        return PropertyReview::where('property_id', $propertyId)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();
    }

    public function storePropertyReview($propertyId, $userId, $validated)
    {
        $review = PropertyReview::create([
            'property_id' => $propertyId,
            'user_id' => $userId,
            ...$validated
        ]);

        $this->updatePropertyStats($propertyId);

        return $review;
    }

    private function updatePropertyStats($propertyId)
    {
        $stats = PropertyReview::where('property_id', $propertyId);

        PropertyRatingStat::updateOrCreate(
            ['property_id' => $propertyId],
            [
                'avg_construction' => $stats->avg('construction'),
                'avg_amenities' => $stats->avg('amenities'),
                'avg_management' => $stats->avg('management'),
                'avg_connectivity' => $stats->avg('connectivity'),
                'avg_green_area' => $stats->avg('green_area'),
                'avg_locality' => $stats->avg('locality'),

                'overall_rating' => $stats->selectRaw('
                    (AVG(construction) + AVG(amenities) + AVG(management) +
                     AVG(connectivity) + AVG(green_area) + AVG(locality)) / 6 as total
                ')->value('total')
            ]
        );
    }


    /* -----------------------------------------
     | AGENT REVIEWS
     ----------------------------------------- */

    public function getAgentReviews($agentId)
    {
        return AgentReview::where('agent_id', $agentId)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();
    }

    public function storeAgentReview($agentId, $userId, $validated)
    {
        $review = AgentReview::create([
            'agent_id' => $agentId,
            'user_id' => $userId,
            ...$validated
        ]);

        $this->updateAgentAverage($agentId);

        return $review;
    }

    private function updateAgentAverage($agentId)
    {
        $avg = AgentReview::where('agent_id', $agentId)->avg('rating');

        User::where('id', $agentId)->update([
            'avg_agent_rating' => $avg ?? 0
        ]);
    }
}
