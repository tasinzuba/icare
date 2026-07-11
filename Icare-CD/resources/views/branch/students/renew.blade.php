@extends('layouts.branch')

@section('title', 'Renew Package - ' . $enrollment->student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.students.show', $enrollment) }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Student
    </a>
</div>

<div class="max-w-4xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Renew Package</h1>
    <p class="text-gray-600 mb-6">Renew enrollment for <strong>{{ $enrollment->student->name }}</strong> ({{ $enrollment->student_id }})</p>

    {{-- Current Status Card --}}
    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-amber-800 mb-3 flex items-center">
            <i class="fas fa-info-circle mr-2"></i> Current Enrollment Status
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
            <div>
                <p class="text-gray-600">Status</p>
                <p class="font-semibold text-{{ $enrollment->status_color }}-600">{{ ucfirst($enrollment->status) }}</p>
            </div>
            <div>
                <p class="text-gray-600">Full Tests</p>
                <p class="font-semibold">{{ $enrollment->full_tests_taken }} / {{ $enrollment->full_tests_allowed }}</p>
            </div>
            <div>
                <p class="text-gray-600">Section Tests</p>
                @if($enrollment->hasPerSectionLimits())
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach(['listening' => 'L', 'reading' => 'R', 'writing' => 'W', 'speaking' => 'S'] as $secType => $secLabel)
                            @php $secLimit = $enrollment->getSectionTestLimit($secType); @endphp
                            @if($secLimit > 0)
                                <span class="px-1.5 py-0.5 bg-white text-amber-800 rounded text-xs font-medium" title="{{ ucfirst($secType) }}">
                                    {{ $secLabel }}: {{ $enrollment->getSectionTestsTaken($secType) }}/{{ $secLimit }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="font-semibold">{{ $enrollment->section_tests_taken }} / {{ $enrollment->section_tests_allowed }}</p>
                @endif
            </div>
            <div>
                <p class="text-gray-600">Valid Until</p>
                <p class="font-semibold {{ $enrollment->isExpired() ? 'text-red-600' : '' }}">
                    {{ $enrollment->valid_until?->format('M d, Y') ?? 'N/A' }}
                </p>
            </div>
            <div>
                <p class="text-gray-600">Renewal Count</p>
                <p class="font-semibold">{{ $enrollment->renewal_count ?? 0 }} times</p>
            </div>
        </div>
    </div>

    {{-- Previously Completed Tests Warning --}}
    @if(count($allCompletedIds) > 0)
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
        <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
            <i class="fas fa-history mr-2"></i> Previously Completed Tests ({{ count($allCompletedIds) }})
        </h3>
        <p class="text-sm text-blue-700 mb-3">
            These tests have already been completed and <strong>cannot be retaken</strong>:
        </p>
        <div class="flex flex-wrap gap-2">
            @foreach($fullTests->whereIn('id', $allCompletedIds) as $completedTest)
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                    <i class="fas fa-check-circle mr-1"></i> {{ $completedTest->title }}
                </span>
            @endforeach
        </div>
    </div>
    @endif

    <form action="{{ route('branch.students.renew', $enrollment) }}" method="POST" class="bg-white rounded-xl shadow-md p-6" id="renewForm">
        @csrf

        {{-- Hidden renewal mode field (will be set by JS) --}}
        <input type="hidden" name="renewal_mode" id="renewal_mode" value="add_new">

        {{-- Renewal Mode Selection --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-cog text-indigo-500 mr-2"></i> Renewal Mode
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer">
                    <input type="radio" name="renewal_mode_select" value="add_new" class="hidden peer" checked>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-plus-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Add New Tests</p>
                                <p class="text-xs text-gray-500">Keep old tests, add new ones with new validity</p>
                                <p class="text-xs text-green-600 mt-1">
                                    <i class="fas fa-info-circle mr-1"></i> Previous tests validity unchanged
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="renewal_mode_select" value="full_reset" class="hidden peer">
                    <div class="p-4 border-2 rounded-xl peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sync-alt text-orange-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Full Reset</p>
                                <p class="text-xs text-gray-500">Reset counters, completed tests excluded</p>
                                <p class="text-xs text-orange-600 mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Counters reset to 0
                                </p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>
        </div>

        {{-- Package Type Selection --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-box text-indigo-500 mr-2"></i> Package Configuration
            </h2>

            {{-- Package Type Toggle --}}
            <div class="flex gap-4 mb-6">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="package_type" value="preset" class="hidden peer" {{ old('package_type', 'custom') === 'preset' ? 'checked' : '' }}>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-cube text-indigo-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Preset Package</p>
                                <p class="text-xs text-gray-500">Use predefined packages</p>
                            </div>
                        </div>
                    </div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="package_type" value="custom" class="hidden peer" {{ old('package_type', 'custom') === 'custom' ? 'checked' : '' }}>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-sliders-h text-purple-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">Custom Package</p>
                                <p class="text-xs text-gray-500">Flexible configuration</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Preset Package Section --}}
            <div id="presetPackageSection" class="hidden">
                @if(isset($packages) && $packages->count() > 0)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Package *</label>
                    <select name="package_id" id="package_select" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Select a Package --</option>
                        @foreach($packages as $package)
                            <option value="{{ $package->id }}"
                                    data-full-tests="{{ $package->full_tests_allowed }}"
                                    data-section-tests="{{ $package->section_tests_allowed }}"
                                    data-validity-days="{{ $package->validity_days }}"
                                    data-price="{{ $package->price ?? 0 }}"
                                    {{ old('package_id') == $package->id ? 'selected' : '' }}>
                                {{ $package->name }} ({{ $package->full_tests_allowed }} Full Tests, {{ $package->validity_days }} Days)
                            </option>
                        @endforeach
                    </select>
                    @error('package_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Package Summary --}}
                <div id="packageSummary" class="p-4 bg-indigo-50 rounded-lg hidden">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 text-sm">
                            <span><i class="fas fa-clipboard-list text-indigo-500 mr-1"></i> <strong id="summaryFullTests">0</strong> Full Tests</span>
                            <span><i class="fas fa-file-alt text-green-500 mr-1"></i> <strong id="summarySectionTests">0</strong> Section Tests</span>
                            <span><i class="fas fa-calendar-alt text-orange-500 mr-1"></i> <strong id="summaryValidity">0</strong> Days</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800"><i class="fas fa-exclamation-triangle mr-1"></i> No preset packages available. Please use custom configuration.</p>
                </div>
                @endif
            </div>

            {{-- Custom Package Section --}}
            <div id="customPackageSection">
                {{-- Add New Tests mode info --}}
                <div id="addNewModeInfo" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-800">
                        <i class="fas fa-info-circle mr-1"></i>
                        <strong>Add New Tests Mode:</strong> Enter how many <u>additional</u> tests to add. Current tests will remain unchanged.
                    </p>
                </div>
                <div id="fullResetModeInfo" class="mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg hidden">
                    <p class="text-sm text-orange-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Full Reset Mode:</strong> Enter the <u>total</u> number of tests for the new package. Counters will reset.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-clipboard-list text-indigo-500 mr-1"></i>
                            <span id="fullTestsLabel">New Full Tests to Add</span>
                        </label>
                        <input type="number" name="full_tests_allowed" id="custom_full_tests"
                               value="{{ old('full_tests_allowed', 0) }}" min="0" max="100"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="text-xs text-gray-500 mt-1" id="fullTestsCurrentInfo">
                            Current: {{ $enrollment->full_tests_allowed }} allowed, {{ $enrollment->full_tests_taken }} taken
                        </p>
                        @error('full_tests_allowed')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-calendar-alt text-orange-500 mr-1"></i> Validity (Days)
                        </label>
                        <input type="number" name="validity_days" id="custom_validity"
                               value="{{ old('validity_days', 30) }}" min="1" max="365"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('validity_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">New expiry: <strong id="newExpiryDate">{{ now()->addDays(30)->format('M d, Y') }}</strong></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            <i class="fas fa-calendar-check text-purple-500 mr-1"></i> Or Set Specific Date
                        </label>
                        <input type="date" name="valid_until" id="custom_valid_until"
                               value="{{ old('valid_until') }}" min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('valid_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-500 mt-1">Overrides days if set</p>
                    </div>
                </div>

                {{-- Per-Section Test Limits --}}
                @php
                    $currentLimits = $enrollment->section_test_limits ?? [];
                @endphp
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-puzzle-piece text-green-500 mr-1"></i> Section Test Limits
                        <span class="text-xs text-gray-500 font-normal">(Set 0 to disable a section)</span>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="border border-blue-200 rounded-xl p-3 bg-blue-50/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-headphones text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Listening</span>
                            </div>
                            <input type="number" name="section_limit_listening" id="section_limit_listening"
                                   value="{{ old('section_limit_listening', $currentLimits['listening'] ?? 0) }}" min="0" max="100"
                                   class="w-full px-3 py-2 border border-blue-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-center font-semibold section-limit-input">
                        </div>
                        <div class="border border-green-200 rounded-xl p-3 bg-green-50/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 bg-green-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book-open text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Reading</span>
                            </div>
                            <input type="number" name="section_limit_reading" id="section_limit_reading"
                                   value="{{ old('section_limit_reading', $currentLimits['reading'] ?? 0) }}" min="0" max="100"
                                   class="w-full px-3 py-2 border border-green-200 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-center font-semibold section-limit-input">
                        </div>
                        <div class="border border-amber-200 rounded-xl p-3 bg-amber-50/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 bg-amber-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-pen-fancy text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Writing</span>
                            </div>
                            <input type="number" name="section_limit_writing" id="section_limit_writing"
                                   value="{{ old('section_limit_writing', $currentLimits['writing'] ?? 0) }}" min="0" max="100"
                                   class="w-full px-3 py-2 border border-amber-200 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-center font-semibold section-limit-input">
                        </div>
                        <div class="border border-purple-200 rounded-xl p-3 bg-purple-50/50">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-7 h-7 bg-purple-500 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-microphone text-white text-xs"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-700">Speaking</span>
                            </div>
                            <input type="number" name="section_limit_speaking" id="section_limit_speaking"
                                   value="{{ old('section_limit_speaking', $currentLimits['speaking'] ?? 0) }}" min="0" max="100"
                                   class="w-full px-3 py-2 border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-center font-semibold section-limit-input">
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Total section tests: <strong id="totalSectionTests">0</strong>
                        </p>
                        <input type="hidden" name="section_tests_allowed" id="custom_section_tests" value="{{ old('section_tests_allowed', $enrollment->section_tests_allowed ?? 0) }}">
                    </div>
                </div>

                {{-- Evaluation Type --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-robot text-purple-500 mr-1"></i> Evaluation Type *
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <label class="cursor-pointer">
                            <input type="radio" name="evaluation_type" value="ai" class="hidden peer" {{ old('evaluation_type', $enrollment->evaluation_type ?? 'ai') === 'ai' ? 'checked' : '' }}>
                            <div class="p-4 border-2 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 transition-all text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-robot text-blue-600 text-xl"></i>
                                </div>
                                <p class="font-semibold text-gray-800">AI Only</p>
                                <p class="text-xs text-gray-500 mt-1">Instant AI evaluation</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="evaluation_type" value="human" class="hidden peer" {{ old('evaluation_type', $enrollment->evaluation_type ?? 'ai') === 'human' ? 'checked' : '' }}>
                            <div class="p-4 border-2 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300 transition-all text-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                                </div>
                                <p class="font-semibold text-gray-800">Human Only</p>
                                <p class="text-xs text-gray-500 mt-1">Teacher evaluation</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="evaluation_type" value="both" class="hidden peer" {{ old('evaluation_type', $enrollment->evaluation_type ?? 'ai') === 'both' ? 'checked' : '' }}>
                            <div class="p-4 border-2 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-all text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <i class="fas fa-balance-scale text-green-600 text-xl"></i>
                                </div>
                                <p class="font-semibold text-gray-800">Both</p>
                                <p class="text-xs text-gray-500 mt-1">AI & Human options</p>
                            </div>
                        </label>
                    </div>
                    @error('evaluation_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Allowed Full Tests Selection --}}
                <div class="mb-6" id="fullTestsSelectionSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-check-circle text-emerald-500 mr-1"></i> Allowed Full Tests
                        <span class="text-xs text-gray-500 font-normal" id="fullTestsHint">(Select exactly <span id="fullTestsRequiredCount">{{ $enrollment->full_tests_allowed }}</span> test(s))</span>
                    </label>
                    @php
                        $currentAllowedTests = $enrollment->allowed_full_tests ?? [];
                    @endphp
                    <div class="border border-gray-200 rounded-xl p-4 max-h-64 overflow-y-auto bg-gray-50" id="fullTestsContainer">
                        @if(isset($fullTests) && $fullTests->count() > 0)
                            <div class="space-y-2">
                                @foreach($fullTests as $test)
                                    @php
                                        $isCompleted = in_array($test->id, $allCompletedIds);
                                        $isCurrentlyAssigned = in_array($test->id, $currentAllowedTests) && !$isCompleted;
                                    @endphp
                                    <label class="flex items-center gap-3 p-2 hover:bg-white rounded-lg cursor-pointer transition full-test-item {{ $isCompleted ? 'opacity-50' : '' }} {{ $isCurrentlyAssigned ? 'bg-green-50 border border-green-200 rounded-lg' : '' }}">
                                        <input type="checkbox" name="allowed_full_tests[]" value="{{ $test->id }}"
                                               class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 full-test-checkbox"
                                               {{ $isCompleted ? 'disabled' : '' }}
                                               {{ $isCurrentlyAssigned ? 'checked disabled data-existing="true"' : '' }}
                                               {{ !$isCompleted && !$isCurrentlyAssigned && is_array(old('allowed_full_tests')) && in_array($test->id, old('allowed_full_tests')) ? 'checked' : '' }}>
                                        {{-- Hidden field to ensure existing tests are submitted --}}
                                        @if($isCurrentlyAssigned)
                                            <input type="hidden" name="allowed_full_tests[]" value="{{ $test->id }}">
                                        @endif
                                        <span class="flex-1 text-sm text-gray-700 {{ $isCompleted ? 'line-through' : '' }}">{{ $test->title }}</span>
                                        @if($test->is_premium)
                                            <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-700 rounded-full">Premium</span>
                                        @endif
                                        @if($isCompleted)
                                            <span class="text-xs px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full">Completed</span>
                                        @elseif($isCurrentlyAssigned)
                                            <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full">
                                                <i class="fas fa-lock mr-1"></i> Currently Assigned
                                            </span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">No full tests available for offline students.</p>
                        @endif
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span id="fullTestsSelectedInfo">Selected: <strong id="fullTestsSelectedCount">0</strong> / <strong id="fullTestsAllowedCount">{{ $enrollment->full_tests_allowed }}</strong></span>
                            @if(count($currentAllowedTests) > 0)
                                <span class="text-green-600 ml-2">({{ count(array_diff($currentAllowedTests, $allCompletedIds)) }} existing)</span>
                            @endif
                        </p>
                        <p class="text-xs text-red-500 hidden" id="fullTestsWarning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Please select exactly <span id="fullTestsWarningCount">{{ $enrollment->full_tests_allowed }}</span> test(s)
                        </p>
                    </div>
                </div>

                {{-- Allowed Section Tests Selection --}}
                <div class="mb-6" id="sectionTestsSelectionSection">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-puzzle-piece text-blue-500 mr-1"></i> Allowed Section Tests
                        <span class="text-xs text-gray-500 font-normal" id="sectionTestsHint">(Select up to <span id="sectionTestsRequiredCount">0</span> test(s))</span>
                    </label>
                    <div class="border border-gray-200 rounded-xl p-3 bg-gray-50" id="sectionTestsContainer">
                        @if(isset($sectionTests) && $sectionTests->count() > 0)
                            @php
                                $sectionStyles = [
                                    'listening' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'icon' => 'fa-headphones', 'accent' => 'text-blue-600'],
                                    'reading' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'icon' => 'fa-book-open', 'accent' => 'text-emerald-600'],
                                    'writing' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'icon' => 'fa-pen-fancy', 'accent' => 'text-amber-600'],
                                    'speaking' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-200', 'text' => 'text-purple-700', 'icon' => 'fa-microphone', 'accent' => 'text-purple-600'],
                                ];
                                $existingSectionTestIds = is_array(old('allowed_section_tests', $enrollment->allowed_section_tests ?? [])) ? old('allowed_section_tests', $enrollment->allowed_section_tests ?? []) : [];
                                $grouped = $sectionTests->groupBy(fn($t) => strtolower($t->section->name ?? 'unknown'));
                                $sectionOrder = ['listening', 'reading', 'writing', 'speaking'];
                            @endphp

                            {{-- Master Select-All toolbar --}}
                            <div class="flex items-center justify-between gap-2 px-3 py-2 mb-3 bg-white rounded-lg border border-gray-200">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" id="selectAllSectionTests"
                                           class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="text-xs font-semibold text-gray-700">Select All ({{ $sectionTests->count() }} tests)</span>
                                </label>
                                <button type="button" id="clearAllSectionTests" class="text-xs text-gray-500 hover:text-red-600 font-medium">
                                    <i class="fas fa-times-circle mr-1"></i> Clear All
                                </button>
                            </div>

                            <div class="space-y-3" x-data="{
                                openSections: {
                                    @foreach($sectionOrder as $sec)
                                        @if(isset($grouped[$sec])) '{{ $sec }}': true, @endif
                                    @endforeach
                                }
                            }">
                                @foreach($sectionOrder as $sectionName)
                                    @if(isset($grouped[$sectionName]) && $grouped[$sectionName]->count() > 0)
                                        @php
                                            $style = $sectionStyles[$sectionName] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-200', 'text' => 'text-gray-700', 'icon' => 'fa-file', 'accent' => 'text-gray-600'];
                                            $sectionCount = $grouped[$sectionName]->count();
                                        @endphp
                                        <div class="bg-white border {{ $style['border'] }} rounded-lg overflow-hidden">
                                            {{-- Section header with bulk select --}}
                                            <div class="flex items-center gap-3 px-3 py-2.5 {{ $style['bg'] }} border-b {{ $style['border'] }}">
                                                <label class="flex items-center gap-2 cursor-pointer flex-1">
                                                    <input type="checkbox"
                                                           class="w-4 h-4 rounded border-gray-300 section-group-checkbox"
                                                           data-section-group="{{ $sectionName }}">
                                                    <i class="fas {{ $style['icon'] }} {{ $style['accent'] }} text-sm"></i>
                                                    <span class="text-sm font-bold {{ $style['text'] }}">{{ ucfirst($sectionName) }}</span>
                                                    <span class="text-[11px] {{ $style['text'] }} opacity-70">
                                                        (<span class="section-group-selected" data-section-group="{{ $sectionName }}">0</span>/{{ $sectionCount }} selected)
                                                    </span>
                                                </label>
                                                <button type="button"
                                                        @click="openSections['{{ $sectionName }}'] = !openSections['{{ $sectionName }}']"
                                                        class="{{ $style['accent'] }} hover:opacity-70">
                                                    <i class="fas fa-chevron-down text-xs transition-transform"
                                                       :class="openSections['{{ $sectionName }}'] ? 'rotate-180' : ''"></i>
                                                </button>
                                            </div>

                                            {{-- Test items --}}
                                            <div x-show="openSections['{{ $sectionName }}']" x-collapse class="p-2 max-h-64 overflow-y-auto">
                                                @foreach($grouped[$sectionName] as $testSet)
                                                    <label class="flex items-center gap-3 px-2 py-1.5 hover:bg-gray-50 rounded cursor-pointer transition section-test-item"
                                                           data-section="{{ $sectionName }}">
                                                        <input type="checkbox" name="allowed_section_tests[]" value="{{ $testSet->id }}"
                                                               class="w-4 h-4 rounded border-gray-300 focus:ring-2 section-test-checkbox"
                                                               data-section="{{ $sectionName }}"
                                                               {{ in_array($testSet->id, $existingSectionTestIds) ? 'checked' : '' }}>
                                                        <span class="flex-1 text-sm text-gray-700">{{ $testSet->title }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">No section tests available.</p>
                        @endif
                    </div>
                    {{-- Per-section selection counters --}}
                    <div class="mt-2 space-y-1" id="sectionTestCounters">
                        <div class="flex items-center flex-wrap gap-2 text-xs">
                            <span class="text-gray-500"><i class="fas fa-info-circle mr-1"></i> Selected:</span>
                            <span class="px-1.5 py-0.5 bg-blue-50 text-blue-700 rounded font-medium" id="counterListening">Listening: <strong>0</strong></span>
                            <span class="px-1.5 py-0.5 bg-green-50 text-green-700 rounded font-medium" id="counterReading">Reading: <strong>0</strong></span>
                            <span class="px-1.5 py-0.5 bg-amber-50 text-amber-700 rounded font-medium" id="counterWriting">Writing: <strong>0</strong></span>
                            <span class="px-1.5 py-0.5 bg-purple-50 text-purple-700 rounded font-medium" id="counterSpeaking">Speaking: <strong>0</strong></span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-1">
                        <p class="text-xs text-gray-500">
                            <span id="sectionTestsSelectedInfo">Total selected: <strong id="sectionTestsSelectedCount">0</strong> / <strong id="sectionTestsAllowedCount">0</strong></span>
                        </p>
                        <p class="text-xs text-red-500 hidden" id="sectionTestsWarning">
                            <i class="fas fa-exclamation-triangle mr-1"></i> <span id="sectionTestsWarningText">Limit exceeded</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payment (Optional) --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-money-bill text-indigo-500 mr-2"></i> Payment
                <span class="ml-2 text-xs font-normal text-gray-500">(Optional)</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
                    <input type="number" name="total_amount" id="total_amount" value="{{ old('total_amount', 0) }}"
                           min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Paid Amount</label>
                    <input type="number" name="paid_amount" value="{{ old('paid_amount', 0) }}"
                           min="0" step="0.01"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        {{-- Summary Card --}}
        <div id="enrollmentSummary" class="mb-8 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-xl border border-indigo-100">
            <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                <i class="fas fa-receipt text-indigo-500 mr-2"></i> Renewal Summary
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div class="p-3 bg-white rounded-lg shadow-sm">
                    <p class="text-2xl font-bold text-indigo-600" id="summaryTestsDisplay">{{ $enrollment->full_tests_allowed }}</p>
                    <p class="text-xs text-gray-500">Full Tests</p>
                </div>
                <div class="p-3 bg-white rounded-lg shadow-sm">
                    <p class="text-2xl font-bold text-green-600" id="summarySectionsDisplay">{{ $enrollment->section_tests_allowed }}</p>
                    <p class="text-xs text-gray-500">Section Tests</p>
                </div>
                <div class="p-3 bg-white rounded-lg shadow-sm">
                    <p class="text-2xl font-bold text-orange-600" id="summaryValidityDisplay">30</p>
                    <p class="text-xs text-gray-500">Days Validity</p>
                </div>
                <div class="p-3 bg-white rounded-lg shadow-sm">
                    <p class="text-lg font-bold text-purple-600" id="summaryEvaluationDisplay">{{ ucfirst($enrollment->evaluation_type ?? 'AI') }}</p>
                    <p class="text-xs text-gray-500">Evaluation</p>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-4">
            <a href="{{ route('branch.students.show', $enrollment) }}"
               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center">
                <i class="fas fa-sync-alt mr-2"></i> Renew Package
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const renewalModeRadios = document.querySelectorAll('input[name="renewal_mode_select"]');
    const renewalModeHidden = document.getElementById('renewal_mode');
    const packageTypeRadios = document.querySelectorAll('input[name="package_type"]');
    const presetSection = document.getElementById('presetPackageSection');
    const customSection = document.getElementById('customPackageSection');
    const packageSelect = document.getElementById('package_select');
    const packageSummary = document.getElementById('packageSummary');

    // Summary display elements
    const summaryTestsDisplay = document.getElementById('summaryTestsDisplay');
    const summarySectionsDisplay = document.getElementById('summarySectionsDisplay');
    const summaryValidityDisplay = document.getElementById('summaryValidityDisplay');
    const summaryEvaluationDisplay = document.getElementById('summaryEvaluationDisplay');

    // Custom package inputs
    const customFullTests = document.getElementById('custom_full_tests');
    const customSectionTests = document.getElementById('custom_section_tests');
    const customValidity = document.getElementById('custom_validity');
    const evaluationRadios = document.querySelectorAll('input[name="evaluation_type"]');

    // Preset summary elements
    const summaryFullTests = document.getElementById('summaryFullTests');
    const summarySectionTests = document.getElementById('summarySectionTests');
    const summaryValidity = document.getElementById('summaryValidity');

    // Dynamic test selection elements
    const fullTestCheckboxes = document.querySelectorAll('.full-test-checkbox:not(:disabled)');
    const sectionTestCheckboxes = document.querySelectorAll('.section-test-checkbox');
    const fullTestsRequiredCount = document.getElementById('fullTestsRequiredCount');
    const fullTestsSelectedCount = document.getElementById('fullTestsSelectedCount');
    const fullTestsAllowedCount = document.getElementById('fullTestsAllowedCount');
    const fullTestsWarning = document.getElementById('fullTestsWarning');
    const fullTestsWarningCount = document.getElementById('fullTestsWarningCount');
    const sectionTestsRequiredCount = document.getElementById('sectionTestsRequiredCount');
    const sectionTestsSelectedCount = document.getElementById('sectionTestsSelectedCount');
    const sectionTestsAllowedCount = document.getElementById('sectionTestsAllowedCount');
    const sectionTestsWarning = document.getElementById('sectionTestsWarning');
    const sectionTestsWarningCount = document.getElementById('sectionTestsWarningCount');
    const sectionTestsSelectionSection = document.getElementById('sectionTestsSelectionSection');

    // Per-section limit inputs
    const sectionLimitInputs = document.querySelectorAll('.section-limit-input');
    const totalSectionTestsDisplay = document.getElementById('totalSectionTests');

    // Track allowed counts
    let maxFullTests = parseInt(customFullTests?.value) || 1;
    let maxSectionTests = parseInt(customSectionTests?.value) || 0;

    // Per-section limit values
    function getSectionLimits() {
        return {
            listening: parseInt(document.getElementById('section_limit_listening')?.value) || 0,
            reading: parseInt(document.getElementById('section_limit_reading')?.value) || 0,
            writing: parseInt(document.getElementById('section_limit_writing')?.value) || 0,
            speaking: parseInt(document.getElementById('section_limit_speaking')?.value) || 0,
        };
    }

    // Count selected section tests per type
    function getSelectedPerSection() {
        const counts = { listening: 0, reading: 0, writing: 0, speaking: 0 };
        document.querySelectorAll('.section-test-checkbox:checked').forEach(cb => {
            const sec = cb.dataset.section;
            if (counts.hasOwnProperty(sec)) counts[sec]++;
        });
        return counts;
    }

    // Calculate total section tests from per-section limits
    function updateSectionTestTotal() {
        let total = 0;
        sectionLimitInputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });

        if (customSectionTests) customSectionTests.value = total;
        if (totalSectionTestsDisplay) totalSectionTestsDisplay.textContent = total;
        maxSectionTests = total;

        if (sectionTestsSelectionSection) {
            if (total > 0) {
                sectionTestsSelectionSection.classList.remove('hidden');
            } else {
                sectionTestsSelectionSection.classList.add('hidden');
                sectionTestCheckboxes.forEach(cb => cb.checked = false);
            }
        }

        filterSectionTestsByLimits();
        updateSummaryDisplay();
        updateTestSelectionLimits();
    }

    // Show/hide section test items based on per-section limits
    function filterSectionTestsByLimits() {
        const limits = getSectionLimits();
        document.querySelectorAll('.section-test-item').forEach(item => {
            const sec = item.dataset.section;
            const limit = limits[sec] ?? 0;
            if (limit > 0) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
                const cb = item.querySelector('.section-test-checkbox');
                if (cb) cb.checked = false;
            }
        });
    }

    // Elements for mode-specific UI
    const addNewModeInfo = document.getElementById('addNewModeInfo');
    const fullResetModeInfo = document.getElementById('fullResetModeInfo');
    const fullTestsLabel = document.getElementById('fullTestsLabel');
    const sectionTestsLabel = document.getElementById('sectionTestsLabel');

    // Sync renewal mode to hidden field and update UI
    renewalModeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            renewalModeHidden.value = this.value;
            updateModeUI(this.value);
        });
    });

    // Update UI based on renewal mode
    function updateModeUI(mode) {
        if (mode === 'add_new') {
            addNewModeInfo?.classList.remove('hidden');
            fullResetModeInfo?.classList.add('hidden');
            if (fullTestsLabel) fullTestsLabel.textContent = 'New Full Tests to Add *';
            if (customFullTests) customFullTests.value = 1;
            // Reset section limits to 0 for add mode
            sectionLimitInputs.forEach(input => input.value = 0);
        } else {
            addNewModeInfo?.classList.add('hidden');
            fullResetModeInfo?.classList.remove('hidden');
            if (fullTestsLabel) fullTestsLabel.textContent = 'Total Full Tests Allowed *';
            if (customFullTests) customFullTests.value = {{ $enrollment->full_tests_allowed }};
            // Restore current section limits for reset mode
            @php $cl = $enrollment->section_test_limits ?? []; @endphp
            document.getElementById('section_limit_listening').value = {{ $cl['listening'] ?? 0 }};
            document.getElementById('section_limit_reading').value = {{ $cl['reading'] ?? 0 }};
            document.getElementById('section_limit_writing').value = {{ $cl['writing'] ?? 0 }};
            document.getElementById('section_limit_speaking').value = {{ $cl['speaking'] ?? 0 }};
        }
        updateSectionTestTotal();
        updateTestSelectionLimits();
    }

    // Toggle package type sections
    function togglePackageType() {
        const selectedType = document.querySelector('input[name="package_type"]:checked')?.value;

        if (selectedType === 'preset') {
            presetSection.classList.remove('hidden');
            customSection.classList.add('hidden');
            if (customFullTests) customFullTests.removeAttribute('required');
            if (customValidity) customValidity.removeAttribute('required');
            if (packageSelect) packageSelect.setAttribute('required', 'required');
        } else {
            presetSection.classList.add('hidden');
            customSection.classList.remove('hidden');
            if (customFullTests) customFullTests.setAttribute('required', 'required');
            if (customValidity) customValidity.setAttribute('required', 'required');
            if (packageSelect) packageSelect.removeAttribute('required');
        }

        updateSummaryDisplay();
        updateTestSelectionLimits();
    }

    // Update test selection limits based on allowed counts
    function updateTestSelectionLimits() {
        maxFullTests = parseInt(customFullTests?.value) || 1;

        // Update display counts
        if (fullTestsRequiredCount) fullTestsRequiredCount.textContent = maxFullTests;
        if (fullTestsAllowedCount) fullTestsAllowedCount.textContent = maxFullTests;
        if (fullTestsWarningCount) fullTestsWarningCount.textContent = maxFullTests;

        if (sectionTestsRequiredCount) sectionTestsRequiredCount.textContent = maxSectionTests;
        if (sectionTestsAllowedCount) sectionTestsAllowedCount.textContent = maxSectionTests;

        enforceFullTestLimit();
        enforceSectionTestLimit();
    }

    // Enforce full test selection limit
    function enforceFullTestLimit() {
        const checkedCount = document.querySelectorAll('.full-test-checkbox:checked').length;

        if (fullTestsSelectedCount) fullTestsSelectedCount.textContent = checkedCount;

        fullTestCheckboxes.forEach(checkbox => {
            if (!checkbox.checked && checkedCount >= maxFullTests) {
                checkbox.disabled = true;
                checkbox.closest('.full-test-item')?.classList.add('opacity-50');
            } else if (!checkbox.hasAttribute('data-completed')) {
                checkbox.disabled = false;
                checkbox.closest('.full-test-item')?.classList.remove('opacity-50');
            }
        });

        if (fullTestsWarning) {
            if (checkedCount !== maxFullTests && maxFullTests > 0) {
                fullTestsWarning.classList.remove('hidden');
            } else {
                fullTestsWarning.classList.add('hidden');
            }
        }
    }

    // Enforce section test selection limit (per-section)
    function enforceSectionTestLimit() {
        const limits = getSectionLimits();
        const selected = getSelectedPerSection();
        const totalChecked = document.querySelectorAll('.section-test-checkbox:checked').length;

        if (sectionTestsSelectedCount) sectionTestsSelectedCount.textContent = totalChecked;

        // Update per-section counters
        ['listening', 'reading', 'writing', 'speaking'].forEach(sec => {
            const counterEl = document.getElementById('counter' + sec.charAt(0).toUpperCase() + sec.slice(1));
            if (counterEl) {
                const strong = counterEl.querySelector('strong');
                if (strong) strong.textContent = selected[sec];
                if (limits[sec] <= 0) {
                    counterEl.classList.add('hidden');
                } else {
                    counterEl.classList.remove('hidden');
                    if (selected[sec] > limits[sec]) {
                        counterEl.classList.add('!bg-red-50', '!text-red-700');
                    } else {
                        counterEl.classList.remove('!bg-red-50', '!text-red-700');
                    }
                }
            }
        });

        // Enable/disable checkboxes based on per-section limit
        let hasWarning = false;
        sectionTestCheckboxes.forEach(checkbox => {
            const sec = checkbox.dataset.section;
            const secLimit = limits[sec] ?? 0;
            const secSelected = selected[sec] ?? 0;
            if (!checkbox.checked && secSelected >= secLimit) {
                checkbox.disabled = true;
                checkbox.closest('.section-test-item')?.classList.add('opacity-50');
            } else {
                checkbox.disabled = false;
                checkbox.closest('.section-test-item')?.classList.remove('opacity-50');
            }
            if (secSelected > secLimit) hasWarning = true;
        });

        if (sectionTestsWarning) {
            const warningText = document.getElementById('sectionTestsWarningText');
            if (hasWarning) {
                if (warningText) warningText.textContent = 'Per-section limit exceeded!';
                sectionTestsWarning.classList.remove('hidden');
            } else if (totalChecked > maxSectionTests) {
                if (warningText) warningText.textContent = `Maximum ${maxSectionTests} test(s) allowed`;
                sectionTestsWarning.classList.remove('hidden');
            } else {
                sectionTestsWarning.classList.add('hidden');
            }
        }
    }

    // Update summary display
    function updateSummaryDisplay() {
        const selectedType = document.querySelector('input[name="package_type"]:checked')?.value;

        if (selectedType === 'preset' && packageSelect && packageSelect.value) {
            const selectedOption = packageSelect.options[packageSelect.selectedIndex];
            summaryTestsDisplay.textContent = selectedOption.dataset.fullTests || '0';
            summarySectionsDisplay.textContent = selectedOption.dataset.sectionTests || '0';
            summaryValidityDisplay.textContent = selectedOption.dataset.validityDays || '0';
            summaryEvaluationDisplay.textContent = 'AI';
        } else {
            summaryTestsDisplay.textContent = customFullTests?.value || '0';
            summarySectionsDisplay.textContent = customSectionTests?.value || '0';
            summaryValidityDisplay.textContent = customValidity?.value || '0';

            const selectedEval = document.querySelector('input[name="evaluation_type"]:checked')?.value;
            const evalLabels = { 'ai': 'AI', 'human': 'Human', 'both': 'Both' };
            summaryEvaluationDisplay.textContent = evalLabels[selectedEval] || 'AI';
        }
    }

    // Handle package select change
    if (packageSelect) {
        packageSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value) {
                packageSummary.classList.remove('hidden');
                if (summaryFullTests) summaryFullTests.textContent = selectedOption.dataset.fullTests || 0;
                if (summarySectionTests) summarySectionTests.textContent = selectedOption.dataset.sectionTests || 0;
                if (summaryValidity) summaryValidity.textContent = selectedOption.dataset.validityDays || 0;

                // Update total amount
                const totalAmountInput = document.getElementById('total_amount');
                if (totalAmountInput) {
                    totalAmountInput.value = selectedOption.dataset.price || 0;
                }
            } else {
                packageSummary.classList.add('hidden');
            }

            updateSummaryDisplay();
        });
    }

    // Event listeners
    packageTypeRadios.forEach(radio => {
        radio.addEventListener('change', togglePackageType);
    });

    if (customFullTests) {
        customFullTests.addEventListener('input', function() {
            updateSummaryDisplay();
            updateTestSelectionLimits();
        });
    }

    // Event listeners for per-section limit inputs
    sectionLimitInputs.forEach(input => {
        input.addEventListener('input', updateSectionTestTotal);
    });

    if (customValidity) {
        customValidity.addEventListener('input', function() {
            updateSummaryDisplay();
            updateExpiryDate();
        });
    }

    // Update expiry date preview
    function updateExpiryDate() {
        const days = parseInt(customValidity?.value) || 30;
        const newDate = new Date();
        newDate.setDate(newDate.getDate() + days);
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        const newExpiryDate = document.getElementById('newExpiryDate');
        if (newExpiryDate) {
            newExpiryDate.textContent = newDate.toLocaleDateString('en-US', options);
        }
    }

    // Specific date overrides days
    const customValidUntil = document.getElementById('custom_valid_until');
    if (customValidUntil) {
        customValidUntil.addEventListener('change', function() {
            const newExpiryDate = document.getElementById('newExpiryDate');
            if (this.value && newExpiryDate) {
                const date = new Date(this.value);
                const options = { year: 'numeric', month: 'short', day: 'numeric' };
                newExpiryDate.textContent = date.toLocaleDateString('en-US', options);
            }
        });
    }

    evaluationRadios.forEach(radio => {
        radio.addEventListener('change', updateSummaryDisplay);
    });

    fullTestCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', enforceFullTestLimit);
    });

    sectionTestCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', enforceSectionTestLimit);
    });

    // ============== BULK SELECT FOR SECTION TESTS ==============
    function updateGroupSelectedCounts() {
        document.querySelectorAll('.section-group-selected').forEach(span => {
            const group = span.dataset.sectionGroup;
            const checked = document.querySelectorAll(`.section-test-checkbox[data-section="${group}"]:checked`).length;
            span.textContent = checked;
        });
        // Sync per-group master checkbox state (indeterminate / checked / unchecked)
        document.querySelectorAll('.section-group-checkbox').forEach(master => {
            const group = master.dataset.sectionGroup;
            const all = document.querySelectorAll(`.section-test-checkbox[data-section="${group}"]`);
            const checked = document.querySelectorAll(`.section-test-checkbox[data-section="${group}"]:checked`);
            if (checked.length === 0) {
                master.checked = false;
                master.indeterminate = false;
            } else if (checked.length === all.length) {
                master.checked = true;
                master.indeterminate = false;
            } else {
                master.checked = false;
                master.indeterminate = true;
            }
        });
        // Sync global master
        const globalMaster = document.getElementById('selectAllSectionTests');
        if (globalMaster) {
            const all = sectionTestCheckboxes.length;
            const checked = document.querySelectorAll('.section-test-checkbox:checked').length;
            if (checked === 0) { globalMaster.checked = false; globalMaster.indeterminate = false; }
            else if (checked === all) { globalMaster.checked = true; globalMaster.indeterminate = false; }
            else { globalMaster.checked = false; globalMaster.indeterminate = true; }
        }
    }

    // Per-group master toggle
    document.querySelectorAll('.section-group-checkbox').forEach(master => {
        master.addEventListener('change', function () {
            const group = this.dataset.sectionGroup;
            document.querySelectorAll(`.section-test-checkbox[data-section="${group}"]`).forEach(cb => {
                cb.checked = this.checked;
            });
            enforceSectionTestLimit();
            updateGroupSelectedCounts();
        });
    });

    // Global master toggle
    document.getElementById('selectAllSectionTests')?.addEventListener('change', function () {
        sectionTestCheckboxes.forEach(cb => { cb.checked = this.checked; });
        enforceSectionTestLimit();
        updateGroupSelectedCounts();
    });

    // Clear all
    document.getElementById('clearAllSectionTests')?.addEventListener('click', function () {
        sectionTestCheckboxes.forEach(cb => { cb.checked = false; });
        enforceSectionTestLimit();
        updateGroupSelectedCounts();
    });

    // Update counts on any individual test toggle
    sectionTestCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateGroupSelectedCounts);
    });

    // Form validation removed — branch admin can renew with section-only, full-only, or both.

    // Initialize
    togglePackageType();
    updateSectionTestTotal();
    updateTestSelectionLimits();
    updateGroupSelectedCounts();
});
</script>
@endpush
@endsection
