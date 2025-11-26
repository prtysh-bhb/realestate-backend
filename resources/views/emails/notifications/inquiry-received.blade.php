@extends('emails.layouts.layout')

@section('title', 'New Inquiry Received')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $agentName }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    You have received a new inquiry from a potential customer interested in your property.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $propertyTitle }}
            </div>
            
            <div style="color:#374151; font-size:14px; line-height:1.6; margin-top:12px;">
                <strong>Customer:</strong> {{ $customerName }}<br>
                <strong>Email:</strong> <a href="mailto:{{ $customerEmail }}" style="color:#2563eb;">{{ $customerEmail }}</a><br>
                <strong>Phone:</strong> {{ $customerPhone }}<br>
            </div>

            <div style="margin-top:12px; padding:12px; background:#f9fafb; border-radius:6px;">
                <div style="font-size:13px; font-weight:600; color:#374151; margin-bottom:6px;">Message:</div>
                <div style="font-size:14px; color:#4b5563; line-height:1.6;">
                    {{ $inquiryMessage }}
                </div>
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Received:</strong> {{ $createdAt }} &nbsp; • &nbsp;
                        <strong>Inquiry ID:</strong> #{{ $inquiryId }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#f3f6ff; border-left:4px solid #2563eb; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px;">Action Required</div>
    <div style="font-size:14px; color:#374151;">Respond quickly to increase your chances of closing this deal!</div>
</div>

<!-- Buttons -->
<table cellpadding="0" cellspacing="0" role="presentation" style="margin-top:10px; width:100%;">
    <tr>
        <td align="left">
            <!-- Primary CTA -->
            <a href="{{ $actionUrl }}"
                style="background:linear-gradient(90deg,#2563eb 0%, #06b6d4 100%); color:#ffffff; text-decoration:none; padding:11px 18px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px;">
                View Inquiry
            </a>
            <!-- Secondary CTA -->
            <a href="mailto:{{ $customerEmail }}"
                style="margin-left:12px; color:#2563eb; text-decoration:none; padding:10px 16px; border-radius:8px; display:inline-block; font-weight:600; font-size:14px; border:1px solid #e6efff;">
                Reply via Email
            </a>
        </td>
    </tr>
</table>
@endsection