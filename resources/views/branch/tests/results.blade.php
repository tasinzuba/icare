@extends('layouts.branch')

@section('title', 'Test Results')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.tests.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Tests
    </a>
</div>

<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Test Results</h1>
                <p class="text-gray-600">{{ $attempt->created_at->format('F d, Y - h:i A') }}</p>
            </div>
            @php
                // Resolve the band the way every other result screen does; compared against null
                // because 0.0 is a real score.
                $sectionName = optional($attempt->testSet->section)->name ?? 'reading';
                $card = \App\Services\StudentSectionResultService::cardData($attempt, $sectionName);
            @endphp
            @if(in_array($card['state'], ['scored', 'ai'], true))
            <div class="text-center">
                <p class="text-5xl font-bold text-indigo-600">{{ number_format($card['band'], 1) }}</p>
                <p class="text-gray-600">Band Score</p>
                @if(!empty($card['total']))
                    <p class="text-xs text-gray-500 mt-1">{{ $card['correct'] ?? 0 }}/{{ $card['total'] }} correct</p>
                @endif
            </div>
            @elseif($card['state'] === 'pending')
            <div class="text-center">
                <p class="text-lg font-semibold text-amber-600">Awaiting evaluation</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Student Info -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Information</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-500">Name</p>
                <p class="font-medium">{{ $attempt->user->name ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Student ID</p>
                <p class="font-medium">{{ $attempt->user->offlineEnrollment->student_id ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">{{ $attempt->user->email ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Test Type</p>
                <p class="font-medium">{{ $attempt->testSet->section->name ?? 'Full Test' }}</p>
            </div>
        </div>
    </div>

    <!-- Section Scores (if full test) -->
    @if(isset($sectionScores) && count($sectionScores) > 0)
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Section Breakdown</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($sectionScores as $section => $score)
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <p class="text-3xl font-bold text-indigo-600">{{ $score ?? '-' }}</p>
                <p class="text-sm text-gray-600">{{ $section }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Status -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Test Status</h2>
        <div class="flex items-center justify-between">
            <div>
                {{-- StudentAttempt has no is_completed column; use status --}}
                @if($attempt->status === 'completed')
                <span class="px-4 py-2 text-lg rounded-full bg-green-100 text-green-800">
                    <i class="fas fa-check-circle mr-2"></i> Completed
                </span>
                @elseif($attempt->status === 'in_progress')
                <span class="px-4 py-2 text-lg rounded-full bg-yellow-100 text-yellow-800">
                    <i class="fas fa-clock mr-2"></i> In Progress
                </span>
                @else
                <span class="px-4 py-2 text-lg rounded-full bg-red-100 text-red-800">
                    <i class="fas fa-times-circle mr-2"></i> {{ ucfirst($attempt->status) }}
                </span>
                @endif
            </div>
            <div class="text-right text-sm text-gray-500">
                <p>Started: {{ optional($attempt->start_time ?? $attempt->created_at)->format('h:i A') }}</p>
                {{-- the column is end_time, not completed_at --}}
                @if($attempt->end_time)
                <p>Completed: {{ $attempt->end_time->format('h:i A') }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Student answers --}}
    <div class="bg-white rounded-xl shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Answers</h2>

        @if(!empty($responses))
            <div class="flex flex-wrap items-center gap-3 mb-5 text-sm">
                <span class="px-3 py-1 rounded-full" style="background:#dcfce7;color:#166534;font-weight:600;">
                    {{ collect($responses)->where('is_correct', true)->count() }} correct
                </span>
                <span class="px-3 py-1 rounded-full" style="background:#fee2e2;color:#991b1b;font-weight:600;">
                    {{ collect($responses)->where('is_answered', true)->where('is_correct', false)->count() }} wrong
                </span>
                <span class="px-3 py-1 rounded-full" style="background:#f1f5f9;color:#475569;font-weight:600;">
                    {{ collect($responses)->where('is_answered', false)->count() }} unanswered
                </span>
                <span class="text-gray-500">out of {{ count($responses) }} questions</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($responses as $item)
                    <div class="py-3 flex items-start gap-4">
                        <span class="w-8 shrink-0 text-sm font-bold text-gray-700">{{ $item['number'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-700">
                                {{ Str::limit(trim(strip_tags(html_entity_decode($item['content'] ?? ''))), 120) }}
                            </p>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-6 gap-y-1 text-sm">
                                <span>
                                    <span class="text-gray-500">Answer:</span>
                                    @if($item['is_answered'] && $item['student_answer'] !== 'No answer')
                                        <span style="font-weight:600;color:{{ $item['is_correct'] ? '#166534' : '#991b1b' }};">{{ $item['student_answer'] }}</span>
                                    @else
                                        <span class="text-gray-400 italic">No answer</span>
                                    @endif
                                </span>
                                @if(!$item['is_correct'] && $item['correct_answer'] !== '' && $item['correct_answer'] !== null)
                                    <span>
                                        <span class="text-gray-500">Correct:</span>
                                        <span class="font-medium text-green-700">{{ $item['correct_answer'] }}</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if(!$item['is_answered'])
                                <span class="text-gray-400 text-sm">&mdash;</span>
                            @elseif($item['is_correct'])
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Correct
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Incorrect
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        @elseif($attempt->answers->count() > 0)
            {{-- Writing / speaking: show what the student submitted --}}
            @foreach($attempt->answers->sortBy('question.order_number') as $answer)
                <div class="py-4 border-b border-gray-100 last:border-0">
                    <p class="text-sm font-medium text-gray-900 mb-2">
                        Task {{ $answer->question->order_number ?? $loop->iteration }}
                    </p>
                    <div class="prose prose-sm max-w-none text-gray-600 mb-3">
                        {!! $answer->question->content ?? '' !!}
                    </div>

                    @if($answer->speakingRecording)
                        <audio controls class="w-full max-w-md">
                            <source src="{{ $answer->speakingRecording->file_url }}">
                        </audio>
                    @elseif($answer->answer)
                        <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-800 whitespace-pre-line">{{ $answer->answer }}</div>
                        <p class="text-xs text-gray-400 mt-1">{{ str_word_count($answer->answer) }} words</p>
                    @else
                        <p class="text-sm text-gray-400 italic">No response submitted</p>
                    @endif
                </div>
            @endforeach

        @else
            <p class="text-sm text-gray-500">No answers recorded for this attempt.</p>
        @endif
    </div>
</div>
@endsection
