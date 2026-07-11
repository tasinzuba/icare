@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        New Sign-in Detected
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $user->name }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        A new sign-in was detected on your account.
    </p>

    <!-- Details -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color: #F9FAFB; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px;">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Device</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $device->device_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Browser</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $device->browser }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Location</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $device->location ?? 'Unknown' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">IP Address</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $device->ip_address }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Time</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $device->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 14px;">
        If this was you, no action is needed. If you don't recognize this activity, please change your password immediately.
    </p>

    <!-- Button -->
    <table border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td style="border-radius: 6px; background-color: #C8102E;">
                <a href="{{ route('password.request') }}" style="display: inline-block; padding: 12px 24px; font-size: 14px; color: #FFFFFF; text-decoration: none; font-weight: 500;">
                    Change Password
                </a>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0 0; color: #9CA3AF; font-size: 13px;">
        Questions? Contact support@cdielts.com
    </p>
@endsection
