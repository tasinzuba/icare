<x-admin-layout>
    <x-slot:title>Create Test Set</x-slot>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create Test Set</h1>
                    <p class="mt-1 text-sm text-gray-600">Create a new test set for a specific IELTS section</p>
                </div>
                <a href="{{ route('admin.test-sets.index') }}"
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Test Sets
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="rounded-xl bg-white shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <div class="flex items-center">
                <svg class="mr-3 h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Test Set Information</h3>
            </div>
        </div>

        <form action="{{ route('admin.test-sets.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Title Field -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Test Set Title <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="title"
                       name="title"
                       value="{{ old('title') }}"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-500 @enderror"
                       placeholder="e.g., Listening Test - Academic Module 1"
                       required>
                @error('title')
                    <p class="mt-2 flex items-center text-sm text-red-600">
                        <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Section Selection -->
            <div>
                <label for="section_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Section <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <select id="section_id"
                            name="section_id"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('section_id') border-red-500 @enderror"
                            required>
                        <option value="">Select a section...</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}"
                                    {{ old('section_id', request('section')) == $section->id ? 'selected' : '' }}
                                    data-time="{{ $section->time_limit }}"
                                    data-name="{{ $section->name }}">
                                {{ ucfirst($section->name) }} - {{ $section->time_limit }} minutes
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                @error('section_id')
                    <p class="mt-2 flex items-center text-sm text-red-600">
                        <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        {{ $message }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-gray-500">
                        <svg class="inline-block mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Select the IELTS section for this test set
                    </p>
                @enderror
            </div>

            <!-- Section Info Display -->
            <div id="section-info" class="hidden rounded-lg border-2 border-indigo-100 bg-indigo-50 p-4">
                <div class="flex items-start">
                    <svg class="mr-3 h-5 w-5 text-indigo-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-indigo-900">Selected Section</h4>
                        <p class="mt-1 text-sm text-indigo-700">
                            <span id="section-name" class="font-medium"></span>
                            <span id="section-time" class="ml-2"></span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Writing Task Type (Writing Only) -->
            <div id="writing-task-section" class="hidden space-y-4">
                <div>
                    <label for="writing_task_type" class="block text-sm font-medium text-gray-700 mb-2">
                        <span class="flex items-center">
                            <svg class="mr-2 h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Writing Task Type
                        </span>
                    </label>
                    <select id="writing_task_type"
                            name="writing_task_type"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <option value="">Combined (Task 1 + Task 2)</option>
                        <option value="task1" {{ old('writing_task_type') === 'task1' ? 'selected' : '' }}>Task 1 Only (Report / Letter) — 20 min default</option>
                        <option value="task2" {{ old('writing_task_type') === 'task2' ? 'selected' : '' }}>Task 2 Only (Essay) — 40 min default</option>
                    </select>
                    <p class="mt-2 text-sm text-gray-500">
                        Choose whether this test set has a single task or combined Task 1 + Task 2.
                    </p>
                </div>

                <div>
                    <label for="writing_category" class="block text-sm font-medium text-gray-700 mb-2">
                        Writing Module / Tab <span class="text-gray-400 text-xs">(Shown as sidebar tab on student dashboard)</span>
                    </label>
                    <select id="writing_category"
                            name="writing_category"
                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                        <option value="">— Uncategorized —</option>
                        @foreach(config('writing_categories') as $slug => $label)
                            <option value="{{ $slug }}" {{ old('writing_category') === $slug ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500">
                        Group writing tests by module. Edit <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">config/writing_categories.php</code> to add or rename tabs.
                    </p>
                </div>

                <div>
                    <label for="time_limit_minutes" class="block text-sm font-medium text-gray-700 mb-2">
                        Custom Time Limit (minutes) <span class="text-gray-400 text-xs">(Optional)</span>
                    </label>
                    <input type="number"
                           id="time_limit_minutes"
                           name="time_limit_minutes"
                           value="{{ old('time_limit_minutes') }}"
                           min="5"
                           max="120"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                           placeholder="Leave empty for default (Task 1: 20 min, Task 2: 40 min)">
                    <p class="mt-2 text-sm text-gray-500">
                        Override the default time limit. Leave blank to use task-type default.
                    </p>
                </div>
            </div>

            <!-- Avatar Teacher Selection (Speaking Only) -->
            @if(isset($avatarTeachers) && $avatarTeachers->count() > 0)
            <div id="avatar-teacher-section" class="hidden">
                <label for="avatar_teacher_id" class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center">
                        <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Default Avatar Teacher
                    </span>
                </label>
                <select id="avatar_teacher_id"
                        name="avatar_teacher_id"
                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 sm:text-sm">
                    <option value="">No Avatar (Text Only)</option>
                    @foreach($avatarTeachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('avatar_teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }} — {{ $teacher->voice_name }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500">
                    <svg class="inline-block mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    All questions in this test set will use this avatar teacher for AI video generation
                </p>
            </div>
            @endif

            <!-- Status Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Active Status -->
                <div class="rounded-lg border-2 border-gray-200 p-4 hover:border-green-300 transition-colors">
                    <label class="flex items-start cursor-pointer">
                        <div class="flex h-5 items-center">
                            <input type="checkbox"
                                   id="active"
                                   name="active"
                                   value="1"
                                   {{ old('active', true) ? 'checked' : '' }}
                                   class="h-4 w-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                        </div>
                        <div class="ml-3">
                            <span class="flex items-center text-sm font-medium text-gray-700">
                                <svg class="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Active Status
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                Make this test set active and immediately available
                            </p>
                        </div>
                    </label>
                </div>

            </div>

            <!-- Student Type Visibility -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Student Visibility <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-3">Keep this enabled so the test is available to your branch/offline students.</p>

                <div class="grid grid-cols-1 gap-4">
                    <!-- For Offline/Branch Students -->
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
                                <p class="text-xs text-gray-500 mt-1">
                                    Students enrolled in physical branches
                                </p>
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

            <!-- Info Alert -->
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex">
                    <svg class="h-5 w-5 text-blue-600 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-blue-900">What's Next?</h4>
                        <p class="mt-1 text-sm text-blue-700">
                            After creating the test set, you'll be able to add questions to it. Each test set should contain questions specific to the selected IELTS section.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('admin.test-sets.index') }}"
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
                    Create Test Set
                </button>
            </div>
        </form>
    </div>

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
        const sectionSelect = document.getElementById('section_id');
        const sectionInfo = document.getElementById('section-info');
        const sectionName = document.getElementById('section-name');
        const sectionTime = document.getElementById('section-time');
        const avatarSection = document.getElementById('avatar-teacher-section');

        const writingSection = document.getElementById('writing-task-section');

        sectionSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];

            if (this.value) {
                const name = selectedOption.text.split(' - ')[0];
                const sectionNameData = selectedOption.getAttribute('data-name') || '';
                const time = selectedOption.getAttribute('data-time');

                sectionName.textContent = name;
                sectionTime.textContent = `(${time} minutes)`;
                sectionInfo.classList.remove('hidden');

                // Show avatar teacher selection for Speaking section
                if (avatarSection && sectionNameData.toLowerCase() === 'speaking') {
                    avatarSection.classList.remove('hidden');
                } else if (avatarSection) {
                    avatarSection.classList.add('hidden');
                }

                // Show writing task type for Writing section
                if (writingSection && sectionNameData.toLowerCase() === 'writing') {
                    writingSection.classList.remove('hidden');
                } else if (writingSection) {
                    writingSection.classList.add('hidden');
                }
            } else {
                sectionInfo.classList.add('hidden');
                if (avatarSection) {
                    avatarSection.classList.add('hidden');
                }
                if (writingSection) {
                    writingSection.classList.add('hidden');
                }
            }
        });

        // Trigger on page load if section is pre-selected
        if (sectionSelect.value) {
            sectionSelect.dispatchEvent(new Event('change'));
        }

        // Initial visibility validation
        validateVisibility();

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const offlineChecked = document.getElementById('is_for_offline').checked;

            if (!offlineChecked) {
                e.preventDefault();
                validateVisibility();
                document.getElementById('visibility-error').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
    </script>
</x-admin-layout>
