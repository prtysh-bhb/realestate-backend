@extends('emails.layouts.layout')

@section('title', 'Property Approved')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $agentName }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Great news! Your property has been approved and is now live on our platform.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $propertyTitle }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                Your property is now visible to potential buyers/renters and will start receiving inquiries.
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Approved:</strong> {{ $approvedAt }} &nbsp; • &nbsp;
                        <strong>Property ID:</strong> #{{ $propertyId }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#f0fdf4; border-left:4px solid #22c55e; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#15803d;">✓ Property is Live</div>
    <div style="font-size:14px; color:#374151;">Your listing is now searchable and visible to all users on the platform.</div>
</div>

<!-- Buttons -->
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-top:10px; width:100%;">
    <tr>
        <td align="left">
            <!-- Primary CTA -->
            <a href="{{ $actionUrl }}"
                style="background:linear-gradient(90deg,#2563eb 0%, #06b6d4 100%); color:#ffffff; text-decoration:none; padding:11px 18px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px;">
                View Property
            </a>
        </td>
    </tr>
</table>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Start promoting your property to reach more potential customers!</p>
</div>
@endsection