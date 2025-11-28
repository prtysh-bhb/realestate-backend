@extends('emails.layouts.layout')

@section('title', 'System Exception')

@section('content')
<!-- Greeting -->
<p style="margin:0 0 16px 0; font-size:16px;">
    Hello <strong>Admin</strong>,
</p>

<!-- Intro -->
<p style="margin:0 0 18px 0; color:#333;">
    An unhandled exception has occurred in your application.
</p>

<!-- Content card block -->
<table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin:20px 0 18px 0;">
    <tr>
        <td style="background:#fbfcff; border:1px solid #eef4ff; border-radius:8px; padding:16px;">
            <div style="font-size:15px; color:#0f172a; font-weight:600; margin-bottom:8px;">
                {{ get_class($exception) }}
            </div>
            <div style="color:#374151; font-size:14px; line-height:1.6;">
                <strong>Message:</strong> {{ $exception->getMessage() }}<br>
                <strong>File:</strong> {{ $exception->getFile() }}<br>
                <strong>Line:</strong> {{ $exception->getLine() }}
            </div>

            <!-- meta row -->
            <table cellpadding="0" cellspacing="0" role="presentation" style="width:100%; margin-top:14px;">
                <tr>
                    <td style="font-size:13px; color:#6b7280; padding-top:6px;">
                        <strong>Occurred At:</strong> {{ now()->format('M d, Y H:i:s') }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<!-- Highlight / callout -->
<div style="background:#fee2e2; border-left:4px solid #ef4444; padding:12px 14px; border-radius:6px; color:#1f2937; margin:6px 0 18px 0;">
    <div style="font-size:14px; font-weight:600; margin-bottom:6px; color:#b91c1c;">🚨 Critical Exception</div>
    <div style="font-size:14px; color:#374151;">This exception needs immediate investigation.</div>
</div>

<!-- Additional content area -->
<div style="margin-top:22px; color:#4b5563; font-size:14px;">
    <p style="margin:0 0 10px 0;">Check application logs for full stack trace.</p>
</div>
@endsection