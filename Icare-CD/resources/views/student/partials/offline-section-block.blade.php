{{-- resources/views/student/partials/offline-section-block.blade.php --}}
@php
    $compact = $compact ?? false;
@endphp

<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
    <!-- Section Header -->
    <div class="bg-gradient-to-r {{ $config['gradient'] }} px-5 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <i class="fas {{ $config['icon'] }} text-white text-lg"></i>
            </div>
            <div class="text-white">
                <h3 class="font-bold text-lg">{{ $config['title'] }}</h3>
                <p class="text-white/70 text-xs">{{ $config['subtitle'] }}</p>
            </div>
        </div>
        <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-semibold">
            {{ $tests->count() }} টি টেস্ট
        </span>
    </div>

    <!-- Tests List -->
    <div class="{{ $compact ? '' : 'p-4' }}">
        <div class="{{ $compact ? 'divide-y divide-gray-100' : 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3' }}">
            @foreach($tests as $assignment)
                @php
                    $test = $assignment->fullTest;
                    $isExpired = $assignment->isExpired();
                    $daysRemaining = $assignment->days_remaining;

                    // Check attempt status
                    $existingAttempt = \App\Models\FullTestAttempt::where('user_id', auth()->id())
                        ->where('full_test_id', $test->id)
                        ->whereIn('status', ['in_progress', 'completed'])
                        ->latest()
                        ->first();

                    $hasInProgress = $existingAttempt && $existingAttempt->status === 'in_progress';
                    $hasCompleted = $existingAttempt && $existingAttempt->status === 'completed';
                @endphp

                @if($compact)
                    {{-- Compact List Item --}}
                    <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors {{ $isExpired ? 'opacity-50' : '' }}">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            @if($hasCompleted)
                                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-check text-emerald-600"></i>
                                </div>
                            @elseif($hasInProgress)
                                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-play text-amber-600"></i>
                                </div>
                            @elseif($isExpired)
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-lock text-gray-400"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 {{ $config['bg'] }}/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas {{ $config['icon'] }} {{ str_replace('bg-', 'text-', $config['bg']) }}"></i>
                                </div>
                            @endif

                            <div class="min-w-0">
                                <p class="font-semibold text-gray-900 truncate">{{ $test->title }}</p>
                                <p class="text-xs text-gray-500">
                                    @if($hasCompleted)
                                        <span class="text-emerald-600"><i class="fas fa-check-circle mr-1"></i>সম্পন্ন</span>
                                    @elseif($hasInProgress)
                                        <span class="text-amber-600"><i class="fas fa-spinner mr-1"></i>চলমান</span>
                                    @elseif($isExpired)
                                        <span class="text-red-500"><i class="fas fa-times-circle mr-1"></i>মেয়াদ শেষ</span>
                                    @else
                                        <span class="{{ $daysRemaining <= 3 ? 'text-red-500' : 'text-gray-500' }}">
                                            <i class="far fa-clock mr-1"></i>{{ $daysRemaining }} দিন বাকি
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($hasCompleted && $existingAttempt)
                            <a href="{{ route('student.full-test.results', $existingAttempt) }}"
                               class="px-4 py-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-semibold hover:bg-emerald-200 transition-colors">
                                ফলাফল
                            </a>
                        @elseif($isExpired)
                            <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg text-sm font-medium">
                                মেয়াদ শেষ
                            </span>
                        @elseif($hasInProgress)
                            <a href="{{ route('student.full-test.onboarding', $test) }}"
                               class="px-4 py-2 bg-amber-100 text-amber-700 rounded-lg text-sm font-semibold hover:bg-amber-200 transition-colors">
                                চালিয়ে যান
                            </a>
                        @else
                            <a href="{{ route('student.full-test.onboarding', $test) }}"
                               class="px-4 py-2 bg-gradient-to-r {{ $config['gradient'] }} text-white rounded-lg text-sm font-semibold hover:shadow-md transition-all">
                                শুরু করুন
                            </a>
                        @endif
                    </div>
                @else
                    {{-- Card Item for Full Tests --}}
                    <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 {{ $isExpired ? 'opacity-50' : '' }} {{ $hasCompleted ? 'bg-emerald-50 border-emerald-200' : '' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold text-gray-900 truncate">{{ $test->title }}</h4>
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    @php
                                        $sectionTypes = $test->testSets->pluck('pivot.section_type')->toArray();
                                        $sectionIcons = [
                                            'listening' => ['icon' => 'fa-headphones', 'color' => 'blue'],
                                            'reading' => ['icon' => 'fa-book-open', 'color' => 'emerald'],
                                            'writing' => ['icon' => 'fa-pen-fancy', 'color' => 'violet'],
                                            'speaking' => ['icon' => 'fa-microphone', 'color' => 'orange'],
                                        ];
                                    @endphp
                                    @foreach($sectionTypes as $sType)
                                        @if(isset($sectionIcons[$sType]))
                                            <span class="inline-flex items-center px-2 py-0.5 bg-{{ $sectionIcons[$sType]['color'] }}-100 text-{{ $sectionIcons[$sType]['color'] }}-700 rounded text-[10px] font-medium">
                                                <i class="fas {{ $sectionIcons[$sType]['icon'] }} mr-1 text-[8px]"></i>
                                                {{ ucfirst($sType) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            @if($hasCompleted)
                                <span class="px-2 py-1 bg-emerald-200 text-emerald-800 rounded-lg text-[10px] font-bold">
                                    <i class="fas fa-check mr-1"></i>সম্পন্ন
                                </span>
                            @elseif($hasInProgress)
                                <span class="px-2 py-1 bg-amber-200 text-amber-800 rounded-lg text-[10px] font-bold animate-pulse">
                                    <i class="fas fa-play mr-1"></i>চলমান
                                </span>
                            @elseif($isExpired)
                                <span class="px-2 py-1 bg-red-100 text-red-600 rounded-lg text-[10px] font-bold">
                                    <i class="fas fa-lock mr-1"></i>শেষ
                                </span>
                            @endif
                        </div>

                        @if(!$hasCompleted && !$isExpired)
                            <p class="text-xs {{ $daysRemaining <= 3 ? 'text-red-500 font-medium' : 'text-gray-500' }} mb-3">
                                <i class="far fa-clock mr-1"></i>{{ $daysRemaining }} দিন বাকি
                            </p>
                        @endif

                        @if($hasCompleted && $existingAttempt)
                            <a href="{{ route('student.full-test.results', $existingAttempt) }}"
                               class="block w-full py-2 text-center bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>ফলাফল দেখুন
                            </a>
                        @elseif($isExpired)
                            <button disabled class="block w-full py-2 text-center bg-gray-200 text-gray-400 rounded-lg text-sm font-medium cursor-not-allowed">
                                <i class="fas fa-lock mr-1"></i>মেয়াদ শেষ
                            </button>
                        @elseif($hasInProgress)
                            <a href="{{ route('student.full-test.onboarding', $test) }}"
                               class="block w-full py-2 text-center bg-amber-500 text-white rounded-lg text-sm font-semibold hover:bg-amber-600 transition-colors">
                                <i class="fas fa-play mr-1"></i>চালিয়ে যান
                            </a>
                        @else
                            <a href="{{ route('student.full-test.onboarding', $test) }}"
                               class="block w-full py-2 text-center bg-gradient-to-r {{ $config['gradient'] }} text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-play mr-1"></i>শুরু করুন
                            </a>
                        @endif
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
