@extends('emails.layouts.layout')

@section('title', 'Inquiry Confirmation')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $inquiry->customer_name }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Thank you for your inquiry! We've received your message regarding the property.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $inquiry->property->title }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Location:</strong> {{ $inquiry->property->address }}<br>
                <strong>Price:</strong> ₹{{ number_format($inquiry->property->price) }}<br>
                <strong>Your Message:</strong> {{ $inquiry->message }}
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Inquiry ID:</strong> #{{ $inquiry->id }} &nbsp; • &nbsp;
                        <strong>Submitted:</strong> {{ $inquiry->created_at->format('M d, Y H:i') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b45309;">⏱ Response Time</div>
    <div style="font-size:14px; color:#374151;">Our agent will contact you within 24 hours.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Agent: <strong>{{ $inquiry->agent->name }}</strong></p>
    <p style="margin:0;">Contact: {{ $inquiry->agent->email }}</p>
</div>
@endsection