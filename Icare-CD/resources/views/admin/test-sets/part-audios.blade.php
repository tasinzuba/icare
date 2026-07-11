<x-layout>
    <x-slot:title>Manage Part Audios - {{ $testSet->title }}</x-slot>
    
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-semibold">Manage Part Audios</h1>
                        <p class="text-purple-100 text-sm mt-1">{{ $testSet->title }}</p>
                    </div>
                    <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-medium rounded-md hover:bg-white/20 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        Back to Test Set
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">How Part Audios Work</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Upload one audio file per part (Part 1-4) OR use full audio for all parts</li>
                            <li>All questions in a part will automatically use that part's audio</li>
                            <li>No need to upload audio for individual questions anymore!</li>
                            <li>You can update or replace part audios anytime</li>
                            <li>Files are automatically synced to R2 storage for better performance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Audio Upload Option -->
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 border-2 border-purple-200 rounded-lg p-6 mb-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <svg class="h-6 w-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                        </svg>
                        <h3 class="text-lg font-semibold text-purple-900">Full Audio Upload</h3>
                    </div>
                    <p class="text-sm text-purple-700 mb-3">
                        Upload one complete audio file and use it for all parts. Perfect for listening tests with continuous audio.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-purple-100 text-purple-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            R2 Storage
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Auto-sync
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            Fast Delivery
                        </span>
                    </div>
                </div>
                <button onclick="uploadFullAudio()" 
                        class="ml-4 inline-flex items-center px-5 py-3 border-2 border-purple-600 text-purple-600 font-semibold rounded-lg hover:bg-purple-600 hover:text-white transition-all duration-200 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Full Audio
                </button>
            </div>
            
            <!-- Full Audio Status -->
            @php
                $fullAudio = $testSet->partAudios()->where('part_number', 0)->first();
            @endphp
            
            @if($fullAudio)
                <div class="mt-4 bg-white rounded-lg p-4 border border-purple-200">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="font-medium text-gray-900">Full Audio Uploaded</span>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ strtoupper($fullAudio->storage_disk) }}
                        </span>
                    </div>
                    
                    <!-- Warning Message -->
                    <div class="mb-3 bg-amber-50 border border-amber-200 rounded-lg p-3">
                        <div class="flex">
                            <svg class="h-5 w-5 text-amber-400 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-amber-800">Individual Part Audios Disabled</p>
                                <p class="text-xs text-amber-700 mt-1">
                                    All 4 parts are now using this full audio. Individual part audio uploads are disabled until you delete the full audio.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <audio controls class="w-full mb-3">
                        <source src="{{ $fullAudio->audio_url }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                    
                    <div class="flex justify-between text-sm text-gray-600 mb-3">
                        <span>Duration: {{ $fullAudio->formatted_duration }}</span>
                        <span>Size: {{ $fullAudio->formatted_size }}</span>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="replaceFullAudio()" 
                                class="flex-1 px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                            Replace
                        </button>
                        <button onclick="deleteFullAudio({{ $testSet->id }})" 
                                class="px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-medium">
                            Delete
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- Part Audio Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="part-audio-grid">
            @for($part = 1; $part <= 4; $part++)
                @php
                    $partAudio = $partAudios[$part] ?? null;
                    $questionCount = $testSet->questions()->where('part_number', $part)->count();
                    $fullAudio = $partAudios[0] ?? null;
                @endphp
                
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 part-audio-card {{ $fullAudio ? 'opacity-50 pointer-events-none' : '' }}" data-part="{{ $part }}">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-medium text-gray-900">Part {{ $part }}</h3>
                            <div class="flex items-center space-x-2">
                                @if($fullAudio)
                                    <span class="text-xs px-2 py-1 bg-purple-100 text-purple-700 rounded-full font-medium">
                                        Using Full Audio
                                    </span>
                                @endif
                                <span class="text-sm text-gray-500">{{ $questionCount }} questions</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if($fullAudio)
                            <!-- Full Audio is Active - Show Notice -->
                            <div class="text-center py-8">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100 mb-4">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">Full Audio Active</h3>
                                <p class="text-sm text-gray-500 mb-4">
                                    Part {{ $part }} is using the full audio.<br>
                                    Delete full audio to upload individual part audio.
                                </p>
                            </div>
                        @elseif($partAudio)
                            <!-- Audio exists -->
                            <div class="space-y-4">
                                <!-- Audio Player -->
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <audio controls class="w-full mb-2">
                                        <source src="{{ Storage::url($partAudio->audio_path) }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                    <div class="flex justify-between text-sm text-gray-600">
                                        <span>Duration: {{ $partAudio->formatted_duration }}</span>
                                        <span>Size: {{ $partAudio->formatted_size }}</span>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex space-x-3">
                                    <button onclick="replaceAudio({{ $part }})" 
                                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-sm font-medium">
                                        Replace Audio
                                    </button>
                                    <button onclick="deleteAudio({{ $testSet->id }}, {{ $part }})" 
                                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors text-sm font-medium">
                                        Delete
                                    </button>
                                </div>
                                
                                <!-- Transcript -->
                                @if($partAudio->transcript)
                                    <div class="mt-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-1">Transcript:</h4>
                                        <div class="bg-gray-50 rounded p-3 text-sm text-gray-600 max-h-32 overflow-y-auto">
                                            {{ Str::limit($partAudio->transcript, 200) }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- No audio -->
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No audio uploaded</h3>
                                <p class="mt-1 text-sm text-gray-500">Upload an audio file for Part {{ $part }}</p>
                                <div class="mt-6">
                                    <button onclick="uploadAudio({{ $part }})" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                        Upload Audio
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Upload Audio for Part <span id="upload-part-number"></span></h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="upload-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="part-number-input" name="part_number">
                
                <div class="space-y-4">
                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Audio File</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="audio-file" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-purple-500">
                                        <span>Upload a file</span>
                                        <input id="audio-file" name="audio" type="file" class="sr-only" accept=".mp3,.wav,.ogg,.webm,audio/*" required>
                                    </label>
                                    <p class="pl-1">or drag and drop</p>
                                </div>
                                <p class="text-xs text-gray-500">MP3, WAV, OGG, WEBM up to 50MB</p>
                            </div>
                        </div>
                        <div id="file-info" class="mt-2 text-sm text-gray-600"></div>
                    </div>
                    
                    <!-- Transcript -->
                    <div>
                        <label for="transcript" class="block text-sm font-medium text-gray-700 mb-2">Transcript (Optional)</label>
                        <textarea id="transcript" name="transcript" rows="4" 
                                  class="shadow-sm focus:ring-purple-500 focus:border-purple-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                                  placeholder="Enter the audio transcript..."></textarea>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div id="upload-progress" class="hidden">
                        <div class="flex justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">Uploading...</span>
                            <span class="text-sm font-medium text-gray-700" id="progress-percent">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="progress-bar" class="bg-purple-600 h-2.5 rounded-full" style="width: 0%"></div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                    <button type="submit" id="upload-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-purple-600 text-base font-medium text-white hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:col-start-2 sm:text-sm">
                        Upload
                    </button>
                    <button type="button" onclick="closeUploadModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('styles')
    <style>
        /* Disabled state for part audio cards when full audio is active */
        .part-audio-card.opacity-50 {
            position: relative;
        }
        
        .part-audio-card.opacity-50::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(139, 92, 246, 0.03) 0%, rgba(124, 58, 237, 0.03) 100%);
            pointer-events: none;
            border-radius: 0.5rem;
        }
        
        /* Smooth transitions */
        .part-audio-card {
            transition: all 0.3s ease-in-out;
        }
        
        /* Upload button hover effects */
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed !important;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        let currentPart = null;
        let isFullAudio = false;
        
        // Check and toggle part audio sections based on full audio
        function togglePartAudioSections(hasFullAudio) {
            const partAudioCards = document.querySelectorAll('.part-audio-card');
            
            partAudioCards.forEach(card => {
                if (hasFullAudio) {
                    card.classList.add('opacity-50', 'pointer-events-none', 'select-none');
                    card.style.cursor = 'not-allowed';
                } else {
                    card.classList.remove('opacity-50', 'pointer-events-none', 'select-none');
                    card.style.cursor = 'default';
                }
            });
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            const hasFullAudio = {{ $fullAudio ? 'true' : 'false' }};
            togglePartAudioSections(hasFullAudio);
        });
        
        function uploadFullAudio() {
            isFullAudio = true;
            currentPart = 0; // 0 means full audio
            document.getElementById('upload-part-number').textContent = 'ALL (Full Audio)';
            document.getElementById('part-number-input').value = 0;
            document.getElementById('upload-modal').classList.remove('hidden');
        }
        
        function replaceFullAudio() {
            uploadFullAudio();
        }
        
        function deleteFullAudio(testSetId) {
            if (!confirm('Are you sure you want to delete the full audio? After deletion, you can upload individual part audios.')) {
                return;
            }
            
            fetch(`/admin/test-sets/${testSetId}/part-audios/0`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message and reload
                    alert('Full audio deleted successfully. You can now upload individual part audios.');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete full audio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the full audio');
            });
        }
        
        function uploadAudio(partNumber) {
            // Check if full audio exists
            const hasFullAudio = {{ $fullAudio ? 'true' : 'false' }};
            if (hasFullAudio) {
                alert('Full audio is active. Please delete it first to upload individual part audios.');
                return;
            }
            
            isFullAudio = false;
            currentPart = partNumber;
            document.getElementById('upload-part-number').textContent = partNumber;
            document.getElementById('part-number-input').value = partNumber;
            document.getElementById('upload-modal').classList.remove('hidden');
        }
        
        function replaceAudio(partNumber) {
            uploadAudio(partNumber);
        }
        
        function closeUploadModal() {
            document.getElementById('upload-modal').classList.add('hidden');
            document.getElementById('upload-form').reset();
            document.getElementById('file-info').textContent = '';
            document.getElementById('upload-progress').classList.add('hidden');
            isFullAudio = false;
            currentPart = null;
        }
        
        function deleteAudio(testSetId, partNumber) {
            if (!confirm('Are you sure you want to delete this audio? This cannot be undone.')) {
                return;
            }
            
            fetch(`/admin/test-sets/${testSetId}/part-audios/${partNumber}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to delete audio');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the audio');
            });
        }
        
        // File input change handler
        document.getElementById('audio-file').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const fileInfo = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                document.getElementById('file-info').textContent = fileInfo;
            }
        });
        
        // Form submit handler
        document.getElementById('upload-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const uploadBtn = document.getElementById('upload-btn');
            const progressDiv = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');
            
            // Disable button and show progress
            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading...';
            progressDiv.classList.remove('hidden');
            
            // Create XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percentComplete + '%';
                    progressPercent.textContent = percentComplete + '%';
                }
            });
            
            xhr.addEventListener('load', function() {
                try {
                    if (xhr.status === 200) {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            location.reload();
                        } else {
                            alert(response.message || 'Failed to upload audio');
                            resetUploadForm();
                        }
                    } else {
                        // Try to parse error response
                        let errorMessage = 'Failed to upload audio (Status: ' + xhr.status + ')';
                        try {
                            const errorResponse = JSON.parse(xhr.responseText);
                            // Handle Laravel validation errors (422)
                            if (errorResponse.errors) {
                                const errors = Object.values(errorResponse.errors).flat();
                                errorMessage = 'Validation Error:\n' + errors.join('\n');
                            } else if (errorResponse.message) {
                                errorMessage = errorResponse.message;
                            }
                            console.error('Server error response:', errorResponse);
                        } catch (e) {
                            // If response is not JSON, show status
                            console.error('Server response:', xhr.responseText);
                        }
                        alert(errorMessage);
                        resetUploadForm();
                    }
                } catch (e) {
                    console.error('Error parsing response:', e);
                    console.error('Raw response:', xhr.responseText);
                    alert('Unexpected server response. Check console for details.');
                    resetUploadForm();
                }
            });
            
            xhr.addEventListener('error', function() {
                alert('An error occurred while uploading the audio. Please check your connection.');
                resetUploadForm();
            });

            xhr.addEventListener('timeout', function() {
                alert('Upload timed out. The file may be too large or the server is busy. Please try again.');
                resetUploadForm();
            });

            // Set timeout to 5 minutes for large audio files
            xhr.timeout = 300000;

            xhr.open('POST', `/admin/test-sets/{{ $testSet->id }}/part-audios`);
            xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.send(formData);
        });
        
        function resetUploadForm() {
            const uploadBtn = document.getElementById('upload-btn');
            const progressDiv = document.getElementById('upload-progress');
            const progressBar = document.getElementById('progress-bar');
            const progressPercent = document.getElementById('progress-percent');
            
            uploadBtn.disabled = false;
            uploadBtn.textContent = 'Upload';
            progressDiv.classList.add('hidden');
            progressBar.style.width = '0%';
            progressPercent.textContent = '0%';
        }
        
        // Drag and drop support
        const dropZone = document.querySelector('.border-dashed');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight(e) {
            dropZone.classList.add('border-purple-400', 'bg-purple-50');
        }
        
        function unhighlight(e) {
            dropZone.classList.remove('border-purple-400', 'bg-purple-50');
        }
        
        dropZone.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if (files.length > 0) {
                document.getElementById('audio-file').files = files;
                const event = new Event('change', { bubbles: true });
                document.getElementById('audio-file').dispatchEvent(event);
            }
        }
    </script>
    @endpush
</x-layout>