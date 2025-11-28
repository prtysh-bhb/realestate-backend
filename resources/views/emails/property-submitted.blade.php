@extends('emails.layouts.layout')

@section('title', 'New Property Submitted')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>Admin</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    A new property has been submitted for approval by an agent.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $property->title }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Agent:</strong> {{ $property->agent->name }}<br>
                <strong>Type:</strong> {{ $property->type }}<br>
                <strong>Price:</strong> ₹{{ number_format($property->price) }}<br>
                <strong>Location:</strong> {{ $property->address }}
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Property ID:</strong> #{{ $property->id }} &nbsp; • &nbsp;
                        <strong>Submitted:</strong> {{ $property->created_at->format('M d, Y H:i') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b45309;">⏱ Pending Approval</div>
    <div style="font-size:14px; color:#374151;">Please review and approve or reject this property.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Login to admin panel to review the property details and images.</p>
</div>
@endsection