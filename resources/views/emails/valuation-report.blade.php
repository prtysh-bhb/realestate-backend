@extends('emails.layouts.layout')

@section('title', 'Property Valuation Report')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Your property valuation report is ready! Here are the results.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $data['property_address'] }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Estimated Value:</strong> ₹{{ number_format($data['estimated_value']) }}<br>
                <strong>Value Range:</strong> ₹{{ number_format($data['min_value']) }} - ₹{{ number_format($data['max_value']) }}<br>
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Valuation Date:</strong> {{ $data['valuation_date'] }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#f0fdf4; border-left:4px solid #22c55e; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#15803d;">✓ Valuation Complete</div>
    <div style="font-size:14px; color:#374151;">This is an estimated market value based on current data.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">For a detailed analysis, please contact our agents.</p>
</div>
@endsection