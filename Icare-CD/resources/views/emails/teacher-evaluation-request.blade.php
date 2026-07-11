@extends('emails.layouts.master')

@section('content')
    <h2 style="margin: 0 0 16px 0; color: #111827; font-size: 20px; font-weight: 600;">
        New Evaluation Request
    </h2>

    <p style="margin: 0 0 20px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        Hi {{ $evaluationRequest->teacher->user->name }},
    </p>

    <p style="margin: 0 0 24px 0; color: #4B5563; font-size: 15px; line-height: 1.6;">
        You have received a new evaluation request. Please review and provide feedback by the deadline.
    </p>

    <!-- Request Details -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color: #F9FAFB; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px;">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Student</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $evaluationRequest->studentAttempt->user->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Test Type</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ ucfirst($evaluationRequest->studentAttempt->testSet->section->name) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Test Set</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $evaluationRequest->studentAttempt->testSet->title }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Tokens</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $evaluationRequest->tokens_paid }} tokens</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Deadline</td>
                        <td style="padding: 4px 0; color: #C8102E; font-weight: 500; text-align: right;">{{ $evaluationRequest->deadline_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($evaluationRequest->priority === 'urgent')
    <p style="margin: 0 0 24px 0; color: #C8102E; font-size: 14px; font-weight: 500; text-align: center;">
        This is an urgent request - Please evaluate as soon as possible
    </p>
    @endif

    <!-- Button -->
    @php
        $evaluationUrl = isset($evaluationRequest->id)
            ? route('teacher.evaluations.show', $evaluationRequest->id)
            : url('/teacher/evaluations/1');
    @endphp
    <table border="0" cellspacing="0" cellpadding="0" style="margin-bottom: 24px;">
        <tr>
            <td style="border-radius: 6px; background-color: #C8102E;">
                <a href="{{ $evaluationUrl }}" style="display: inline-block; padding: 12px 24px; font-size: 14px; color: #FFFFFF; text-decoration: none; font-weight: 500;">
                    Start Evaluation
                </a>
            </td>
        </tr>
    </table>

    <!-- Current Stats -->
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="background-color: #F9FAFB; border-radius: 6px; margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px;">
                <p style="margin: 0 0 12px 0; color: #111827; font-size: 14px; font-weight: 600;">Your Current Stats</p>
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Pending Evaluations</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $pendingCount }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Completed This Month</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ $completedThisMonth }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6B7280;">Average Rating</td>
                        <td style="padding: 4px 0; color: #111827; font-weight: 500; text-align: right;">{{ number_format($averageRating, 1) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <p style="margin: 0; color: #9CA3AF; font-size: 13px;">
        Questions? Contact support@cdielts.com
    </p>
@endsection
