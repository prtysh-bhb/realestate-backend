@extends('emails.layouts.layout')

@section('title', 'Loan Eligibility Result')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $data['applicant_name'] }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Your loan eligibility calculation is complete. Here are your results.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                Loan Details
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Loan Amount:</strong> ₹{{ number_format($data['loan_amount']) }}<br>
                <strong>Monthly EMI:</strong> ₹{{ number_format($data['monthly_emi']) }}<br>
                <strong>Total Payable:</strong> ₹{{ number_format($data['total_payable']) }}<br>
                <strong>Total Interest:</strong> ₹{{ number_format($data['total_interest']) }}
            </div>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
@if($data['eligible'])
<div style="background:#f0fdf4; border-left:4px solid #22c55e; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#15803d;">✓ Eligible</div>
    <div style="font-size:14px; color:#374151;">Congratulations! You are eligible for this loan amount.</div>
</div>
@else
<div style="background:#fee2e2; border-left:4px solid #ef4444; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b91c1c;">✗ Not Eligible</div>
    <div style="font-size:14px; color:#374151;">Your EMI exceeds 50% of your monthly income. Max eligible EMI: ₹{{ number_format($data['max_eligible_emi']) }}</div>
</div>
@endif

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Contact our loan advisors for personalized assistance.</p>
</div>
@endsection