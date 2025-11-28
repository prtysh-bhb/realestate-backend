@extends('emails.layouts.layout')

@section('title', 'Property Status Update')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $user->name }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    The status of a property you favorited has been updated.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ $property->title }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>New Status:</strong> {{ ucfirst($property->status) }}<br>
                <strong>Location:</strong> {{ $property->address }}<br>
                <strong>Price:</strong> ₹{{ number_format($property->price) }}
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Property ID:</strong> #{{ $property->id }} &nbsp; • &nbsp;
                        <strong>Updated:</strong> {{ now()->format('M d, Y H:i') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b45309;">📢 Status Changed</div>
    <div style="font-size:14px; color:#374151;">This property's status has been updated. Check it out!</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">View the updated property details on our platform.</p>
</div>
@endsection