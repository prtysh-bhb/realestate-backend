<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ValuationReportMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ValuationController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'property_address' => 'required|string',
            'property_type' => 'required|string',
            'area' => 'required|numeric',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            'email' => 'required|email',
        ]);

        // Simple valuation logic (you can enhance this with AI/ML)
        $basePrice = 50000; // per sq ft
        $estimatedValue = $validated['area'] * $basePrice;
        
        $result = [
            'property_address' => $validated['property_address'],
            'estimated_value' => $estimatedValue,
            'min_value' => $estimatedValue * 0.9,
            'max_value' => $estimatedValue * 1.1,
            'valuation_date' => now()->format('Y-m-d'),
        ];

        // Send email
        Mail::to($validated['email'])->send(new ValuationReportMail($result));

        return response()->json([
            'success' => true,
            'message' => 'Valuation report sent to your email',
            'data' => $result,
        ]);
    }
}