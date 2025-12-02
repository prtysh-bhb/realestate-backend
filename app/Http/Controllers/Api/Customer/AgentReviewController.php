<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AgentReviewController extends Controller
{
    protected $reviews;

    public function __construct(ReviewService $reviews)
    {
        $this->reviews = $reviews;
    }

    public function index($agentId)
    {
        $data = $this->reviews->getAgentReviews($agentId);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function store(Request $request, $agentId)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $review = $this->reviews->storeAgentReview(
            $agentId,
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
