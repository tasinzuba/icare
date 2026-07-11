@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        Verify Email Change
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $userName }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Enter this code to verify your email change request:
    </p>

    <!-- OTP Code -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td align="center">
                <div style="display: inline-block; background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 6px; padding: 16px 32px;">
                    <span style="font-size: 28px; font-weight: 700; color: #111827; letter-spacing: 6px;">{{ $otp }}</span>
                </div>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 24px 0; color: #6B7280; font-size: 14px; text-align: center;">
        This code expires in 10 minutes.
    </p>

    <p style="margin: 0; color: #9CA3AF; font-size: 13px;">
        If you didn't request this change, please ignore this email.
    </p>
@endsection
