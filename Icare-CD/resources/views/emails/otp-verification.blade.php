@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        Verify Your Email
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $user->name ?? 'there' }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Enter this code to verify your email address:
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

    <!-- Button -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="border-radius: 6px; background-color: #C8102E;">
                            <a href="{{ route('auth.verify.otp') }}?code={{ $otp }}" style="display: inline-block; padding: 12px 24px; font-size: 14px; color: #FFFFFF; text-decoration: none; font-weight: 500;">
                                Verify Email
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0 0; color: #9CA3AF; font-size: 13px;">
        If you didn't request this, please ignore this email.
    </p>
@endsection
