<x-admin-layout>
    <x-slot:title>Full Test Attempt Details</x-slot>

    <!-- Page Header -->
    <div class="mb-6">
        <div class="rounded-xl bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Full Test Attempt Details</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $fullTestAttempt->fullTest->title }} - {{ $fullTestAttempt->user->name }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Started: {{ $fullTestAttempt->start_time->format('M d, Y h:i A') }}
                        @if($fullTestAttempt->end_time)
                            | Ended: {{ $fullTestAttempt->end_time->format('M d, Y h:i A') }}
                        @endif
                    </p>
                </div>
                <a href="{{ route('admin.full-tests.show', $fullTestAttempt->fullTest) }}"
                   class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 hover:bg-gray-50 transition-colors">
                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-900">Back to Full Test</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Scores -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="rounded-xl bg-white p-6 shadow-sm text-center">
            <p class="text-xs text-gray-600 mb-2">Overall Band</p>
            <p class="text-3xl font-bold {{ $fullTestAttempt->overall_band_score ? 'text-indigo-600' : 'text-yellow-600' }}">
                {{ $fullTestAttempt->overall_band_score ? number_format($fullTestAttempt->overall_band_score, 1) : 'Pending' }}
            </p>
        </div>

        @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
            @if($fullTestAttempt->fullTest->hasSection($section))
                @php
                    $scoreField = $section . '_score';
                    $score = $fullTestAttempt->$scoreField;
                @endphp
                <div class="rounded-xl bg-white p-6 shadow-sm text-center">
                    <p class="text-xs text-gray-600 mb-2 capitalize">{{ $section }}</p>
                    <p class="text-2xl font-bold {{ $score !== null ? 'text-gray-900' : 'text-yellow-600' }}">
                        {{ $score !== null ? number_format($score, 1) : 'Pending' }}
                    </p>
                    <button onclick="openScoreModal('{{ $section }}', {{ $score ?? 0 }})"
                            class="mt-2 text-xs text-indigo-600 hover:text-indigo-800">
                        Edit Score
                    </button>
                </div>
            @endif
        @endforeach
    </div>

    <!-- Score Edit Modal -->
    <div id="scoreModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Update <span id="modalSectionName"></span> Score</h3>
                <button onclick="closeScoreModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="scoreUpdateForm" method="POST" action="{{ route('admin.full-test-attempts.update-score', $fullTestAttempt) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="section" id="sectionInput">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Band Score (0.0 - 9.0)
                    </label>
                    <input type="number"
                           name="score"
                           id="scoreInput"
                           step="0.5"
                           min="0"
                           max="9"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Enter score in 0.5 increments (e.g., 6.0, 6.5, 7.0)</p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button"
                            onclick="closeScoreModal()"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Update Score
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openScoreModal(section, currentScore) {
            document.getElementById('scoreModal').classList.remove('hidden');
            document.getElementById('sectionInput').value = section;
            document.getElementById('scoreInput').value = currentScore;
            document.getElementById('modalSectionName').textContent = section.charAt(0).toUpperCase() + section.slice(1);
        }

        function closeScoreModal() {
            document.getElementById('scoreModal').classList.add('hidden');
        }

        // Close modal on outside click
        document.getElementById('scoreModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeScoreModal();
            }
        });
    </script>

    <!-- Sections -->
    @foreach($fullTestAttempt->sectionAttempts as $sectionAttempt)
        @php
            $studentAttempt = $sectionAttempt->studentAttempt;
            $sectionType = $sectionAttempt->section_type;
            $sectionName = ucfirst($sectionType);
            $sectionColors = [
                'listening' => 'purple',
                'reading' => 'green',
                'writing' => 'blue',
                'speaking' => 'orange'
            ];
            $color = $sectionColors[$sectionType] ?? 'gray';
        @endphp

        <div class="rounded-xl bg-white p-6 shadow-sm mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $sectionName }} Section</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Test Set: {{ $studentAttempt->testSet->title }}
                        @if($studentAttempt->band_score)
                            | Band Score: <span class="font-semibold text-{{ $color }}-600">{{ number_format($studentAttempt->band_score, 1) }}</span>
                        @endif
                    </p>
                </div>

                @if(in_array($sectionType, ['writing', 'speaking']) && $studentAttempt->humanEvaluationRequest)
                    <a href="{{ route('admin.attempts.show', $studentAttempt) }}"
                       class="inline-flex items-center rounded-lg bg-{{ $color }}-600 px-4 py-2 text-sm font-medium text-white hover:bg-{{ $color }}-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Full Details
                    </a>
                @else
                    <a href="{{ route('admin.attempts.show', $studentAttempt) }}"
                       class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2 hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-900">View Details</span>
                    </a>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="text-xs text-gray-600">Total Questions</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $studentAttempt->total_questions ?? 0 }}</p>
                </div>
                <div class="border border-gray-200 rounded-lg p-3">
                    <p class="text-xs text-gray-600">Answered</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $studentAttempt->answered_questions ?? 0 }}</p>
                </div>
                @if(in_array($sectionType, ['listening', 'reading']))
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Correct</p>
                        <p class="text-lg font-semibold text-green-600">{{ $studentAttempt->correct_answers ?? 0 }}</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Accuracy</p>
                        <p class="text-lg font-semibold text-gray-900">
                            {{ $studentAttempt->total_questions > 0 ? round(($studentAttempt->correct_answers / $studentAttempt->total_questions) * 100) : 0 }}%
                        </p>
                    </div>
                @else
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Completion</p>
                        <p class="text-lg font-semibold text-gray-900">{{ round($studentAttempt->completion_rate ?? 0) }}%</p>
                    </div>
                    <div class="border border-gray-200 rounded-lg p-3">
                        <p class="text-xs text-gray-600">Evaluation</p>
                        <p class="text-sm font-medium {{ $studentAttempt->humanEvaluationRequest?->status === 'completed' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ $studentAttempt->humanEvaluationRequest?->status === 'completed' ? 'Complete' : 'Pending' }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endforeach

</x-admin-layout>
