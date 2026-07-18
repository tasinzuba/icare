<x-admin-layout>
    <x-slot:title>Create Full Test</x-slot>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Full Test</h1>
                    <p class="mt-1 text-sm text-gray-600">Combine individual section tests to create a complete IELTS mock test</p>
                </div>
                <a href="{{ route('admin.full-tests.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.full-tests.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <!-- Basic Information Card -->
        <div class="rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center">
                    <svg class="mr-3 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">Basic Information</h3>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Test Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="title" 
                               value="{{ old('title') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="e.g., Cambridge IELTS 18 Test 1"
                               required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <input type="text" 
                               name="description" 
                               id="description" 
                               value="{{ old('description') }}"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Brief description of the test">
                        @error('description')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-lg border-2 border-gray-200 p-4 hover:border-green-300 transition-colors">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox"
                                   name="active"
                                   value="1"
                                   {{ old('active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                            <span class="ml-3 flex items-center text-sm font-medium text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Active Status
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Student Visibility Section -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                        Student Visibility <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-gray-500 mb-3">Keep this enabled so the test is available to your branch/offline students.</p>

                    <div class="grid grid-cols-1 gap-4">
                        <!-- For Branch/Offline Students -->
                        <div class="rounded-lg border-2 border-gray-200 p-4 hover:border-orange-300 transition-colors" id="offline-checkbox-container">
                            <label class="flex items-start cursor-pointer">
                                <div class="flex h-5 items-center">
                                    <input type="checkbox"
                                           id="is_for_offline"
                                           name="is_for_offline"
                                           value="1"
                                           {{ old('is_for_offline', true) ? 'checked' : '' }}
                                           class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                           onchange="validateVisibility()">
                                </div>
                                <div class="ml-3">
                                    <span class="flex items-center text-sm font-medium text-gray-700">
                                        <svg class="mr-2 h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Branch/Offline Students
                                    </span>
                                    <p class="text-xs text-gray-500 mt-1">Students enrolled in physical branches</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <p id="visibility-error" class="mt-2 text-sm text-red-600 hidden">
                        <svg class="inline mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Please select at least one student type
                    </p>
                </div>
            </div>
        </div>

        <!-- Section Selection Card -->
        <div class="rounded-xl bg-white shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 px-6 py-4">
                <div class="flex items-center">
                    <svg class="mr-3 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-900">Select Test Sets for Each Section</h3>
                </div>
                <p class="mt-1 text-sm text-gray-500">Choose test sets for sections. Minimum 3 sections are required to create a Full Test.</p>
                <div class="mt-2 rounded-lg bg-amber-50 border border-amber-200 p-3">
                    <p class="text-sm text-amber-800">
                        <svg class="inline h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <strong>Note:</strong> You can create a full test with any combination of 3 or 4 sections.
                    </p>
                </div>
            </div>
            
            <div class="p-6 space-y-6">
                <!-- Listening Section -->
                <div class="rounded-lg border-2 border-purple-200 bg-purple-50 p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-purple-900">Listening Section</h3>
                    </div>
                    <select name="listening_test_set_id" 
                            id="listening_test_set_id" 
                            class="block w-full rounded-lg border-purple-300 bg-white shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                        <option value="">Select a listening test set</option>
                        @foreach($testSets['listening'] ?? [] as $testSet)
                            <option value="{{ $testSet->id }}" 
                                    {{ old('listening_test_set_id') == $testSet->id ? 'selected' : '' }}>
                                {{ $testSet->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('listening_test_set_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Reading Section -->
                <div class="rounded-lg border-2 border-green-200 bg-green-50 p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-green-900">Reading Section</h3>
                    </div>
                    <select name="reading_test_set_id" 
                            id="reading_test_set_id" 
                            class="block w-full rounded-lg border-green-300 bg-white shadow-sm focus:border-green-500 focus:ring-green-500 sm:text-sm">
                        <option value="">Select a reading test set</option>
                        @foreach($testSets['reading'] ?? [] as $testSet)
                            <option value="{{ $testSet->id }}" 
                                    {{ old('reading_test_set_id') == $testSet->id ? 'selected' : '' }}>
                                {{ $testSet->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('reading_test_set_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Writing Section -->
                <div class="rounded-lg border-2 border-blue-200 bg-blue-50 p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-blue-900">Writing Section</h3>
                    </div>
                    <select name="writing_test_set_id" 
                            id="writing_test_set_id" 
                            class="block w-full rounded-lg border-blue-300 bg-white shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">Select a writing test set</option>
                        @foreach($testSets['writing'] ?? [] as $testSet)
                            <option value="{{ $testSet->id }}" 
                                    {{ old('writing_test_set_id') == $testSet->id ? 'selected' : '' }}>
                                {{ $testSet->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('writing_test_set_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Speaking Section -->
                <div class="rounded-lg border-2 border-orange-200 bg-orange-50 p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-600">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                            </svg>
                        </div>
                        <h3 class="ml-4 text-lg font-semibold text-orange-900">Speaking Section</h3>
                    </div>
                    <select name="speaking_test_set_id" 
                            id="speaking_test_set_id" 
                            class="block w-full rounded-lg border-orange-300 bg-white shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                        <option value="">Select a speaking test set</option>
                        @foreach($testSets['speaking'] ?? [] as $testSet)
                            <option value="{{ $testSet->id }}" 
                                    {{ old('speaking_test_set_id') == $testSet->id ? 'selected' : '' }}>
                                {{ $testSet->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('speaking_test_set_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('admin.full-tests.index') }}" 
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </a>
            <button type="submit" 
                    class="inline-flex items-center rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Create Full Test
            </button>
        </div>
    </form>
    
    @push('scripts')
    <script>
        function validateVisibility() {
            const offlineChecked = document.getElementById('is_for_offline').checked;
            const errorMsg = document.getElementById('visibility-error');
            const offlineContainer = document.getElementById('offline-checkbox-container');

            if (!offlineChecked) {
                errorMsg.classList.remove('hidden');
                offlineContainer.classList.add('border-red-300');
            } else {
                errorMsg.classList.add('hidden');
                offlineContainer.classList.remove('border-red-300');

                // Highlight selected option
                offlineContainer.classList.toggle('border-orange-400', offlineChecked);
                offlineContainer.classList.toggle('bg-orange-50', offlineChecked);
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const selects = {
                listening: document.getElementById('listening_test_set_id'),
                reading: document.getElementById('reading_test_set_id'),
                writing: document.getElementById('writing_test_set_id'),
                speaking: document.getElementById('speaking_test_set_id')
            };

            // Initial visibility validation
            validateVisibility();

            form.addEventListener('submit', function(e) {
                let selectedCount = 0;

                for (let section in selects) {
                    if (selects[section].value) {
                        selectedCount++;
                    }
                }

                if (selectedCount < 3) {
                    e.preventDefault();
                    alert('Please select at least 3 sections to create a full test.');
                    return false;
                }

                // Validate visibility
                const offlineChecked = document.getElementById('is_for_offline').checked;

                if (!offlineChecked) {
                    e.preventDefault();
                    validateVisibility();
                    document.getElementById('visibility-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            });

            // Update UI to show section count
            function updateSectionCount() {
                let selectedCount = 0;

                for (let section in selects) {
                    if (selects[section].value) {
                        selectedCount++;
                    }
                }

                const submitButton = form.querySelector('button[type="submit"]');
                if (selectedCount < 3) {
                    submitButton.setAttribute('title', `Select ${3 - selectedCount} more section(s)`);
                } else {
                    submitButton.setAttribute('title', 'Create Full Test');
                }
            }

            // Add change listeners
            for (let section in selects) {
                selects[section].addEventListener('change', updateSectionCount);
            }

            updateSectionCount();
        });
    </script>
    @endpush
</x-admin-layout>
