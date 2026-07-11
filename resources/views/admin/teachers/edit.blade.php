<x-admin-layout>
    <x-slot:title>Edit Teacher</x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Edit Teacher: {{ $teacher->user->name }}</h2>
            </div>
            
            <form action="{{ route('admin.teachers.update', $teacher) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')
                
                <!-- User Info (Read-only) -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-2">Teacher Information</h3>
                    <p class="text-sm text-gray-900">{{ $teacher->user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $teacher->user->email }}</p>
                </div>
                
                <!-- Specialization -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Specialization</label>
                    <div class="space-y-2">
                        @foreach(['writing', 'speaking', 'reading', 'listening'] as $spec)
                        <label class="flex items-center">
                            <input type="checkbox" name="specialization[]" value="{{ $spec }}" 
                                   {{ in_array($spec, old('specialization', $teacher->specialization ?? [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ ucfirst($spec) }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('specialization')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Experience and Pricing -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="experience_years" class="block text-sm font-medium text-gray-700">Experience (Years)</label>
                        <input type="number" name="experience_years" id="experience_years" 
                               value="{{ old('experience_years', $teacher->experience_years) }}" required min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('experience_years')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="evaluation_price_tokens" class="block text-sm font-medium text-gray-700">Price (Tokens)</label>
                        <input type="number" name="evaluation_price_tokens" id="evaluation_price_tokens" 
                               value="{{ old('evaluation_price_tokens', $teacher->evaluation_price_tokens) }}" required min="1"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('evaluation_price_tokens')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <!-- Qualifications -->
                <div>
                    <label for="qualifications" class="block text-sm font-medium text-gray-700">Qualifications</label>
                    <div id="qualifications-container" class="space-y-2">
                        @php
                            $qualifications = old('qualifications', $teacher->qualifications ?? ['']);
                        @endphp
                        @foreach($qualifications as $qualification)
                            <input type="text" name="qualifications[]" value="{{ $qualification }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="e.g., IELTS Examiner">
                        @endforeach
                    </div>
                    <button type="button" onclick="addQualification()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
                        + Add another qualification
                    </button>
                    @error('qualifications')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Languages -->
                <div>
                    <label for="languages" class="block text-sm font-medium text-gray-700">Languages</label>
                    <div id="languages-container" class="space-y-2">
                        @php
                            $languages = old('languages', $teacher->languages ?? ['English']);
                        @endphp
                        @foreach($languages as $language)
                            <input type="text" name="languages[]" value="{{ $language }}"
                                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="e.g., English">
                        @endforeach
                    </div>
                    <button type="button" onclick="addLanguage()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-500">
                        + Add another language
                    </button>
                    @error('languages')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Profile Description -->
                <div>
                    <label for="profile_description" class="block text-sm font-medium text-gray-700">Profile Description</label>
                    <textarea name="profile_description" id="profile_description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Brief description about the teacher's expertise and experience...">{{ old('profile_description', $teacher->profile_description) }}</textarea>
                    @error('profile_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Availability -->
                <div>
                    <label class="flex items-center">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" 
                               {{ old('is_available', $teacher->is_available) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Available for evaluations</span>
                    </label>
                </div>
                
                <!-- Statistics -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Teacher Statistics</h3>
                    <div class="grid grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500">Total Evaluations:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $teacher->total_evaluations_done }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Average Rating:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $teacher->rating }}/5</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Avg. Turnaround:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $teacher->average_turnaround_hours }}h</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.teachers.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Update Teacher
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    @push('scripts')
    <script>
        function addQualification() {
            const container = document.getElementById('qualifications-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'qualifications[]';
            input.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
            input.placeholder = 'e.g., IELTS Examiner';
            container.appendChild(input);
        }
        
        function addLanguage() {
            const container = document.getElementById('languages-container');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'languages[]';
            input.className = 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm';
            input.placeholder = 'e.g., English';
            container.appendChild(input);
        }
    </script>
    @endpush
</x-admin-layout>
