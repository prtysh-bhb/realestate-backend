<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\RatingStat;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index($propertyId)
    {
       
        $reviews = Review::where('property_id', $propertyId)
            ->with('user:id,name,avatar')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    public function store(Request $request, $propertyId)
    {
        $validator = Validator::make($request->all(), [
            'construction' => 'required|integer|min:1|max:5',
            'amenities' => 'required|integer|min:1|max:5',
            'management' => 'required|integer|min:1|max:5',
            'connectivity' => 'required|integer|min:1|max:5',
            'green_area' => 'required|integer|min:1|max:5',
            'locality' => 'required|integer|min:1|max:5',
            'positive_comment' => 'nullable|string|max:2000',
            'negative_comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $review = Review::create([
            'property_id' => $propertyId,
            'user_id' => auth()->id(),
            ...$validator->validated()
        ]);

        $this->updateStats($propertyId);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ]);
    }

    private function updateStats($propertyId)
    {
        $stats = Review::where('property_id', $propertyId);

        RatingStat::updateOrCreate(
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
}