<x-dashboard-layout>
    <x-slot:title>Request Full Test Evaluation</x-slot>

    @php
        $user = auth()->user();
        $isOfflineStudent = $user->isOfflineStudent();
        $canUseAI = $user->canUseAIEvaluation();
        $canUseHuman = $user->canUseHumanEvaluation();
        $enrollment = $isOfflineStudent ? $user->offlineEnrollment : null;
        $evalType = $enrollment->evaluation_type ?? 'ai';
        $requestedType = request('type', ($canUseAI ? 'ai' : 'human'));
    @endphp

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Request Evaluation</h1>
            <p class="text-gray-500 mt-1">{{ $fullTestAttempt->fullTest->title }}</p>
        </div>

        {{-- Offline Student Evaluation Type Info --}}
        @if($isOfflineStudent)
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-2xl p-5 mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        @if($evalType === 'ai')
                            <i class="fas fa-robot text-indigo-600 text-xl"></i>
                        @elseif($evalType === 'human')
                            <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                        @else
                            <i class="fas fa-balance-scale text-emerald-600 text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Your Evaluation Package</p>
                        <p class="text-lg font-bold {{ $evalType === 'ai' ? 'text-indigo-700' : ($evalType === 'human' ? 'text-purple-700' : 'text-emerald-700') }}">
                            @if($evalType === 'ai')
                                AI Evaluation Only
                            @elseif($evalType === 'human')
                                Human Evaluation Only
                            @else
                                AI + Human Evaluation
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <i class="fas fa-info-circle mr-1"></i>No tokens required for offline students
                        </p>
                    </div>
                </div>
            </div>

            {{-- Evaluation Type Selection for Both --}}
            @if($evalType === 'both')
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                        <h2 class="font-semibold text-gray-900">Choose Evaluation Type</h2>
                        <p class="text-sm text-gray-500 mt-1">Select how you want your test to be evaluated</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- AI Evaluation Option --}}
                            <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}?type=ai"
                               class="block border-2 rounded-xl p-5 transition-all {{ $requestedType === 'ai' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-blue-300' }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-robot text-white text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">AI Evaluation</h3>
                                        <p class="text-sm text-gray-500">Instant feedback with AI</p>
                                        <span class="inline-block mt-2 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs font-medium rounded-full">
                                            <i class="fas fa-bolt mr-1"></i>Instant
                                        </span>
                                    </div>
                                </div>
                            </a>

                            {{-- Human Evaluation Option --}}
                            <a href="{{ route('student.full-test.request-evaluation', $fullTestAttempt) }}?type=human"
                               class="block border-2 rounded-xl p-5 transition-all {{ $requestedType === 'human' ? 'border-purple-500 bg-purple-50' : 'border-gray-200 hover:border-purple-300' }}">
                                <div class="flex items-center gap-4">
                                    <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-user-tie text-white text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900">Human Evaluation</h3>
                                        <p class="text-sm text-gray-500">Expert teacher feedback</p>
                                        <span class="inline-block mt-2 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-medium rounded-full">
                                            <i class="fas fa-clock mr-1"></i>24-48 hours
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- AI Evaluation Section --}}
            @if($canUseAI && ($evalType === 'ai' || ($evalType === 'both' && $requestedType === 'ai')))
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-robot text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-900">AI Evaluation</h2>
                                <p class="text-sm text-gray-500">Get instant AI-powered feedback</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($sectionsNeedingEvaluation as $section)
                                @php
                                    $sectionIcon = $section['type'] === 'writing' ? 'fa-pen-fancy' : 'fa-microphone';
                                    $sectionColor = $section['type'] === 'writing' ? 'violet' : 'orange';
                                    $hasAIEvaluation = $section['student_attempt']->ai_evaluated_at !== null;
                                @endphp
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-{{ $sectionColor }}-100 rounded-lg flex items-center justify-center">
                                            <i class="fas {{ $sectionIcon }} text-{{ $sectionColor }}-600"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-gray-900 capitalize">{{ $section['type'] }}</p>
                                            <p class="text-xs text-gray-500">
                                                @if($hasAIEvaluation)
                                                    <span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Already Evaluated</span>
                                                @else
                                                    Ready for evaluation
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @if($hasAIEvaluation)
                                        <a href="{{ route('ai.evaluation.get', $section['student_attempt']->id) }}"
                                           class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium hover:bg-green-200 transition">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                    @else
                                        <button type="button"
                                                onclick="startSectionAIEvaluation({{ $section['student_attempt']->id }}, '{{ $section['type'] }}', this)"
                                                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition ai-eval-btn">
                                            <i class="fas fa-robot mr-1"></i>Evaluate
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <p class="text-sm text-blue-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                AI evaluation provides instant feedback on your writing and speaking. Results are typically ready within seconds.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Back Button for AI Only -->
                @if($evalType === 'ai')
                    <div class="flex items-center justify-between">
                        <a href="{{ route('student.full-test.results', $fullTestAttempt) }}" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Results
                        </a>
                    </div>
                @endif
            @endif

            {{-- Human Evaluation Section for Offline Students --}}
            @if($canUseHuman && ($evalType === 'human' || ($evalType === 'both' && $requestedType === 'human')))
                @if($teachers->count() > 0)
                    <form action="{{ route('student.full-test.submit-evaluation', $fullTestAttempt) }}" method="POST">
                        @csrf
                        <input type="hidden" name="is_offline_student" value="1">

                        <!-- Select Sections -->
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-purple-50 to-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-user-tie text-purple-600"></i>
                                    </div>
                                    <div>
                                        <h2 class="font-semibold text-gray-900">Human Evaluation</h2>
                                        <p class="text-sm text-gray-500">Select sections for expert teacher evaluation</p>
                                    </div>
                                </div>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($sectionsNeedingEvaluation as $index => $section)
                                        @php
                                            $hasHumanRequest = $section['student_attempt']->humanEvaluationRequest;
                                        @endphp
                                        @if(!$hasHumanRequest)
                                            <label class="cursor-pointer block">
                                                <input type="checkbox"
                                                       name="sections[]"
                                                       value="{{ $section['student_attempt']->id }}"
                                                       class="peer hidden"
                                                       checked>
                                                <div class="border-2 border-gray-200 rounded-xl p-4 transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex items-center">
                                                            <div class="w-12 h-12 rounded-xl bg-gray-100 peer-checked:bg-purple-100 flex items-center justify-center mr-4">
                                                                <i class="fas {{ $section['type'] === 'writing' ? 'fa-pen-fancy' : 'fa-microphone' }} text-gray-500 peer-checked:text-purple-600 text-lg"></i>
                                                            </div>
                                                            <div>
                                                                <p class="font-semibold text-gray-900 capitalize">{{ $section['type'] }}</p>
                                                                <p class="text-sm text-green-600"><i class="fas fa-check-circle mr-1"></i>Free for you</p>
                                                            </div>
                                                        </div>
                                                        <div class="w-6 h-6 rounded-full border-2 border-gray-300 peer-checked:bg-purple-500 peer-checked:border-purple-500 flex items-center justify-center">
                                                            <i class="fas fa-check text-white text-xs"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        @else
                                            <div class="border-2 border-green-200 rounded-xl p-4 bg-green-50">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                                                            <i class="fas {{ $section['type'] === 'writing' ? 'fa-pen-fancy' : 'fa-microphone' }} text-green-600 text-lg"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-semibold text-gray-900 capitalize">{{ $section['type'] }}</p>
                                                            <p class="text-sm text-green-600">
                                                                <i class="fas fa-check-circle mr-1"></i>
                                                                {{ $hasHumanRequest->status === 'completed' ? 'Evaluated' : 'Request Pending' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Teacher Selection -->
                        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                                <h2 class="font-semibold text-gray-900">Select Teacher</h2>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    @foreach($teachers as $teacher)
                                        <label class="cursor-pointer block">
                                            <input type="radio" name="teacher_id" value="{{ $teacher->id }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                                            <div class="border-2 border-gray-200 rounded-xl p-4 h-full transition-all peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-gray-300">
                                                <div class="flex items-start mb-3">
                                                    <img src="{{ $teacher->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacher->user->name) . '&background=8B5CF6&color=fff' }}"
                                                         alt="{{ $teacher->user->name }}"
                                                         class="w-12 h-12 rounded-full mr-3 border-2 border-gray-100">
                                                    <div class="flex-1">
                                                        <h3 class="font-semibold text-gray-900">{{ $teacher->user->name }}</h3>
                                                        @if($teacher->specialization)
                                                            <div class="flex flex-wrap gap-1 mt-1">
                                                                @foreach($teacher->specialization as $spec)
                                                                    <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-600 capitalize">{{ $spec }}</span>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                @if($teacher->bio)
                                                    <p class="text-xs text-gray-500 line-clamp-2">{{ $teacher->bio }}</p>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-between">
                            <a href="{{ route('student.full-test.results', $fullTestAttempt) }}" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-arrow-left mr-1"></i> Back to Results
                            </a>
                            <button type="submit"
                                    class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-xl transition-colors">
                                <i class="fas fa-paper-plane mr-2"></i>Request Human Evaluation
                            </button>
                        </div>
                    </form>
                @else
                    <div class="bg-white border border-amber-200 rounded-2xl p-8 text-center shadow-sm">
                        <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-slash text-amber-600 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">No Teachers Available</h3>
                        <p class="text-gray-500 mb-6">There are currently no teachers available for evaluation. Please try again later.</p>
                        <a href="{{ route('student.full-test.results', $fullTestAttempt) }}"
                           class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Results
                        </a>
                    </div>
                @endif
            @endif

        {{-- Regular Student (Token-based) --}}
        @else
            <!-- Token Balance Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-5 mb-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center mr-4">
                            <i class="fas fa-coins text-amber-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Your Token Balance</p>
                            <p class="text-2xl font-bold text-gray-900">{{ number_format($tokenBalance->available_tokens) }} <span class="text-base font-normal text-gray-500">Tokens</span></p>
                        </div>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
                        <div>
                            <p class="font-semibold text-red-800">Please fix the following errors:</p>
                            <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                    <p class="text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            @if($teachers->count() > 0)
                <form action="{{ route('student.full-test.submit-evaluation', $fullTestAttempt) }}" method="POST">
                    @csrf

                    <!-- Select Sections -->
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="font-semibold text-gray-900">Select Sections to Evaluate</h2>
                            <p class="text-sm text-gray-500 mt-1">Choose which sections you want to get evaluated</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($sectionsNeedingEvaluation as $index => $section)
                                    <label class="cursor-pointer block">
                                        <input type="checkbox"
                                               name="sections[]"
                                               value="{{ $section['student_attempt']->id }}"
                                               class="peer hidden">
                                        <div class="border-2 border-gray-200 rounded-xl p-4 transition-all peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]/5 hover:border-gray-300">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="w-12 h-12 rounded-xl bg-gray-100 peer-checked:bg-[#C8102E]/10 flex items-center justify-center mr-4">
                                                        <i class="fas {{ $section['type'] === 'writing' ? 'fa-pen-fancy' : 'fa-microphone' }} text-gray-500 peer-checked:text-[#C8102E] text-lg"></i>
                                                    </div>
                                                    <div>
                                                        <p class="font-semibold text-gray-900 capitalize">{{ $section['type'] }}</p>
                                                        <p class="text-sm text-gray-500">10 tokens</p>
                                                    </div>
                                                </div>
                                                <div class="w-6 h-6 rounded-full border-2 border-gray-300 peer-checked:bg-[#C8102E] peer-checked:border-[#C8102E] flex items-center justify-center">
                                                    <i class="fas fa-check text-white text-xs hidden peer-checked:block"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Total Cost -->
                            <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                                <p class="text-sm text-amber-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    10 tokens per section (15 tokens for urgent priority)
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Priority Selection -->
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="font-semibold text-gray-900">Select Priority</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="cursor-pointer block">
                                    <input type="radio" name="priority" value="normal" class="peer hidden" checked>
                                    <div class="border-2 border-gray-200 rounded-xl p-4 transition-all peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]/5 hover:border-gray-300">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-100 peer-checked:bg-[#C8102E]/10 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-clock text-gray-500 peer-checked:text-[#C8102E]"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900">Normal</h3>
                                                    <p class="text-sm text-gray-500">48 hours turnaround</p>
                                                </div>
                                            </div>
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]"></div>
                                        </div>
                                    </div>
                                </label>
                                <label class="cursor-pointer block">
                                    <input type="radio" name="priority" value="urgent" class="peer hidden">
                                    <div class="border-2 border-gray-200 rounded-xl p-4 transition-all peer-checked:border-amber-500 peer-checked:bg-amber-50 hover:border-gray-300">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <div class="w-10 h-10 bg-gray-100 peer-checked:bg-amber-100 rounded-lg flex items-center justify-center mr-3">
                                                    <i class="fas fa-bolt text-gray-500 peer-checked:text-amber-600"></i>
                                                </div>
                                                <div>
                                                    <h3 class="font-semibold text-gray-900">Urgent</h3>
                                                    <p class="text-sm text-gray-500">12 hours (1.5x cost)</p>
                                                </div>
                                            </div>
                                            <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-amber-500 peer-checked:bg-amber-500"></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Selection -->
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 shadow-sm">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                            <h2 class="font-semibold text-gray-900">Select Teacher</h2>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($teachers as $teacher)
                                    <label class="cursor-pointer block">
                                        <input type="radio" name="teacher_id" value="{{ $teacher->id }}" class="peer hidden" {{ $loop->first ? 'checked' : '' }}>
                                        <div class="border-2 border-gray-200 rounded-xl p-4 h-full transition-all peer-checked:border-[#C8102E] peer-checked:bg-[#C8102E]/5 hover:border-gray-300">
                                            <div class="flex items-start mb-3">
                                                <img src="{{ $teacher->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($teacher->user->name) . '&background=C8102E&color=fff' }}"
                                                     alt="{{ $teacher->user->name }}"
                                                     class="w-12 h-12 rounded-full mr-3 border-2 border-gray-100">
                                                <div class="flex-1">
                                                    <h3 class="font-semibold text-gray-900">{{ $teacher->user->name }}</h3>
                                                    @if($teacher->specialization)
                                                        <div class="flex flex-wrap gap-1 mt-1">
                                                            @foreach($teacher->specialization as $spec)
                                                                <span class="text-xs px-2 py-0.5 bg-gray-100 rounded text-gray-600 capitalize">{{ $spec }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($teacher->bio)
                                                <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $teacher->bio }}</p>
                                            @endif
                                            <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                                <span class="text-xs text-gray-500">Cost per section</span>
                                                <span class="font-semibold text-[#C8102E]">10 tokens</span>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between">
                        <a href="{{ route('student.full-test.results', $fullTestAttempt) }}" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-arrow-left mr-1"></i> Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-3 bg-gray-900 hover:bg-gray-800 text-white font-medium rounded-xl transition-colors">
                            <i class="fas fa-paper-plane mr-2"></i>Request Evaluation
                        </button>
                    </div>
                </form>
            @else
                <!-- No Teachers Available -->
                <div class="bg-white border border-amber-200 rounded-2xl p-8 text-center shadow-sm">
                    <div class="w-16 h-16 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-slash text-amber-600 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No Teachers Available</h3>
                    <p class="text-gray-500 mb-6">There are currently no teachers available for evaluation. Please try again later.</p>
                    <a href="{{ route('student.full-test.results', $fullTestAttempt) }}"
                       class="inline-flex items-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Results
                    </a>
                </div>
            @endif
        @endif
    </div>

    {{-- AI Evaluation Modal --}}
    @if($isOfflineStudent && $canUseAI)
    <div id="aiEvalModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
            <div class="text-center">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-robot text-white text-3xl animate-pulse"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">AI Evaluation in Progress</h3>
                <p class="text-gray-500 text-sm mb-4" id="eval-status">Initializing AI evaluation...</p>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                    <div id="eval-progress" class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                </div>
                <p class="text-xs text-gray-400" id="eval-tip">Please wait while we analyze your response</p>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        async function startSectionAIEvaluation(attemptId, sectionType, button) {
            const modal = document.getElementById('aiEvalModal');
            const statusEl = document.getElementById('eval-status');
            const progressEl = document.getElementById('eval-progress');
            const tipEl = document.getElementById('eval-tip');

            // Show modal and disable button
            if (modal) modal.classList.remove('hidden');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                if (statusEl) statusEl.textContent = `Evaluating ${sectionType} section...`;
                if (tipEl) tipEl.textContent = 'This may take a moment';
                if (progressEl) progressEl.style.width = '30%';

                if (sectionType === 'speaking') {
                    await evaluateSpeakingProgressive(attemptId, csrfToken, statusEl, tipEl, progressEl);
                } else {
                    // Writing evaluation
                    const response = await fetch('/ai/evaluate/writing', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ attempt_id: attemptId })
                    });

                    const data = await response.json();

                    if (!data.success && !data.already_evaluated) {
                        throw new Error(data.error || 'Evaluation failed');
                    }

                    if (progressEl) progressEl.style.width = '100%';

                    // Redirect to AI evaluation result
                    if (data.redirect_url) {
                        if (statusEl) statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-2"></i>Evaluation Complete!';
                        if (tipEl) tipEl.textContent = 'Redirecting to results...';
                        setTimeout(() => {
                            window.location.href = data.redirect_url;
                        }, 1000);
                        return;
                    }
                }

                // Success - reload page
                if (statusEl) statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-2"></i>Evaluation Complete!';
                if (tipEl) tipEl.textContent = 'Refreshing page...';
                if (progressEl) progressEl.style.width = '100%';

                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } catch (error) {
                console.error('AI Evaluation error:', error);
                if (statusEl) statusEl.innerHTML = `<span class="text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>${error.message || 'Evaluation failed'}</span>`;
                if (tipEl) tipEl.textContent = 'Please try again';

                setTimeout(() => {
                    if (modal) modal.classList.add('hidden');
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-robot mr-1"></i>Evaluate';
                }, 3000);
            }
        }

        async function evaluateSpeakingProgressive(attemptId, csrfToken, statusEl, tipEl, progressEl) {
            // Step 1: Get status
            if (statusEl) statusEl.textContent = 'Checking speaking recordings...';

            const statusRes = await fetch('/ai/evaluate/speaking/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ attempt_id: attemptId })
            });
            const statusData = await statusRes.json();

            if (statusData.status === 'completed') {
                if (statusData.redirect_url) {
                    window.location.href = statusData.redirect_url;
                }
                return;
            }

            const recordings = statusData.recordings || [];
            const pendingRecordings = recordings.filter(r => !r.evaluated);

            // Step 2: Evaluate each recording
            for (let i = 0; i < pendingRecordings.length; i++) {
                const recording = pendingRecordings[i];
                const progress = 30 + ((i + 1) / pendingRecordings.length) * 50;

                if (statusEl) statusEl.textContent = `Evaluating recording ${i + 1} of ${pendingRecordings.length}...`;
                if (tipEl) tipEl.textContent = `Part ${recording.part_number}, Question ${recording.question_order}`;
                if (progressEl) progressEl.style.width = `${progress}%`;

                const evalRes = await fetch('/ai/evaluate/speaking/single', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        attempt_id: attemptId,
                        answer_id: recording.answer_id
                    })
                });

                const evalData = await evalRes.json();
                if (!evalData.success) {
                    throw new Error(evalData.error || 'Failed to evaluate recording');
                }
            }

            // Step 3: Finalize
            if (statusEl) statusEl.textContent = 'Calculating final score...';
            if (progressEl) progressEl.style.width = '90%';

            const finalRes = await fetch('/ai/evaluate/speaking/finalize', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ attempt_id: attemptId })
            });

            const finalData = await finalRes.json();
            if (!finalData.success) {
                throw new Error(finalData.error || 'Failed to finalize evaluation');
            }

            if (finalData.redirect_url) {
                if (statusEl) statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-500 mr-2"></i>Complete!';
                if (tipEl) tipEl.textContent = 'Redirecting...';
                if (progressEl) progressEl.style.width = '100%';
                setTimeout(() => {
                    window.location.href = finalData.redirect_url;
                }, 1000);
            }
        }
    </script>
    @endpush
</x-dashboard-layout>
