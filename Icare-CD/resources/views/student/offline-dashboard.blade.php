{{-- resources/views/student/offline-dashboard.blade.php --}}
{{-- Minimal Crimson Design for Offline Students --}}
<x-dashboard-layout>
    <x-slot:title>My Tests</x-slot>

    @php
        $enrollment = auth()->user()->offlineEnrollment;
        $branch = $enrollment->branch ?? null;

        // Group tests by section type
        $getSectionTypes = function($assignment) {
            if (!$assignment->fullTest) return [];
            return $assignment->fullTest->testSets->pluck('pivot.section_type')->toArray();
        };

        // Categorize
        $fullTests = $assignedTests->filter(fn($t) => $t->fullTest && $t->fullTest->hasAllSections());
        $sectionTests = $assignedTests->filter(fn($t) => $t->fullTest && !$t->fullTest->hasAllSections());

        $listeningTests = $sectionTests->filter(fn($t) => count($getSectionTypes($t)) === 1 && in_array('listening', $getSectionTypes($t)));
        $readingTests = $sectionTests->filter(fn($t) => count($getSectionTypes($t)) === 1 && in_array('reading', $getSectionTypes($t)));
        $writingTests = $sectionTests->filter(fn($t) => count($getSectionTypes($t)) === 1 && in_array('writing', $getSectionTypes($t)));
        $speakingTests = $sectionTests->filter(fn($t) => count($getSectionTypes($t)) === 1 && in_array('speaking', $getSectionTypes($t)));
    @endphp

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 py-8">

            {{-- Welcome Card --}}
            <div class="bg-[#C8102E] rounded-2xl p-6 mb-8 text-white">
                <p class="text-white/70 text-sm">স্বাগতম</p>
                <h1 class="text-2xl font-bold">{{ auth()->user()->name }}</h1>
                @if($branch)
                    <p class="text-white/80 text-sm mt-1"><i class="fas fa-map-marker-alt mr-1"></i>{{ $branch->name }}</p>
                @endif

                <div class="flex gap-6 mt-6">
                    <div>
                        <p class="text-3xl font-black">{{ $enrollment->remaining_full_tests }}</p>
                        <p class="text-white/70 text-xs">বাকি টেস্ট</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black">{{ $enrollment->days_remaining }}</p>
                        <p class="text-white/70 text-xs">দিন বাকি</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black">{{ $completedAttempts->count() }}</p>
                        <p class="text-white/70 text-xs">সম্পন্ন</p>
                    </div>
                </div>
            </div>

            @if($assignedTests->isEmpty())
                {{-- Empty State --}}
                <div class="bg-white rounded-2xl p-12 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-clipboard-list text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-gray-500">কোনো টেস্ট এসাইন করা হয়নি</p>
                </div>
            @else

                {{-- Full Mock Tests --}}
                @if($fullTests->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-[#C8102E] rounded-lg flex items-center justify-center">
                            <i class="fas fa-layer-group text-white text-sm"></i>
                        </span>
                        Full Mock Test
                        <span class="text-sm font-normal text-gray-500">({{ $fullTests->count() }})</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($fullTests as $assignment)
                            @include('student.partials.offline-test-item', ['assignment' => $assignment, 'color' => 'red'])
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Listening --}}
                @if($listeningTests->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-headphones text-white text-sm"></i>
                        </span>
                        Listening
                        <span class="text-sm font-normal text-gray-500">({{ $listeningTests->count() }})</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($listeningTests as $assignment)
                            @include('student.partials.offline-test-item', ['assignment' => $assignment, 'color' => 'blue'])
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Reading --}}
                @if($readingTests->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book-open text-white text-sm"></i>
                        </span>
                        Reading
                        <span class="text-sm font-normal text-gray-500">({{ $readingTests->count() }})</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($readingTests as $assignment)
                            @include('student.partials.offline-test-item', ['assignment' => $assignment, 'color' => 'emerald'])
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Writing --}}
                @if($writingTests->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-violet-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-pen-fancy text-white text-sm"></i>
                        </span>
                        Writing
                        <span class="text-sm font-normal text-gray-500">({{ $writingTests->count() }})</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($writingTests as $assignment)
                            @include('student.partials.offline-test-item', ['assignment' => $assignment, 'color' => 'violet'])
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Speaking --}}
                @if($speakingTests->isNotEmpty())
                <div class="mb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-microphone text-white text-sm"></i>
                        </span>
                        Speaking
                        <span class="text-sm font-normal text-gray-500">({{ $speakingTests->count() }})</span>
                    </h2>
                    <div class="space-y-3">
                        @foreach($speakingTests as $assignment)
                            @include('student.partials.offline-test-item', ['assignment' => $assignment, 'color' => 'orange'])
                        @endforeach
                    </div>
                </div>
                @endif

            @endif

            {{-- Completed Tests --}}
            @if($completedAttempts->isNotEmpty())
            <div class="mt-10">
                <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <span class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check text-white text-sm"></i>
                    </span>
                    সম্পন্ন টেস্ট
                </h2>
                <div class="bg-white rounded-2xl divide-y divide-gray-100">
                    @foreach($completedAttempts->take(10) as $attempt)
                        <a href="{{ route('student.full-test.results', $attempt) }}" class="flex items-center justify-between p-4 hover:bg-gray-50">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $attempt->fullTest->title }}</p>
                                <p class="text-sm text-gray-500">{{ $attempt->end_time ? $attempt->end_time->format('d M, Y') : '' }}</p>
                            </div>
                            @if($attempt->overall_band_score)
                                <span class="text-2xl font-black text-[#C8102E]">{{ number_format($attempt->overall_band_score, 1) }}</span>
                            @else
                                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-sm">পেন্ডিং</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-dashboard-layout>
