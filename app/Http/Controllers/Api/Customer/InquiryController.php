<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Services\InquiryService;
use Illuminate\Http\Request;
use App\Events\InquiryReceivedEvent;

class InquiryController extends Controller
{
    protected $inquiryService;

    public function __construct(InquiryService $inquiryService)
    {
        $this->inquiryService = $inquiryService;
    }

    // Create inquiry
    public function store(Request $request, $propertyId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string|max:1000',
        ]);

        try {
            $inquiry = $this->inquiryService->createInquiry(
                $request->user()->id,
                $propertyId,
                $request->all()
            );

            // Load relationships with avatar
            $inquiry->load(['property:id,title,location,price,type', 'agent:id,name,email,avatar', 'customer:id,name,email,phone']); 

            event(new InquiryReceivedEvent($inquiry));

            return response()->json([
                'success' => true,
                'message' => 'Inquiry sent successfully. The agent will contact you soon.',
                'data' => [
                    'inquiry' => $inquiry,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    // List customer's inquiries
    public function index(Request $request)
    {
        $inquiries = $this->inquiryService->getCustomerInquiries($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Inquiries retrieved successfully',
            'data' => [
                'inquiries' => $inquiries->items(),
                'pagination' => [
                    'total' => $inquiries->total(),
                    'per_page' => $inquiries->perPage(),
                    'current_page' => $inquiries->currentPage(),
                    'last_page' => $inquiries->lastPage(),
                ],
            ],
        ]);
    }

    // View specific inquiry
    public function show(Request $request, $id)
    {
        try {
            $inquiry = $this->inquiryService->getInquiryById(
                $id,
                $request->user()->id,
                'customer'
            );

            return response()->json([
                'success' => true,
                'message' => 'Inquiry details retrieved successfully',
                'data' => [
                    'inquiry' => $inquiry,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}