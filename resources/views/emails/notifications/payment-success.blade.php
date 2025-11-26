@extends('emails.layouts.layout')

@section('title', 'Payment Successful')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $userName }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Your payment has been processed successfully! Your subscription is now active.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $planName }} Plan
            </div>
            
            <div style="color:#374151; font-size:14px; line-height:1.6; margin-top:12px;">
                <strong>Amount Paid:</strong> ${{ number_format($amount, 2) }}<br>
                <strong>Payment ID:</strong> #{{ $paymentId }}<br>
                <strong>Payment Date:</strong> {{ $paymentDate }}<br>
            </div>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#f0fdf4; border-left:4px solid #22c55e; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#15803d;">✓ Subscription Active</div>
    <div style="font-size:14px; color:#374151;">You now have access to all premium features of the {{ $planName }} plan.</div>
</div>

<!-- Buttons -->
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-top:10px; width:100%;">
    <tr>
        <td align="left">
            <!-- Primary CTA -->
            <a href="{{ $actionUrl }}"
                style="background:linear-gradient(90deg,#2563eb 0%, #06b6d4 100%); color:#ffffff; text-decoration:none; padding:11px 18px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px;">
                View Subscription
            </a>
            <!-- Secondary CTA -->
            <a href="{{ $invoiceUrl }}"
                style="margin-left:12px; color:#2563eb; text-decoration:none; padding:10px 16px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px; border:1px solid #e6efff;">
                Download Invoice
            </a>
        </td>
    </tr>
</table>
@endsection