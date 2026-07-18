<x-layout>
    <x-slot:title>Edit Test Set - Admin</x-slot>
    
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Test Set') }}
            </h2>
            <a href="{{ route('admin.test-sets.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                Back to Test Sets
            </a>
        </div>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.test-sets.update', $testSet) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label for="title" class="block mb-2 text-sm font-medium text-gray-900">Test Set Title</label>
                            <input type="text" id="title" name="title" value="{{ old('title', $testSet->title) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                            @error('title')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label for="section_id" class="block mb-2 text-sm font-medium text-gray-900">Section</label>
                            <select id="section_id" name="section_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select a section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}"
                                            data-name="{{ $section->name }}"
                                            {{ old('section_id', $testSet->section_id) == $section->id ? 'selected' : '' }}>
                                        {{ ucfirst($section->name) }} ({{ $section->time_limit }} minutes)
                                    </option>
                                @endforeach
                            </select>
                            @error('section_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Writing Task Type (Writing Only) -->
                        <div id="writing-task-section" class="mb-6 {{ $testSet->section && $testSet->section->name === 'writing' ? '' : 'hidden' }}">
                            <div class="mb-4">
                                <label for="writing_task_type" class="block mb-2 text-sm font-medium text-gray-900">Writing Task Type</label>
                                <select id="writing_task_type"
                                        name="writing_task_type"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5">
                                    <option value="" {{ old('writing_task_type', $testSet->writing_task_type) === null ? 'selected' : '' }}>Combined (Task 1 + Task 2)</option>
                                    <option value="task1" {{ old('writing_task_type', $testSet->writing_task_type) === 'task1' ? 'selected' : '' }}>Task 1 Only (Report / Letter) — 20 min default</option>
                                    <option value="task2" {{ old('writing_task_type', $testSet->writing_task_type) === 'task2' ? 'selected' : '' }}>Task 2 Only (Essay) — 40 min default</option>
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Choose whether this test set has a single task or combined Task 1 + Task 2.</p>
                            </div>
                            <div class="mb-4">
                                <label for="writing_category" class="block mb-2 text-sm font-medium text-gray-900">Writing Module / Tab <span class="text-gray-400 text-xs">(Shown as sidebar tab on student dashboard)</span></label>
                                <select id="writing_category"
                                        name="writing_category"
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5">
                                    <option value="" {{ old('writing_category', $testSet->writing_category) === null ? 'selected' : '' }}>— Uncategorized —</option>
                                    @foreach(config('writing_categories') as $slug => $label)
                                        <option value="{{ $slug }}" {{ old('writing_category', $testSet->writing_category) === $slug ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <p class="mt-2 text-sm text-gray-500">Group writing tests by module. Edit <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">config/writing_categories.php</code> to add or rename tabs.</p>
                            </div>
                            <div>
                                <label for="time_limit_minutes" class="block mb-2 text-sm font-medium text-gray-900">Custom Time Limit (minutes) <span class="text-gray-400 text-xs">(Optional)</span></label>
                                <input type="number"
                                       id="time_limit_minutes"
                                       name="time_limit_minutes"
                                       value="{{ old('time_limit_minutes', $testSet->time_limit_minutes) }}"
                                       min="5"
                                       max="120"
                                       class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-500 focus:border-red-500 block w-full p-2.5"
                                       placeholder="Leave empty for default">
                                <p class="mt-2 text-sm text-gray-500">Override the default time limit. Leave blank to use task-type default.</p>
                            </div>
                        </div>

                        <!-- Reading Module (Reading Only) -->
                        <div id="reading-module-section" class="mb-6 {{ $testSet->section && $testSet->section->name === 'reading' ? '' : 'hidden' }}">
                            <label for="test_type" class="block mb-2 text-sm font-medium text-gray-900">Reading Module</label>
                            <select id="test_type"
                                    name="test_type"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-orange-500 focus:border-orange-500 block w-full p-2.5">
                                <option value="academic" {{ old('test_type', $testSet->test_type ?? 'academic') === 'academic' ? 'selected' : '' }}>Academic</option>
                                <option value="general" {{ old('test_type', $testSet->test_type) === 'general' ? 'selected' : '' }}>General Training</option>
                            </select>
                            <p class="mt-2 text-sm text-gray-500">Choose the IELTS Reading module. Academic and General Training convert the same number of correct answers to different band scores.</p>
                        </div>

                        <!-- Avatar Teacher Selection (Speaking Only) -->
                        @if(isset($avatarTeachers) && $avatarTeachers->count() > 0)
                        <div id="avatar-teacher-section" class="mb-6 {{ $testSet->section && $testSet->section->name === 'speaking' ? '' : 'hidden' }}">
                            <label for="avatar_teacher_id" class="block mb-2 text-sm font-medium text-gray-900">
                                <span class="flex items-center">
                                    <svg class="mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    Default Avatar Teacher
                                </span>
                            </label>
                            <select id="avatar_teacher_id"
                                    name="avatar_teacher_id"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 block w-full p-2.5">
                                <option value="">No Avatar (Text Only)</option>
                                @foreach($avatarTeachers as $teacher)
                                    <option value="{{ $teacher->id }}" {{ old('avatar_teacher_id', $testSet->avatar_teacher_id) == $teacher->id ? 'selected' : '' }}>
                                        {{ $teacher->name }} — {{ $teacher->voice_name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-500">All questions in this test set will use this avatar teacher for AI video generation</p>
                        </div>
                        @endif

                        <div class="mb-6 space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" id="active" name="active" value="1" {{ old('active', $testSet->active) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                <label for="active" class="ml-2 text-sm font-medium text-gray-900">Make this test set active and available to students</label>
                            </div>
                        </div>

                        <!-- Student Visibility Section -->
                        <div class="mb-6">
                            <label class="block mb-2 text-sm font-medium text-gray-900">Student Visibility <span class="text-red-500">*</span></label>
                            <p class="text-xs text-gray-500 mb-3">Keep this enabled so the test is available to your branch/offline students.</p>

                            <div class="grid grid-cols-1 gap-4">
                                <!-- For Branch/Offline Students -->
                                <div class="rounded-lg border-2 p-4 transition-colors {{ old('is_for_offline', $testSet->is_for_offline) ? 'border-orange-400 bg-orange-50' : 'border-gray-200 hover:border-orange-300' }}" id="offline-checkbox-container">
                                    <label class="flex items-start cursor-pointer">
                                        <div class="flex h-5 items-center">
                                            <input type="checkbox"
                                                   id="is_for_offline"
                                                   name="is_for_offline"
                                                   value="1"
                                                   {{ old('is_for_offline', $testSet->is_for_offline) ? 'checked' : '' }}
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
                        
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                            Update Test Set
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                offlineContainer.classList.remove('border-red-300', 'border-gray-200');
                offlineContainer.classList.add('border-orange-400', 'bg-orange-50');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Section change handler for avatar teacher & writing task type visibility
            const sectionSelect = document.getElementById('section_id');
            const avatarSection = document.getElementById('avatar-teacher-section');
            const writingSection = document.getElementById('writing-task-section');
            const readingSection = document.getElementById('reading-module-section');

            if (sectionSelect) {
                sectionSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const sectionName = selectedOption.getAttribute('data-name') || '';

                    if (avatarSection) {
                        if (sectionName.toLowerCase() === 'speaking') {
                            avatarSection.classList.remove('hidden');
                        } else {
                            avatarSection.classList.add('hidden');
                        }
                    }

                    if (writingSection) {
                        if (sectionName.toLowerCase() === 'writing') {
                            writingSection.classList.remove('hidden');
                        } else {
                            writingSection.classList.add('hidden');
                        }
                    }

                    if (readingSection) {
                        if (sectionName.toLowerCase() === 'reading') {
                            readingSection.classList.remove('hidden');
                        } else {
                            readingSection.classList.add('hidden');
                        }
                    }
                });
            }

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
    @endpush
</x-layout>