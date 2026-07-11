@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        Welcome to CD IELTS!
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $user->name ?? 'there' }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Your account has been created at <strong>{{ $branch->name }}</strong>. Below are your login credentials:
    </p>

    <!-- Credentials Box -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td>
                <div style="background-color: #F9FAFB; border: 1px solid #E5E7EB; border-radius: 8px; padding: 20px;">
                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 12px;">
                                <span style="color: #6B7280; font-size: 13px;">Student ID</span><br>
                                <span style="color: #111827; font-size: 16px; font-weight: 600;">{{ $enrollment->student_id }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 12px;">
                                <span style="color: #6B7280; font-size: 13px;">Email</span><br>
                                <span style="color: #111827; font-size: 16px; font-weight: 600;">{{ $user->email }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <span style="color: #6B7280; font-size: 13px;">Password</span><br>
                                <span style="color: #C8102E; font-size: 18px; font-weight: 700; letter-spacing: 1px;">{{ $password }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Enrollment Details -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td>
                <p style="margin: 0 0 12px 0; color: #111827; font-size: 14px; font-weight: 600;">Your Package Details:</p>
                <div style="background-color: #EEF2FF; border: 1px solid #C7D2FE; border-radius: 8px; padding: 16px;">
                    <table border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td style="padding-bottom: 8px; color: #4338CA; font-size: 14px;">
                                <strong>Full Tests:</strong> {{ $enrollment->full_tests_allowed }} test(s)
                            </td>
                        </tr>
                        @if($enrollment->section_tests_allowed > 0)
                        <tr>
                            <td style="padding-bottom: 8px; color: #4338CA; font-size: 14px;">
                                <strong>Section Tests:</strong> {{ $enrollment->section_tests_allowed }} test(s)
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="color: #4338CA; font-size: 14px;">
                                <strong>Valid Until:</strong> {{ $enrollment->valid_until->format('F d, Y') }}
                            </td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- Login Button -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="border-radius: 6px; background-color: #C8102E;">
                            <a href="{{ $loginUrl }}" style="display: inline-block; padding: 14px 32px; font-size: 15px; color: #FFFFFF; text-decoration: none; font-weight: 600;">
                                Login to Start Practice
                            </a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 24px 0 0 0; color: #6B7280; font-size: 13px; text-align: center;">
        Please keep your password secure. You can change it after logging in.
    </p>

    <p style="margin: 16px 0 0 0; color: #9CA3AF; font-size: 12px;">
        If you have any questions, please contact <strong>{{ $branch->name }}</strong>.
    </p>
@endsection
