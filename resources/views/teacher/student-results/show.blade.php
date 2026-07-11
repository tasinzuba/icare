<x-teacher-layout>
    <x-slot:title>Student Result Details</x-slot>

    <x-slot:header>
        <div class="flex items-center space-x-4">
            <a href="{{ route('teacher.student-results.index') }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-semibold text-gray-900">Student Result Details</h1>
        </div>
    </x-slot>

    <div class="container mx-auto px-4 py-8">
        <!-- Student Info Card -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-5">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center">
                            <span class="text-xl font-bold text-white">
                                {{ strtoupper(substr($studentAttempt->user->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">{{ $studentAttempt->user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $studentAttempt->user->email }}</p>
                            @if($studentAttempt->user->branch)
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800 mt-1 inline-block">
                                    <i class="fas fa-building mr-1"></i>{{ $studentAttempt->user->branch->name }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800 mt-1 inline-block">
                                    <i class="fas fa-globe mr-1"></i>Online Student
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @if($studentAttempt->band_score)
                            <div class="text-3xl font-bold text-gray-900">{{ number_format($studentAttempt->band_score, 1) }}</div>
                            <p class="text-sm text-gray-500">Band Score</p>
                        @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Awaiting Evaluation
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Details -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Test Set</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $studentAttempt->testSet->title }}</p>
                @if($studentAttempt->testSet->is_premium)
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-100 text-amber-800 mt-2 inline-block">
                        <i class="fas fa-crown mr-1"></i>Premium
                    </span>
                @else
                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 mt-2 inline-block">
                        Free Test
                    </span>
                @endif
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Section</h3>
                @php
                    $sectionIcons = [
                        'listening' => 'fa-headphones',
                        'reading' => 'fa-book-open',
                        'writing' => 'fa-pen-fancy',
                        'speaking' => 'fa-microphone'
                    ];
                    $sectionColors = [
                        'listening' => 'text-blue-600',
                        'reading' => 'text-purple-600',
                        'writing' => 'text-emerald-600',
                        'speaking' => 'text-orange-600'
                    ];
                @endphp
                <p class="text-lg font-semibold text-gray-900">
                    <i class="fas {{ $sectionIcons[$studentAttempt->testSet->section->name] ?? 'fa-file' }} {{ $sectionColors[$studentAttempt->testSet->section->name] ?? 'text-gray-600' }} mr-2"></i>
                    {{ ucfirst($studentAttempt->testSet->section->name) }}
                </p>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Test Type</h3>
                @if($studentAttempt->fullTestSectionAttempt)
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">
                        <i class="fas fa-clipboard-list mr-1"></i>Full Test
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-700">
                        Single Section
                    </span>
                @endif
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-2">Completed At</h3>
                <p class="text-lg font-semibold text-gray-900">{{ $studentAttempt->updated_at->format('M d, Y g:i A') }}</p>
            </div>
        </div>

        <!-- Human Evaluation Info -->
        @if($studentAttempt->humanEvaluationRequest)
            @php
                $evalRequest = $studentAttempt->humanEvaluationRequest;
                $evaluation = $evalRequest->humanEvaluation;
            @endphp
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Human Evaluation</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="text-lg font-semibold capitalize {{ $evalRequest->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                                {{ $evalRequest->status }}
                            </p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <p class="text-sm text-gray-500">Requested</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $evalRequest->requested_at->format('M d, Y') }}</p>
                        </div>
                        @if($evalRequest->completed_at)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Completed</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $evalRequest->completed_at->format('M d, Y') }}</p>
                            </div>
                        @endif
                        @if($evaluation)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <p class="text-sm text-gray-500">Overall Band Score</p>
                                <p class="text-2xl font-bold text-emerald-600">{{ number_format($evaluation->overall_band_score, 1) }}</p>
                            </div>
                        @endif
                    </div>

                    @if($evaluation)
                        <!-- Task Scores -->
                        @if($evaluation->task_scores)
                            <div class="mb-6">
                                <h4 class="text-md font-semibold text-gray-900 mb-4">Task Scores</h4>
                                <div class="space-y-4">
                                    @foreach($evaluation->task_scores as $taskNum => $taskScore)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <div class="flex items-center justify-between mb-2">
                                                <h5 class="font-medium text-gray-900">Task {{ $taskNum }}</h5>
                                                <span class="text-lg font-bold text-emerald-600">{{ number_format($taskScore['score'] ?? 0, 1) }}</span>
                                            </div>
                                            @if(isset($taskScore['feedback']))
                                                <p class="text-sm text-gray-600 mt-2">{{ $taskScore['feedback'] }}</p>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Strengths & Improvements -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @if($evaluation->strengths && count($evaluation->strengths) > 0)
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 mb-3">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>Strengths
                                    </h4>
                                    <ul class="space-y-2">
                                        @foreach($evaluation->strengths as $strength)
                                            <li class="flex items-start">
                                                <i class="fas fa-plus text-green-500 mr-2 mt-1"></i>
                                                <span class="text-gray-600">{{ $strength }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($evaluation->improvements && count($evaluation->improvements) > 0)
                                <div>
                                    <h4 class="text-md font-semibold text-gray-900 mb-3">
                                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>Areas for Improvement
                                    </h4>
                                    <ul class="space-y-2">
                                        @foreach($evaluation->improvements as $improvement)
                                            <li class="flex items-start">
                                                <i class="fas fa-arrow-right text-yellow-500 mr-2 mt-1"></i>
                                                <span class="text-gray-600">{{ $improvement }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Score Summary for Listening/Reading -->
        @if(in_array($studentAttempt->testSet->section->name, ['listening', 'reading']))
            @php
                $correctCount = $studentAttempt->answers->where('is_correct', true)->count();
                $totalQuestions = $studentAttempt->answers->count();
                $percentage = $totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100) : 0;
            @endphp
            <div class="bg-white shadow rounded-lg mb-6">
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Score Summary</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ ucfirst($studentAttempt->testSet->section->name) }} Test Results</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold {{ $percentage >= 70 ? 'text-green-600' : ($percentage >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $correctCount }}/{{ $totalQuestions }}
                            </div>
                            <p class="text-sm text-gray-500">{{ $percentage }}% Correct</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="h-2.5 rounded-full {{ $percentage >= 70 ? 'bg-green-500' : ($percentage >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Student Answers -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Student Answers</h3>
            </div>
            <div class="p-6">
                @forelse($studentAttempt->answers as $answer)
                    <div class="mb-6 pb-6 border-b border-gray-200 last:border-0 last:mb-0 last:pb-0">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900">
                                Question {{ $loop->iteration }}
                                @if($answer->question)
                                    <span class="text-sm text-gray-500 ml-2">({{ ucfirst($answer->question->type) }})</span>
                                @endif
                            </h4>
                        </div>

                        @if($answer->question)
                            <div class="bg-gray-50 rounded-lg p-4 mb-3">
                                <div class="prose prose-sm max-w-none text-gray-700">
                                    {!! $answer->question->question_text ?? $answer->question->content ?? 'Question text' !!}
                                </div>

                                @if($answer->question->media_url ?? null)
                                    <div class="mt-4">
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                                            <i class="fas fa-image text-gray-400 mr-1"></i> Reference Image / Chart
                                        </p>
                                        <a href="{{ $answer->question->media_url }}" target="_blank" rel="noopener">
                                            <img src="{{ $answer->question->media_url }}"
                                                 alt="Question {{ $loop->iteration }} reference"
                                                 class="rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition" style="max-width: 280px; width: 100%; height: auto;"
                                                 loading="lazy">
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($studentAttempt->testSet->section->name === 'writing')
                            @php
                                $studentText = $answer->answer ?? $answer->text_answer ?? '';
                                $studentText = is_string($studentText) ? $studentText : '';
                                $wordCount = $studentText !== '' ? str_word_count(strip_tags($studentText)) : 0;
                            @endphp
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <p class="text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-pen text-gray-400 mr-1"></i> Student's Response
                                </p>
                                <div class="prose prose-sm max-w-none text-gray-800 whitespace-pre-wrap leading-relaxed">{{ $studentText !== '' ? $studentText : 'No answer provided' }}</div>
                                @if($wordCount > 0)
                                    <p class="text-xs text-gray-500 mt-3 inline-flex items-center gap-1.5">
                                        <i class="fas fa-font text-gray-400"></i>
                                        Word count: <strong>{{ $wordCount }}</strong>
                                    </p>
                                @endif
                            </div>
                        @elseif($studentAttempt->testSet->section->name === 'speaking')
                            @if($answer->speakingRecording)
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <p class="text-sm text-gray-500 mb-2">Audio Recording:</p>
                                    <audio controls class="w-full">
                                        <source src="{{ Storage::url($answer->speakingRecording->file_path) }}" type="audio/webm">
                                        Your browser does not support the audio element.
                                    </audio>
                                    @if($answer->speakingRecording->duration)
                                        <p class="text-xs text-gray-400 mt-2">Duration: {{ gmdate("i:s", $answer->speakingRecording->duration) }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-sm text-gray-400 italic">No recording available</p>
                            @endif
                        @elseif(in_array($studentAttempt->testSet->section->name, ['listening', 'reading']))
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Student's Answer:</p>
                                        <p class="text-base font-medium text-gray-900">
                                            @if($answer->selectedOption)
                                                {{ $answer->selectedOption->option_text }}
                                            @elseif($answer->text_answer)
                                                {{ $answer->text_answer }}
                                            @else
                                                <span class="text-gray-400 italic">No answer</span>
                                            @endif
                                        </p>
                                    </div>
                                    @if($answer->is_correct !== null)
                                        <div class="flex-shrink-0">
                                            @if($answer->is_correct)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check mr-1"></i> Correct
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times mr-1"></i> Incorrect
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                @if($answer->question && $answer->question->correct_answer && !$answer->is_correct)
                                    <div class="mt-3 pt-3 border-t border-gray-100">
                                        <p class="text-sm text-gray-500">Correct Answer:</p>
                                        <p class="text-sm font-medium text-green-600">{{ $answer->question->correct_answer }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No answers found for this attempt.</p>
                @endforelse
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('teacher.student-results.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Results
            </a>
        </div>
    </div>
</x-teacher-layout>
