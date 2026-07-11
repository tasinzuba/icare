<x-admin-layout>
    <x-slot:title>Edit Avatar Teacher</x-slot>

    <!-- Page Header with Gradient -->
    <div class="mb-8">
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 p-8 shadow-xl">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative flex items-center justify-between">
                <div class="flex items-center">
                    <img src="{{ $avatarTeacher->photo_url }}"
                         alt="{{ $avatarTeacher->name }}"
                         class="w-16 h-16 rounded-full object-cover border-4 border-white/30 mr-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white">Edit {{ $avatarTeacher->name }}</h1>
                        <p class="mt-1 text-indigo-100">Update avatar teacher settings</p>
                    </div>
                </div>
                <a href="{{ route('admin.avatar-teachers.index') }}"
                   class="inline-flex items-center rounded-lg border-2 border-white/30 bg-white/10 backdrop-blur-sm px-4 py-2 text-sm font-medium text-white hover:bg-white/20 transition-all">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Teachers
                </a>
            </div>
        </div>
    </div>

    <!-- Form Container -->
    <form action="{{ route('admin.avatar-teachers.update', $avatarTeacher) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Basic Information Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-indigo-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Teacher Information</h3>
                        <p class="text-sm text-gray-600">Basic details about the avatar teacher</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                        Teacher Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="name"
                           id="name"
                           value="{{ old('name', $avatarTeacher->name) }}"
                           required
                           class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 placeholder-gray-400 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('name') border-red-500 @enderror"
                           placeholder="e.g., Ms. Sarah, Mr. James">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Photo & Upload -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Teacher Photo
                    </label>

                    <!-- Current Photo -->
                    <div class="mb-4 flex items-center gap-4">
                        <img src="{{ $avatarTeacher->photo_url }}"
                             alt="{{ $avatarTeacher->name }}"
                             class="w-24 h-24 rounded-xl object-cover border-2 border-gray-200"
                             id="current-photo">
                        <div>
                            <p class="text-sm font-medium text-gray-900">Current Photo</p>
                            <p class="text-xs text-gray-500">Upload a new photo to replace</p>
                        </div>
                    </div>

                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-indigo-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <div id="photo-preview" class="hidden mb-4">
                                <img src="" alt="Preview" class="mx-auto h-32 w-32 object-cover rounded-full">
                            </div>
                            <svg id="photo-icon" class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 justify-center">
                                <label for="photo" class="relative cursor-pointer rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                    <span>Upload a new photo</span>
                                    <input id="photo" name="photo" type="file" class="sr-only" accept="image/jpeg,image/png,image/jpg">
                                </label>
                            </div>
                            <p class="text-xs text-gray-500">PNG, JPG up to 5MB (optional)</p>
                        </div>
                    </div>
                    @error('photo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gender & Accent -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="gender" class="block text-sm font-semibold text-gray-700 mb-2">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select name="gender"
                                id="gender"
                                required
                                class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('gender') border-red-500 @enderror">
                            <option value="female" {{ old('gender', $avatarTeacher->gender) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="male" {{ old('gender', $avatarTeacher->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        </select>
                        @error('gender')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="accent" class="block text-sm font-semibold text-gray-700 mb-2">
                            Accent <span class="text-red-500">*</span>
                        </label>
                        <select name="accent"
                                id="accent"
                                required
                                class="block w-full rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 @error('accent') border-red-500 @enderror">
                            <option value="british" {{ old('accent', $avatarTeacher->accent) === 'british' ? 'selected' : '' }}>British</option>
                            <option value="american" {{ old('accent', $avatarTeacher->accent) === 'american' ? 'selected' : '' }}>American</option>
                            <option value="australian" {{ old('accent', $avatarTeacher->accent) === 'australian' ? 'selected' : '' }}>Australian</option>
                            <option value="neutral" {{ old('accent', $avatarTeacher->accent) === 'neutral' ? 'selected' : '' }}>Neutral</option>
                        </select>
                        @error('accent')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Voice Settings Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-purple-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Voice Settings</h3>
                        <p class="text-sm text-gray-600">ElevenLabs voice configuration</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Current Voice Info -->
                <div class="bg-purple-50 rounded-xl p-4 border border-purple-100">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-purple-900">Current Voice</p>
                            <p class="text-xs text-purple-700">{{ $avatarTeacher->voice_name ?? $avatarTeacher->elevenlabs_voice_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Voice Selection -->
                <div>
                    <label for="elevenlabs_voice_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        ElevenLabs Voice <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3">
                        <select name="elevenlabs_voice_id"
                                id="elevenlabs_voice_id"
                                required
                                class="flex-1 rounded-xl border-2 border-gray-200 bg-gray-50 px-4 py-3 text-gray-900 transition-all focus:border-purple-500 focus:bg-white focus:ring-4 focus:ring-purple-500/10 @error('elevenlabs_voice_id') border-red-500 @enderror">
                            <option value="">Select a Voice</option>

                            @if(!empty($groupedVoices['custom']))
                                <optgroup label="Your Custom Voices">
                                    @foreach($groupedVoices['custom'] as $voice)
                                        <option value="{{ $voice['voice_id'] }}"
                                                data-name="{{ $voice['name'] }}"
                                                data-preview="{{ $voice['preview_url'] ?? '' }}"
                                                {{ old('elevenlabs_voice_id', $avatarTeacher->elevenlabs_voice_id) === $voice['voice_id'] ? 'selected' : '' }}>
                                            {{ $voice['name'] }} ({{ ucfirst($voice['category']) }})
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if(!empty($groupedVoices['recommended']))
                                <optgroup label="Recommended for IELTS">
                                    @foreach($groupedVoices['recommended'] as $voice)
                                        <option value="{{ $voice['voice_id'] }}"
                                                data-name="{{ $voice['name'] }}"
                                                data-preview="{{ $voice['preview_url'] ?? '' }}"
                                                {{ old('elevenlabs_voice_id', $avatarTeacher->elevenlabs_voice_id) === $voice['voice_id'] ? 'selected' : '' }}>
                                            {{ $voice['name'] }} - {{ $voice['accent'] }} {{ $voice['gender'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if(!empty($groupedVoices['other']))
                                <optgroup label="Other Voices">
                                    @foreach($groupedVoices['other'] as $voice)
                                        <option value="{{ $voice['voice_id'] }}"
                                                data-name="{{ $voice['name'] }}"
                                                data-preview="{{ $voice['preview_url'] ?? '' }}"
                                                {{ old('elevenlabs_voice_id', $avatarTeacher->elevenlabs_voice_id) === $voice['voice_id'] ? 'selected' : '' }}>
                                            {{ $voice['name'] }} - {{ $voice['accent'] }} {{ $voice['gender'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endif

                            @if(empty($groupedVoices['custom']) && empty($groupedVoices['recommended']) && empty($groupedVoices['other']))
                                <option value="" disabled>No voices found - check API key</option>
                            @endif
                        </select>
                        <button type="button"
                                id="preview-voice-btn"
                                class="inline-flex items-center px-4 py-3 bg-purple-50 text-purple-700 rounded-xl hover:bg-purple-100 transition-colors disabled:opacity-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Preview
                        </button>
                    </div>
                    <input type="hidden" name="voice_name" id="voice_name" value="{{ old('voice_name', $avatarTeacher->voice_name) }}">
                    @error('elevenlabs_voice_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">
                        Custom voices appear first. Click Preview to hear a sample.
                    </p>
                </div>

                <!-- Audio Player (Hidden by default) -->
                <div id="audio-player-container" class="hidden">
                    <div class="bg-purple-50 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-purple-900" id="playing-voice-name">Playing voice preview...</p>
                                <audio id="voice-preview-audio" controls class="w-full mt-2"></audio>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-green-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Settings</h3>
                        <p class="text-sm text-gray-600">Status and configuration options</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Active Status -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">Active Status</h4>
                        <p class="text-sm text-gray-500">Inactive teachers won't be used for new avatar generations</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               name="is_active"
                               value="1"
                               {{ old('is_active', $avatarTeacher->is_active) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>

                <div class="border-t border-gray-100 pt-6"></div>

                <!-- Default Teacher -->
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900">Default Teacher</h4>
                        <p class="text-sm text-gray-500">This teacher will be used when no specific teacher is assigned</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                               name="is_default"
                               value="1"
                               {{ old('is_default', $avatarTeacher->is_default) ? 'checked' : '' }}
                               class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                    </label>
                </div>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="overflow-hidden rounded-2xl bg-white shadow-lg border border-gray-100">
            <div class="bg-gradient-to-r from-gray-50 to-slate-50 px-6 py-5 border-b border-gray-100">
                <div class="flex items-center">
                    <div class="flex-shrink-0 rounded-xl bg-gray-600 p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-bold text-gray-900">Statistics</h3>
                        <p class="text-sm text-gray-600">Avatar generation stats for this teacher</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-gray-900">{{ $avatarTeacher->total_questions ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Total Questions</p>
                    </div>
                    <div class="bg-green-50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-green-600">{{ $avatarTeacher->ready_avatars ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Ready</p>
                    </div>
                    <div class="bg-yellow-50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-yellow-600">{{ $avatarTeacher->pending_avatars ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Pending</p>
                    </div>
                    <div class="bg-red-50 rounded-xl p-4 text-center">
                        <p class="text-2xl font-bold text-red-600">{{ $avatarTeacher->failed_avatars ?? 0 }}</p>
                        <p class="text-sm text-gray-500">Failed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between">
            <form action="{{ route('admin.avatar-teachers.destroy', $avatarTeacher) }}"
                  method="POST"
                  onsubmit="return confirm('Are you sure you want to delete this teacher? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center rounded-xl border-2 border-red-300 bg-white px-6 py-3 text-sm font-semibold text-red-600 transition-all hover:bg-red-50 hover:border-red-400">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete Teacher
                </button>
            </form>

            <div class="flex items-center gap-4">
                <a href="{{ route('admin.avatar-teachers.index') }}"
                   class="inline-flex items-center rounded-xl border-2 border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 transition-all hover:bg-gray-50 hover:border-gray-400">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-3 text-sm font-bold text-white shadow-lg transition-all hover:from-indigo-700 hover:to-purple-700 hover:shadow-xl hover:scale-105">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </form>
</x-admin-layout>

@push('scripts')
<script>
    // Photo preview
    const photoInput = document.getElementById('photo');
    const photoPreview = document.getElementById('photo-preview');
    const photoIcon = document.getElementById('photo-icon');
    const currentPhoto = document.getElementById('current-photo');

    photoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                photoPreview.querySelector('img').src = e.target.result;
                photoPreview.classList.remove('hidden');
                photoIcon.classList.add('hidden');
                currentPhoto.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });

    // Voice selection
    const voiceSelect = document.getElementById('elevenlabs_voice_id');
    const voiceNameInput = document.getElementById('voice_name');
    const previewBtn = document.getElementById('preview-voice-btn');
    const audioContainer = document.getElementById('audio-player-container');
    const audioPlayer = document.getElementById('voice-preview-audio');
    const playingVoiceName = document.getElementById('playing-voice-name');

    voiceSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        voiceNameInput.value = selectedOption.dataset.name || '';
        previewBtn.disabled = !this.value;
        audioContainer.classList.add('hidden');
    });

    previewBtn.addEventListener('click', async function() {
        const voiceId = voiceSelect.value;
        if (!voiceId) return;

        const selectedOption = voiceSelect.options[voiceSelect.selectedIndex];
        const voiceName = selectedOption.dataset.name;

        previewBtn.disabled = true;
        previewBtn.innerHTML = `
            <svg class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Loading...
        `;

        try {
            const response = await fetch('{{ route("admin.avatar-teachers.preview-voice") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    voice_id: voiceId,
                    text: 'Hello, I am your IELTS speaking examiner. How are you today?'
                })
            });

            const data = await response.json();

            if (data.success) {
                const audioBlob = base64ToBlob(data.audio, 'audio/mpeg');
                const audioUrl = URL.createObjectURL(audioBlob);
                audioPlayer.src = audioUrl;
                playingVoiceName.textContent = `Playing: ${voiceName}`;
                audioContainer.classList.remove('hidden');
                audioPlayer.play();
            } else {
                alert('Failed to preview voice: ' + (data.error || 'Unknown error'));
            }
        } catch (error) {
            console.error('Preview error:', error);
            alert('Failed to preview voice. Please try again.');
        } finally {
            previewBtn.disabled = false;
            previewBtn.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Preview
            `;
        }
    });

    function base64ToBlob(base64, contentType) {
        const byteCharacters = atob(base64);
        const byteArrays = [];
        for (let offset = 0; offset < byteCharacters.length; offset += 512) {
            const slice = byteCharacters.slice(offset, offset + 512);
            const byteNumbers = new Array(slice.length);
            for (let i = 0; i < slice.length; i++) {
                byteNumbers[i] = slice.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            byteArrays.push(byteArray);
        }
        return new Blob(byteArrays, { type: contentType });
    }

    // Initialize voice name
    if (voiceSelect.value) {
        const selectedOption = voiceSelect.options[voiceSelect.selectedIndex];
        if (selectedOption.dataset.name) {
            voiceNameInput.value = selectedOption.dataset.name;
        }
    }
</script>
@endpush
