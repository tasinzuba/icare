<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($completedSection) }} Section Completed - IELTS Mock Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-2xl">
        <!-- Logo -->
        <div class="text-center mb-8">
            @php
                $websiteSetting = \App\Models\WebsiteSetting::first();
                $logoUrl = $websiteSetting && $websiteSetting->logo_url ? $websiteSetting->logo_url : null;
            @endphp
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="Logo" class="h-8 mx-auto">
            @else
                <span class="text-xl font-bold text-[#C8102E]">CD IELTS</span>
            @endif
        </div>

        <!-- Main Card -->
        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-5 border-b border-gray-100 flex items-start justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">{{ ucfirst($completedSection) }} Section Completed</h1>
                    <p class="text-sm text-gray-500 mt-1">Section successfully submitted</p>
                </div>
                <a href="{{ auth()->user()->isOfflineStudent() ? route('offline.dashboard') : route('student.dashboard') }}"
                   class="px-4 py-2 text-sm font-medium text-[#C8102E] border border-[#C8102E] rounded-lg hover:bg-[#C8102E] hover:text-white transition-colors">
                    Exit
                </a>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">

                <!-- Score Display (if available) -->
                @if(isset($sectionScore) && $sectionScore > 0)
                <div class="bg-[#C8102E]/5 border border-[#C8102E]/20 rounded-xl p-6 text-center">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">{{ ucfirst($completedSection) }} Band Score</p>
                    <div class="text-4xl font-bold text-[#C8102E]">{{ number_format($sectionScore, 1) }}</div>
                </div>
                @endif

                <!-- Progress -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Progress</h3>
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Completed</span>
                        <span class="font-medium">{{ $completedSections }}/{{ $totalSections }}</span>
                    </div>
                    <div class="w-full h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-2 bg-[#C8102E] rounded-full transition-all duration-300" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                </div>

                <!-- Sections Status -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Sections</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @php
                            $icons = [
                                'listening' => 'fa-headphones',
                                'reading' => 'fa-book-open',
                                'writing' => 'fa-pen-fancy',
                                'speaking' => 'fa-microphone'
                            ];
                        @endphp
                        @foreach($availableSections as $section)
                            <div class="rounded-lg p-3 text-center {{ in_array($section, $completedSectionsList) ? 'bg-[#C8102E]/5 border border-[#C8102E]/20' : ($section === $nextSection ? 'bg-gray-100 border border-gray-200' : 'bg-gray-50 border border-gray-100') }}">
                                <i class="fas {{ $icons[$section] }} text-lg mb-2 {{ in_array($section, $completedSectionsList) ? 'text-[#C8102E]' : ($section === $nextSection ? 'text-gray-700' : 'text-gray-400') }}"></i>
                                <p class="text-xs font-medium {{ in_array($section, $completedSectionsList) ? 'text-[#C8102E]' : ($section === $nextSection ? 'text-gray-900' : 'text-gray-500') }}">{{ ucfirst($section) }}</p>
                                <p class="text-xs mt-0.5 {{ in_array($section, $completedSectionsList) ? 'text-[#C8102E]/70' : 'text-gray-400' }}">
                                    @if(in_array($section, $completedSectionsList))
                                        Done
                                    @elseif($section === $nextSection)
                                        Next
                                    @else
                                        Pending
                                    @endif
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 24 Hour Time Limit Warning for Offline Students --}}
                @php
                    $fullTestAttempt = \App\Models\FullTestAttempt::find($fullTestAttemptId);
                    $isOfflineStudent = auth()->user()->isOfflineStudent();
                    $remainingTime = $fullTestAttempt ? $fullTestAttempt->remaining_time_formatted : null;
                @endphp
                @if($isOfflineStudent && $remainingTime && $hasNextSection)
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-clock text-amber-600"></i>
                            <p class="text-sm text-amber-800">
                                <span class="font-semibold">Time Remaining:</span> {{ $remainingTime }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Next Section Info -->
                @if($hasNextSection)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">Up next:</span>
                        @if($nextSection === 'listening')
                            Listening section (30 minutes, 40 questions)
                        @elseif($nextSection === 'reading')
                            Reading section (60 minutes, 40 questions)
                        @elseif($nextSection === 'writing')
                            Writing section (60 minutes, 2 tasks)
                        @elseif($nextSection === 'speaking')
                            Speaking section (11-14 minutes, 3 parts)
                        @endif
                    </p>
                </div>
                @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm text-green-800">
                        <span class="font-semibold">All sections completed!</span> Your overall band score is now available.
                    </p>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 space-y-3">
                @if($hasNextSection)
                    <form action="{{ route('student.full-test.section', ['fullTestAttempt' => $fullTestAttemptId, 'section' => $nextSection]) }}" method="GET">
                        <button type="submit" class="w-full py-3 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                            Continue to {{ ucfirst($nextSection) }}
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>
                    <p class="text-center text-xs text-gray-500">Your progress is automatically saved</p>
                @else
                    <a href="{{ route('student.full-test.results', $fullTestAttemptId) }}" class="w-full py-3 rounded-lg bg-gray-900 text-white text-sm font-medium hover:bg-gray-800 transition-colors flex items-center justify-center gap-2">
                        View Results
                        <i class="fas fa-chart-bar"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

</body>
</html>
