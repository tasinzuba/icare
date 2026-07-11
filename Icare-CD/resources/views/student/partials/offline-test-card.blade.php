{{-- resources/views/student/partials/offline-test-card.blade.php --}}
@php
    $test = $assignment->fullTest;
    $isCompleted = $assignment->status === 'completed';
    $isExpired = $assignment->isExpired();
    $daysRemaining = $assignment->days_remaining;

    // Check if test has an in-progress attempt
    $existingAttempt = \App\Models\FullTestAttempt::where('user_id', auth()->id())
        ->where('full_test_id', $test->id)
        ->whereIn('status', ['in_progress', 'completed'])
        ->latest()
        ->first();

    $hasInProgress = $existingAttempt && $existingAttempt->status === 'in_progress';
    $hasCompleted = $existingAttempt && $existingAttempt->status === 'completed';

    // Colors based on type
    $colors = match($type) {
        'full' => ['bg' => 'from-[#C8102E] to-[#A00E27]', 'light' => 'bg-[#C8102E]/10', 'text' => 'text-[#C8102E]'],
        'listening' => ['bg' => 'from-blue-500 to-blue-600', 'light' => 'bg-blue-100', 'text' => 'text-blue-600'],
        'reading' => ['bg' => 'from-emerald-500 to-emerald-600', 'light' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
        'writing' => ['bg' => 'from-violet-500 to-violet-600', 'light' => 'bg-violet-100', 'text' => 'text-violet-600'],
        'speaking' => ['bg' => 'from-orange-500 to-orange-600', 'light' => 'bg-orange-100', 'text' => 'text-orange-600'],
        default => ['bg' => 'from-gray-500 to-gray-600', 'light' => 'bg-gray-100', 'text' => 'text-gray-600'],
    };

    // Get sections info from pivot table
    $sections = [];
    $sectionTypes = $test->testSets->pluck('pivot.section_type')->toArray();
    if (in_array('listening', $sectionTypes)) $sections[] = ['name' => 'Listening', 'icon' => 'fa-headphones', 'color' => 'blue'];
    if (in_array('reading', $sectionTypes)) $sections[] = ['name' => 'Reading', 'icon' => 'fa-book-open', 'color' => 'emerald'];
    if (in_array('writing', $sectionTypes)) $sections[] = ['name' => 'Writing', 'icon' => 'fa-pen-fancy', 'color' => 'violet'];
    if (in_array('speaking', $sectionTypes)) $sections[] = ['name' => 'Speaking', 'icon' => 'fa-microphone', 'color' => 'orange'];
@endphp

<div class="bg-white rounded-2xl border {{ $isExpired ? 'border-gray-200 opacity-60' : ($hasCompleted ? 'border-emerald-200' : 'border-gray-200') }} overflow-hidden hover:shadow-lg transition-all">
    <!-- Card Header -->
    <div class="p-4 {{ $hasCompleted ? 'bg-emerald-50' : ($isExpired ? 'bg-gray-50' : 'bg-gradient-to-r ' . $colors['bg']) }} {{ $hasCompleted || $isExpired ? '' : 'text-white' }}">
        <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
                <h3 class="font-bold {{ $hasCompleted ? 'text-emerald-800' : ($isExpired ? 'text-gray-600' : 'text-white') }} truncate">
                    {{ $test->title }}
                </h3>
                <div class="flex items-center gap-2 mt-1">
                    @foreach($sections as $section)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium {{ $hasCompleted ? 'bg-emerald-100 text-emerald-700' : ($isExpired ? 'bg-gray-200 text-gray-600' : 'bg-white/20 text-white') }}">
                            <i class="fas {{ $section['icon'] }} mr-1 text-[8px]"></i>
                            {{ $section['name'] }}
                        </span>
                    @endforeach
                </div>
            </div>

            <!-- Status Badge -->
            @if($hasCompleted)
                <span class="flex-shrink-0 px-2 py-1 bg-emerald-200 text-emerald-800 rounded-lg text-xs font-semibold">
                    <i class="fas fa-check mr-1"></i>সম্পন্ন
                </span>
            @elseif($isExpired)
                <span class="flex-shrink-0 px-2 py-1 bg-red-100 text-red-600 rounded-lg text-xs font-semibold">
                    <i class="fas fa-clock mr-1"></i>মেয়াদ শেষ
                </span>
            @elseif($hasInProgress)
                <span class="flex-shrink-0 px-2 py-1 bg-amber-400 text-amber-900 rounded-lg text-xs font-semibold">
                    <i class="fas fa-spinner fa-spin mr-1"></i>চলমান
                </span>
            @endif
        </div>
    </div>

    <!-- Card Body -->
    <div class="p-4">
        <!-- Validity Info -->
        @if(!$hasCompleted && !$isExpired)
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                <i class="fas fa-calendar-alt"></i>
                <span>
                    @if($daysRemaining > 0)
                        <span class="{{ $daysRemaining <= 3 ? 'text-red-500 font-medium' : '' }}">
                            {{ $daysRemaining }} দিন বাকি
                        </span>
                    @else
                        আজকেই শেষ!
                    @endif
                </span>
            </div>
        @endif

        <!-- Action Button -->
        @if($hasCompleted && $existingAttempt)
            <a href="{{ route('student.full-test.results', $existingAttempt) }}"
               class="block w-full py-2.5 text-center bg-emerald-100 text-emerald-700 rounded-xl font-semibold hover:bg-emerald-200 transition-colors">
                <i class="fas fa-eye mr-2"></i>ফলাফল দেখুন
            </a>
        @elseif($isExpired)
            <button disabled
                    class="block w-full py-2.5 text-center bg-gray-100 text-gray-400 rounded-xl font-medium cursor-not-allowed">
                <i class="fas fa-lock mr-2"></i>মেয়াদ শেষ
            </button>
        @elseif($hasInProgress && $existingAttempt)
            <a href="{{ route('student.full-test.onboarding', $test) }}"
               class="block w-full py-2.5 text-center bg-gradient-to-r {{ $colors['bg'] }} text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                <i class="fas fa-play mr-2"></i>চালিয়ে যান
            </a>
        @else
            <a href="{{ route('student.full-test.onboarding', $test) }}"
               class="block w-full py-2.5 text-center bg-gradient-to-r {{ $colors['bg'] }} text-white rounded-xl font-semibold hover:shadow-lg transition-all">
                <i class="fas fa-play mr-2"></i>শুরু করুন
            </a>
        @endif
    </div>
</div>
