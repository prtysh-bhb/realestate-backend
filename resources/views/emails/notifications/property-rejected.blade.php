@extends('emails.layouts.layout')

@section('title', 'Property Rejected')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $agentName }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    We've reviewed your property listing and unfortunately it has been rejected.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fef2f2; border:1px solid #fecaca; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $propertyTitle }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6; margin-top:12px;">
                <strong style="color:#dc2626;">Rejection Reason:</strong><br>
                {{ $reason }}
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Rejected:</strong> {{ $rejectedAt }} &nbsp; • &nbsp;
                        <strong>Property ID:</strong> #{{ $propertyId }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px;">Action Required</div>
    <div style="font-size:14px; color:#374151;">Please update your property listing and resubmit for review.</div>
</div>

<!-- Buttons -->
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-top:10px; width:100%;">
    <tr>
        <td align="left">
            <!-- Primary CTA -->
            <a href="{{ $actionUrl }}"
                style="background:linear-gradient(90deg,#2563eb 0%, #06b6d4 100%); color:#ffffff; text-decoration:none; padding:11px 18px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px;">
                Edit Property
            </a>
        </td>
    </tr>
</table>
@endsection