@extends('emails.layouts.layout')

@section('title', 'Welcome to RealEstate')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>{{ $user->name }}</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    Welcome to {{ config('app.name') }}! Your account has been successfully created.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                Account Details
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Email:</strong> {{ $user->email }}<br>
                <strong>Role:</strong> {{ ucfirst($user->role) }}<br>
                <strong>Registered:</strong> {{ $user->created_at->format('M d, Y H:i') }}
            </div>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#f0fdf4; border-left:4px solid #22c55e; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#15803d;">✓ Account Active</div>
    <div style="font-size:14px; color:#374151;">You can now login and start exploring our platform.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Thank you for joining us. We're excited to have you on board!</p>
</div>
@endsection