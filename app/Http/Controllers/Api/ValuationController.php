<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ValuationReportMail;
use App\Mail\ApiFailureAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ValuationController extends Controller
{
    public function calculate(Request $request)
    {
        // throw new \Exception('Testing API failure email');
        
        $validated = $request->validate([
            'property_address' => 'required|string',
            'property_type' => 'required|string',
            'area' => 'required|numeric',
            'bedrooms' => 'required|integer',
            'bathrooms' => 'required|integer',
            'email' => 'required|email',
        ]);

        try {
            // Simple valuation logic
            $basePrice = 50000;
            $estimatedValue = $validated['area'] * $basePrice;
            
            $result = [
                'property_address' => $validated['property_address'],
                'estimated_value' => $estimatedValue,
                'min_value' => $estimatedValue * 0.9,
                'max_value' => $estimatedValue * 1.1,
                'valuation_date' => now()->format('Y-m-d'),
            ];

            Mail::to($validated['email'])->send(new ValuationReportMail($result));

            return response()->json([
                'success' => true,
                'message' => 'Valuation report sent to your email',
                'data' => $result,
            ]);
            
        } catch (\Exception $e) {
            // Send API failure alert
            if ($systemEmail = config('mail.system_alert_email')) {
                Mail::to($systemEmail)->send(new ApiFailureAlertMail($e, [
                    'controller' => 'ValuationController',
                    'method' => 'calculate',
                    'request' => $request->all(),
                ]));
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Valuation calculation failed',
            ], 500);
        }
    }
}