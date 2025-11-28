<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LoanEligibilityResultMail;
use App\Mail\ApiFailureAlertMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LoanCalculatorController extends Controller
{
    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'applicant_name' => 'required|string',
            'email' => 'required|email',
            'monthly_income' => 'required|numeric',
            'existing_emi' => 'nullable|numeric',
            'loan_amount' => 'required|numeric',
            'interest_rate' => 'required|numeric',
            'tenure_years' => 'required|integer',
        ]);

        try {
            // Calculate EMI
            $P = $validated['loan_amount'];
            $r = $validated['interest_rate'] / 12 / 100;
            $n = $validated['tenure_years'] * 12;
            
            $emi = ($P * $r * pow(1 + $r, $n)) / (pow(1 + $r, $n) - 1);
            $maxEmi = ($validated['monthly_income'] * 0.5) - ($validated['existing_emi'] ?? 0);
            $eligible = $emi <= $maxEmi;
            
            $result = [
                'applicant_name' => $validated['applicant_name'],
                'loan_amount' => $P,
                'monthly_emi' => round($emi, 2),
                'total_payable' => round($emi * $n, 2),
                'total_interest' => round(($emi * $n) - $P, 2),
                'eligible' => $eligible,
                'max_eligible_emi' => round($maxEmi, 2),
            ];

            Mail::to($validated['email'])->send(new LoanEligibilityResultMail($result));

            return response()->json([
                'success' => true,
                'message' => 'Loan eligibility result sent to your email',
                'data' => $result,
            ]);
            
        } catch (\Exception $e) {
            // Send API failure alert
            if ($systemEmail = config('mail.system_alert_email')) {
                Mail::to($systemEmail)->send(new ApiFailureAlertMail($e, [
                    'controller' => 'LoanCalculatorController',
                    'method' => 'calculate',
                    'request' => $request->all(),
                ]));
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Loan calculation failed',
            ], 500);
        }
    }
}