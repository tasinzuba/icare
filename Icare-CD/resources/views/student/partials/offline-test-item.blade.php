{{-- resources/views/student/partials/offline-test-item.blade.php --}}
{{-- Minimal test item for offline dashboard --}}
@php
    $test = $assignment->fullTest;
    $isExpired = $assignment->isExpired();
    $daysRemaining = $assignment->days_remaining;

    $existingAttempt = \App\Models\FullTestAttempt::where('user_id', auth()->id())
        ->where('full_test_id', $test->id)
        ->whereIn('status', ['in_progress', 'completed'])
        ->latest()
        ->first();

    $hasInProgress = $existingAttempt && $existingAttempt->status === 'in_progress';
    $hasCompleted = $existingAttempt && $existingAttempt->status === 'completed';

    $colorClasses = [
        'red' => 'bg-[#C8102E]',
        'blue' => 'bg-blue-500',
        'emerald' => 'bg-emerald-500',
        'violet' => 'bg-violet-500',
        'orange' => 'bg-orange-500',
    ];
    $bgColor = $colorClasses[$color] ?? 'bg-gray-500';
@endphp

<div class="bg-white rounded-xl p-4 flex items-center justify-between {{ $isExpired ? 'opacity-50' : '' }}">
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-gray-900 truncate">{{ $test->title }}</p>
        <p class="text-sm text-gray-500">
            @if($hasCompleted)
                <span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i>সম্পন্ন</span>
            @elseif($hasInProgress)
                <span class="text-amber-600"><i class="fas fa-spinner mr-1"></i>চলমান</span>
            @elseif($isExpired)
                <span class="text-red-500"><i class="fas fa-times-circle mr-1"></i>মেয়াদ শেষ</span>
            @else
                <span class="{{ $daysRemaining <= 3 ? 'text-red-500' : '' }}">
                    <i class="far fa-clock mr-1"></i>{{ $daysRemaining }} দিন বাকি
                </span>
            @endif
        </p>
    </div>

    @if($hasCompleted && $existingAttempt)
        <a href="{{ route('student.full-test.results', $existingAttempt) }}"
           class="px-4 py-2 bg-emerald-500 text-white rounded-lg text-sm font-semibold">
            ফলাফল
        </a>
    @elseif($isExpired)
        <span class="px-4 py-2 bg-gray-200 text-gray-400 rounded-lg text-sm">
            মেয়াদ শেষ
        </span>
    @elseif($hasInProgress)
        <a href="{{ route('student.full-test.onboarding', $test) }}"
           class="px-4 py-2 {{ $bgColor }} text-white rounded-lg text-sm font-semibold">
            চালিয়ে যান
        </a>
    @else
        <a href="{{ route('student.full-test.onboarding', $test) }}"
           class="px-4 py-2 {{ $bgColor }} text-white rounded-lg text-sm font-semibold">
            শুরু করুন
        </a>
    @endif
</div>
