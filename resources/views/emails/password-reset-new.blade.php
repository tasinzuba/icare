@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        Reset Your Password
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $user->name ?? 'there' }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        We received a request to reset your password. Click the button below to create a new password:
    </p>

    <!-- Button -->
    <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
        <tr>
            <td style="border-radius: 6px; background-color: #C8102E;">
                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 12px 24px; font-size: 14px; color: #FFFFFF; text-decoration: none; font-weight: 500;">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 8px 0; color: #6B7280; font-size: 13px;">
        Or copy this link:
    </p>
    <p style="margin: 0 0 24px 0; word-break: break-all;">
        <a href="{{ $resetUrl }}" style="color: #C8102E; font-size: 13px;">{{ $resetUrl }}</a>
    </p>

    <p style="margin: 0 0 16px 0; color: #6B7280; font-size: 14px;">
        This link expires in 60 minutes.
    </p>

    <p style="margin: 0; color: #9CA3AF; font-size: 13px;">
        If you didn't request this, please ignore this email.
    </p>
@endsection
