<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PropertyReviewController extends Controller
{
    protected $reviews;

    public function __construct(ReviewService $reviews)
    {
        $this->reviews = $reviews;
    }

    public function index($propertyId)
    {
        $data = $this->reviews->getPropertyReviews($propertyId);

        return response()->json([
            'success' => true,
            'data' => $data
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

        $review = $this->reviews->storePropertyReview(
            $propertyId,
            auth()->id(),
            $validator->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review
        ]);
    }
}
