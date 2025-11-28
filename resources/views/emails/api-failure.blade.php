@extends('emails.layouts.layout')

@section('title', 'API Failure Alert')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>Admin</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    An API call has failed on your platform. Please investigate immediately.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                Error Details
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Message:</strong> {{ $exception->getMessage() }}<br>
                <strong>File:</strong> {{ $exception->getFile() }}<br>
                <strong>Line:</strong> {{ $exception->getLine() }}
            </div>

            @if(!empty($context))
            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Context:</strong> {{ json_encode($context) }}
                    </td>
                </tr>
            </table>
            @endif
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fee2e2; border-left:4px solid #ef4444; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b91c1c;">⚠ Critical Error</div>
    <div style="font-size:14px; color:#374151;">This error requires immediate attention.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Check logs for full stack trace.</p>
</div>
@endsection