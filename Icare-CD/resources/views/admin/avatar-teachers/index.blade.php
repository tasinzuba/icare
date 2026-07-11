<x-admin-layout>
    <x-slot:title>Avatar Teachers</x-slot>

    <style>
        /* Smooth transitions */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        /* Progress bar animation */
        .progress-shine {
            position: relative;
            overflow: hidden;
        }
        .progress-shine::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shine 2s infinite;
        }
        @keyframes shine {
            100% { left: 100%; }
        }

        /* Status indicator */
        .status-pulse {
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Circular progress */
        .circular-progress {
            position: relative;
            width: 100px;
            height: 100px;
        }
        .circular-progress svg {
            transform: rotate(-90deg);
        }
        .circular-progress circle {
            fill: none;
            stroke-width: 8;
            stroke-linecap: round;
        }
        .circular-progress .bg { stroke: #e5e7eb; }
        .circular-progress .fg {
            stroke: #6366f1;
            stroke-dasharray: 251;
            stroke-dashoffset: 251;
            transition: stroke-dashoffset 0.5s ease;
        }
        .circular-progress .text {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
            color: #374151;
        }
    </style>

    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Avatar Teachers</h1>
                <p class="mt-1 text-sm text-gray-500">Manage AI avatar teachers for Speaking tests</p>
            </div>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('admin.avatar-teachers.create') }}"
                   class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Teacher
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Total</p>
            <p class="text-2xl font-bold text-gray-900">{{ $teachers->count() }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Ready</p>
            <p class="text-2xl font-bold text-green-600">{{ $teachers->sum('ready_avatars') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Pending</p>
            <p class="text-2xl font-bold text-amber-600">{{ $teachers->sum('pending_avatars') }}</p>
        </div>
        <div class="rounded-lg bg-white p-4 shadow-sm border border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase">Failed</p>
            <p class="text-2xl font-bold text-red-600">{{ $teachers->sum('failed_avatars') }}</p>
        </div>
    </div>

    <!-- Teachers Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($teachers as $teacher)
            <div class="card-hover rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden {{ !$teacher->is_active ? 'opacity-60' : '' }}">
                <!-- Photo -->
                <div class="relative h-44">
                    <img src="{{ $teacher->photo_url }}"
                         alt="{{ $teacher->name }}"
                         class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                    <!-- Badges -->
                    <div class="absolute top-3 left-3 flex gap-2">
                        @if($teacher->is_default)
                            <span class="px-2 py-0.5 rounded text-xs font-medium bg-amber-500 text-white">Default</span>
                        @endif
                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $teacher->is_active ? 'bg-green-500' : 'bg-gray-500' }} text-white">
                            {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-white/90 text-gray-700">
                            {{ ucfirst($teacher->gender) }} - {{ ucfirst($teacher->accent) }}
                        </span>
                    </div>

                    <!-- Name -->
                    <div class="absolute bottom-3 left-3">
                        <h3 class="text-lg font-semibold text-white">{{ $teacher->name }}</h3>
                        <p class="text-xs text-white/80">{{ $teacher->voice_name ?? 'ElevenLabs' }}</p>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4">
                    <!-- Stats -->
                    <div class="grid grid-cols-4 gap-2 text-center text-sm">
                        <div class="py-2 rounded bg-gray-50">
                            <p class="font-bold text-gray-700">{{ $teacher->total_questions }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                        <div class="py-2 rounded bg-green-50">
                            <p class="font-bold text-green-600">{{ $teacher->ready_avatars }}</p>
                            <p class="text-xs text-gray-500">Ready</p>
                        </div>
                        <div class="py-2 rounded bg-amber-50">
                            <p class="font-bold text-amber-600">{{ $teacher->pending_avatars }}</p>
                            <p class="text-xs text-gray-500">Pending</p>
                        </div>
                        <div class="py-2 rounded bg-red-50">
                            <p class="font-bold text-red-600">{{ $teacher->failed_avatars }}</p>
                            <p class="text-xs text-gray-500">Failed</p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 flex gap-2">
                        @if($teacher->pending_avatars > 0 || $teacher->failed_avatars > 0)
                            <button type="button"
                                    onclick="startGeneration({{ $teacher->id }}, '{{ $teacher->name }}', {{ $teacher->pending_avatars + $teacher->failed_avatars }})"
                                    class="flex-1 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors">
                                Generate {{ $teacher->pending_avatars + $teacher->failed_avatars }} Avatars
                            </button>
                        @endif
                        @if($teacher->total_questions > 0)
                            <button type="button"
                                    onclick="showProgress({{ $teacher->id }}, '{{ $teacher->name }}')"
                                    class="px-4 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Progress
                            </button>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="mt-4 flex items-center justify-between pt-3 border-t border-gray-100">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.avatar-teachers.edit', $teacher) }}"
                               class="px-3 py-1.5 text-xs font-medium text-indigo-600 bg-indigo-50 rounded hover:bg-indigo-100">
                                Edit
                            </a>
                            @if(!$teacher->is_default)
                                <form action="{{ route('admin.avatar-teachers.set-default', $teacher) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-medium text-amber-600 bg-amber-50 rounded hover:bg-amber-100">
                                        Set Default
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="flex gap-1">
                            <form action="{{ route('admin.avatar-teachers.toggle-active', $teacher) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="p-1.5 rounded hover:bg-gray-100 {{ $teacher->is_active ? 'text-amber-500' : 'text-green-500' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($teacher->is_active)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664zM21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                            <form action="{{ route('admin.avatar-teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Delete this teacher?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-red-500 rounded hover:bg-red-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="rounded-xl bg-white p-10 text-center shadow-sm border border-gray-100">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-4 text-base font-semibold text-gray-900">No Avatar Teachers</h3>
                    <p class="mt-1 text-sm text-gray-500">Create your first AI avatar teacher.</p>
                    <a href="{{ route('admin.avatar-teachers.create') }}"
                       class="mt-4 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        Add Teacher
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Confirm Generation Modal -->
    <div id="confirmModal" class="fixed inset-0 z-50 hidden">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="closeConfirmModal()"></div>

            <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl transform transition-all">
                <!-- Icon -->
                <div class="pt-8 pb-4 text-center">
                    <div class="mx-auto w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 pb-6 text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Generate Avatar Videos?</h3>
                    <p class="text-gray-600 mb-1">
                        Teacher: <span id="confirmTeacherName" class="font-semibold text-indigo-600">--</span>
                    </p>
                    <p class="text-gray-600 mb-4">
                        <span id="confirmCount" class="text-2xl font-bold text-indigo-600">0</span> avatar videos will be generated
                    </p>

                    <!-- Info Box -->
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 text-left">
                        <div class="flex gap-3">
                            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-amber-800">
                                <p class="font-medium mb-1">This will use API credits:</p>
                                <ul class="list-disc list-inside text-amber-700 space-y-0.5">
                                    <li>ElevenLabs: ~1000 chars per question</li>
                                    <li>D-ID: 1 credit per video</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3">
                        <button onclick="closeConfirmModal()"
                                class="flex-1 px-4 py-3 text-sm font-medium text-gray-700 bg-gray-100 rounded-xl hover:bg-gray-200 transition-colors">
                            Cancel
                        </button>
                        <button onclick="confirmGeneration()"
                                class="flex-1 px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all shadow-lg shadow-indigo-500/30">
                            Yes, Generate
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Modal -->
    <div id="progressModal" class="fixed inset-0 z-50 hidden">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" onclick="closeProgressModal()"></div>

            <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl">
                <!-- Header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900" id="modalTeacherName">Generating Avatars</h3>
                        <p class="text-xs text-gray-500">Processing videos...</p>
                    </div>
                    <button onclick="closeProgressModal()" class="p-1 rounded hover:bg-gray-100">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-5">
                    <!-- Progress with Circle -->
                    <div class="flex items-center gap-6 mb-5">
                        <div class="circular-progress flex-shrink-0">
                            <svg width="100" height="100" viewBox="0 0 100 100">
                                <circle class="bg" cx="50" cy="50" r="40"/>
                                <circle class="fg" id="circularProgress" cx="50" cy="50" r="40"/>
                            </svg>
                            <div class="text" id="circularPercent">0%</div>
                        </div>

                        <div class="flex-1 grid grid-cols-2 gap-3">
                            <div class="text-center p-3 rounded-lg bg-gray-50">
                                <p class="text-xl font-bold text-gray-700" id="statTotal">0</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-green-50">
                                <p class="text-xl font-bold text-green-600" id="statReady">0</p>
                                <p class="text-xs text-gray-500">Ready</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-amber-50">
                                <p class="text-xl font-bold text-amber-600" id="statInProgress">0</p>
                                <p class="text-xs text-gray-500">Processing</p>
                            </div>
                            <div class="text-center p-3 rounded-lg bg-red-50">
                                <p class="text-xl font-bold text-red-600" id="statFailed">0</p>
                                <p class="text-xs text-gray-500">Failed</p>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mb-4">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-500">Progress</span>
                            <span class="font-medium text-gray-700" id="progressPercent">0%</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-100 overflow-hidden">
                            <div id="progressBar" class="progress-shine h-full rounded-full bg-indigo-500 transition-all duration-500" style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200 mb-3">
                        <button onclick="switchTab('questions')" id="tabQuestions" class="flex-1 py-2 text-sm font-medium text-indigo-600 border-b-2 border-indigo-600 transition-colors">
                            Questions Status
                        </button>
                        <button onclick="switchTab('activity')" id="tabActivity" class="flex-1 py-2 text-sm font-medium text-gray-500 border-b-2 border-transparent hover:text-gray-700 transition-colors">
                            Live Activity
                            <span id="activityBadge" class="ml-1 px-1.5 py-0.5 text-xs bg-green-100 text-green-700 rounded-full hidden">0</span>
                        </button>
                    </div>

                    <!-- Questions List Tab -->
                    <div id="questionsTab" class="max-h-48 overflow-y-auto rounded-lg border border-gray-200">
                        <div id="questionsList" class="divide-y divide-gray-100"></div>
                    </div>

                    <!-- Activity Log Tab -->
                    <div id="activityTab" class="max-h-48 overflow-y-auto rounded-lg border border-gray-200 hidden">
                        <div id="activityLog" class="divide-y divide-gray-100">
                            <div class="p-4 text-center text-gray-400 text-sm">Waiting for activity...</div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="flex items-center justify-between px-5 py-3 border-t border-gray-100 bg-gray-50">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center gap-2 text-xs text-gray-500">
                            <span class="relative flex h-2 w-2">
                                <span id="pulseIndicator" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                            </span>
                            <span id="autoRefreshStatus">Auto-refreshing every 2s</span>
                        </div>
                        <div class="text-xs text-gray-400 pl-4">
                            Last updated: <span id="lastUpdatedTime" class="font-mono">--:--:--</span>
                            <span id="updateFlash" class="ml-1 text-green-500 opacity-0 transition-opacity">Updated!</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="closeProgressModal()" class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-gray-800">
                            Close
                        </button>
                        <button onclick="manualRefresh()" id="refreshBtn" class="px-3 py-2 text-sm font-medium text-indigo-600 bg-indigo-50 rounded-lg hover:bg-indigo-100 flex items-center gap-1">
                            <svg id="refreshIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Refresh
                        </button>
                        <button onclick="closeProgressModal(true)" class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        let pollingInterval = null;
        let currentTeacherId = null;
        let currentTeacherName = null;
        let currentCount = 0;
        let isRefreshing = false;
        let lastUpdateTime = null;
        let activityLog = [];
        let previousStatuses = {};
        let currentTab = 'questions';

        // Show custom confirm modal
        function startGeneration(teacherId, teacherName, count) {
            currentTeacherId = teacherId;
            currentTeacherName = teacherName;
            currentCount = count;

            document.getElementById('confirmTeacherName').textContent = teacherName;
            document.getElementById('confirmCount').textContent = count;
            document.getElementById('confirmModal').classList.remove('hidden');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.add('hidden');
        }

        // Tab switching
        function switchTab(tab) {
            currentTab = tab;
            const tabQuestions = document.getElementById('tabQuestions');
            const tabActivity = document.getElementById('tabActivity');
            const questionsTab = document.getElementById('questionsTab');
            const activityTab = document.getElementById('activityTab');

            if (tab === 'questions') {
                tabQuestions.classList.add('text-indigo-600', 'border-indigo-600');
                tabQuestions.classList.remove('text-gray-500', 'border-transparent');
                tabActivity.classList.remove('text-indigo-600', 'border-indigo-600');
                tabActivity.classList.add('text-gray-500', 'border-transparent');
                questionsTab.classList.remove('hidden');
                activityTab.classList.add('hidden');
            } else {
                tabActivity.classList.add('text-indigo-600', 'border-indigo-600');
                tabActivity.classList.remove('text-gray-500', 'border-transparent');
                tabQuestions.classList.remove('text-indigo-600', 'border-indigo-600');
                tabQuestions.classList.add('text-gray-500', 'border-transparent');
                activityTab.classList.remove('hidden');
                questionsTab.classList.add('hidden');
                // Clear badge when viewing activity
                document.getElementById('activityBadge').classList.add('hidden');
            }
        }

        // Add activity to log
        function addActivity(type, message, questionId = null) {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour12: false });

            const activity = {
                time: timeStr,
                type: type,
                message: message,
                questionId: questionId
            };

            activityLog.unshift(activity);
            if (activityLog.length > 50) activityLog.pop(); // Keep last 50

            updateActivityLogUI();

            // Show badge if not on activity tab
            if (currentTab !== 'activity') {
                const badge = document.getElementById('activityBadge');
                const count = parseInt(badge.textContent || '0') + 1;
                badge.textContent = count;
                badge.classList.remove('hidden');
            }
        }

        function updateActivityLogUI() {
            const container = document.getElementById('activityLog');
            if (activityLog.length === 0) {
                container.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm">Waiting for activity...</div>';
                return;
            }

            container.innerHTML = activityLog.map(a => `
                <div class="flex items-start gap-3 px-3 py-2 text-sm ${getActivityBgClass(a.type)}">
                    <span class="text-gray-400 font-mono text-xs whitespace-nowrap">${a.time}</span>
                    <span class="flex-shrink-0">${getActivityIcon(a.type)}</span>
                    <span class="flex-1 text-gray-700">${a.message}</span>
                </div>
            `).join('');
        }

        function getActivityBgClass(type) {
            switch(type) {
                case 'success': return 'bg-green-50';
                case 'error': return 'bg-red-50';
                case 'processing': return 'bg-amber-50';
                case 'info': return 'bg-blue-50';
                default: return '';
            }
        }

        function getActivityIcon(type) {
            switch(type) {
                case 'success':
                    return '<svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
                case 'error':
                    return '<svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
                case 'processing':
                    return '<svg class="w-4 h-4 text-amber-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                case 'info':
                    return '<svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>';
                default:
                    return '<svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><circle cx="10" cy="10" r="3"/></svg>';
            }
        }

        // Show progress without starting generation
        function showProgress(teacherId, teacherName) {
            currentTeacherId = teacherId;
            currentTeacherName = teacherName;

            document.getElementById('modalTeacherName').textContent = teacherName;
            document.getElementById('progressModal').classList.remove('hidden');

            // Reset UI and activity log
            document.getElementById('questionsList').innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Loading progress...</div>';
            document.getElementById('lastUpdatedTime').textContent = '--:--:--';
            setAutoRefreshStatus('Fetching status...', true);
            activityLog = [];
            previousStatuses = {};
            updateActivityLogUI();
            document.getElementById('activityBadge').classList.add('hidden');
            switchTab('questions'); // Start on questions tab

            // Add initial activity
            addActivity('info', `Viewing progress for ${teacherName}`);

            // Start polling immediately
            pollProgress();
            startPolling();
        }

        // Called when user clicks "Yes, Generate"
        function confirmGeneration() {
            closeConfirmModal();

            // Show progress modal
            document.getElementById('modalTeacherName').textContent = currentTeacherName;
            document.getElementById('progressModal').classList.remove('hidden');

            // Reset UI and activity log
            updateCircularProgress(0);
            document.getElementById('progressBar').style.width = '0%';
            document.getElementById('progressPercent').textContent = '0%';
            document.getElementById('questionsList').innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Starting generation...</div>';
            document.getElementById('lastUpdatedTime').textContent = '--:--:--';
            setAutoRefreshStatus('Sending request...', true);
            activityLog = [];
            previousStatuses = {};
            updateActivityLogUI();
            document.getElementById('activityBadge').classList.add('hidden');
            switchTab('questions'); // Start on questions tab

            // Add initial activity
            addActivity('info', `Starting avatar generation for ${currentTeacherName}`);
            addActivity('info', `${currentCount} videos will be generated`);

            // Send generation request
            fetch(`/admin/avatar-teachers/${currentTeacherId}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(response => {
                if (response.ok) {
                    setAutoRefreshStatus('Jobs queued! Fetching progress...', true);
                    addActivity('success', 'Jobs successfully queued! Queue worker will process them.');
                } else {
                    addActivity('error', `Server returned error: ${response.status}`);
                }
                // Start polling after request sent
                setTimeout(() => {
                    pollProgress();
                    startPolling();
                }, 500);
            }).catch(err => {
                console.error('Generation request failed:', err);
                setAutoRefreshStatus('Request failed! Check console.', false);
                addActivity('error', `Request failed: ${err.message || 'Network error'}`);
            });
        }

        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            pollingInterval = setInterval(pollProgress, 2000);
            setAutoRefreshStatus('Auto-refreshing every 2s', true);
        }

        function stopPolling(message = 'Completed') {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            setAutoRefreshStatus(message, false);
        }

        function setAutoRefreshStatus(text, isActive) {
            const statusEl = document.getElementById('autoRefreshStatus');
            const pulseEl = document.getElementById('pulseIndicator');

            if (statusEl) statusEl.textContent = text;
            if (pulseEl) {
                if (isActive) {
                    pulseEl.classList.add('animate-ping');
                    pulseEl.classList.remove('opacity-0');
                } else {
                    pulseEl.classList.remove('animate-ping');
                    pulseEl.classList.add('opacity-0');
                }
            }
        }

        function pollProgress() {
            if (!currentTeacherId || isRefreshing) return;

            isRefreshing = true;
            const refreshIcon = document.getElementById('refreshIcon');
            if (refreshIcon) refreshIcon.classList.add('animate-spin');

            fetch(`/admin/avatar-teachers/${currentTeacherId}/progress`)
                .then(r => r.json())
                .then(data => {
                    updateProgressUI(data);
                    lastUpdateTime = new Date();
                    updateLastUpdatedTime();

                    if (data.stats.is_complete) {
                        stopPolling('All tasks completed!');
                    }
                })
                .catch(err => {
                    console.error('Polling error:', err);
                    setAutoRefreshStatus('Connection error, retrying...', true);
                    addActivity('error', `Connection error: ${err.message || 'Failed to fetch progress'}`);
                })
                .finally(() => {
                    isRefreshing = false;
                    if (refreshIcon) refreshIcon.classList.remove('animate-spin');
                });
        }

        function manualRefresh() {
            if (!currentTeacherId) return;

            // Force refresh even if already refreshing
            isRefreshing = false;
            pollProgress();

            // Restart polling if it was stopped
            if (!pollingInterval) {
                startPolling();
            }
        }

        function updateProgressUI(data) {
            const stats = data.stats;
            const pct = stats.percentage || 0;

            // Animate progress
            document.getElementById('progressPercent').textContent = pct + '%';
            document.getElementById('progressBar').style.width = pct + '%';
            updateCircularProgress(pct);

            // Update stats with animation effect
            animateNumber('statTotal', stats.total);
            animateNumber('statReady', stats.ready);
            animateNumber('statInProgress', stats.in_progress);
            animateNumber('statFailed', stats.failed);

            // Track status changes and log activity
            if (data.questions && data.questions.length > 0) {
                data.questions.forEach(q => {
                    const prevStatus = previousStatuses[q.id];
                    const newStatus = q.status;

                    // Only log if status changed and we have a previous status
                    if (prevStatus && prevStatus !== newStatus) {
                        const shortContent = (q.content || '').substring(0, 30);

                        if (newStatus === 'generating_audio') {
                            addActivity('processing', `Q#${q.id}: Started generating audio - "${shortContent}..."`, q.id);
                        } else if (newStatus === 'generating_video' && prevStatus === 'generating_audio') {
                            addActivity('processing', `Q#${q.id}: Audio done! Now generating video`, q.id);
                        } else if (newStatus === 'ready') {
                            addActivity('success', `Q#${q.id}: Avatar video ready!`, q.id);
                        } else if (newStatus === 'failed') {
                            const errorMsg = q.error ? ` - ${q.error.substring(0, 50)}` : '';
                            addActivity('error', `Q#${q.id}: Failed${errorMsg}`, q.id);
                        } else if (newStatus === 'pending' && prevStatus === 'none') {
                            addActivity('info', `Q#${q.id}: Added to queue`, q.id);
                        }
                    }

                    // Store current status for next comparison
                    previousStatuses[q.id] = newStatus;
                });
            }

            // Update questions list
            const questionsList = document.getElementById('questionsList');
            if (data.questions && data.questions.length > 0) {
                questionsList.innerHTML = data.questions.map(q => `
                    <div class="flex items-center justify-between px-3 py-2 text-sm transition-colors ${getRowClass(q.status)}">
                        <span class="truncate flex-1 text-gray-700">Q#${q.id}: ${(q.content || '').substring(0, 40)}...</span>
                        ${getStatusBadge(q.status)}
                    </div>
                `).join('');
            } else {
                questionsList.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No questions assigned to this teacher</div>';
            }

            // Update status message based on progress
            if (stats.is_complete) {
                if (stats.failed > 0) {
                    setAutoRefreshStatus(`Done with ${stats.failed} failed`, false);
                } else {
                    setAutoRefreshStatus('All avatars ready!', false);
                }
            } else if (stats.in_progress > 0) {
                setAutoRefreshStatus(`Processing ${stats.in_progress} avatar(s)...`, true);
            }
        }

        function animateNumber(elementId, newValue) {
            const el = document.getElementById(elementId);
            if (!el) return;

            const currentValue = parseInt(el.textContent) || 0;
            if (currentValue !== newValue) {
                el.textContent = newValue;
                el.classList.add('scale-110');
                setTimeout(() => el.classList.remove('scale-110'), 200);
            }
        }

        function updateLastUpdatedTime() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour12: false });
            const timeEl = document.getElementById('lastUpdatedTime');
            const flashEl = document.getElementById('updateFlash');

            if (timeEl) {
                timeEl.textContent = timeStr;
                // Highlight effect
                timeEl.classList.add('text-green-500', 'font-bold');
                setTimeout(() => {
                    timeEl.classList.remove('text-green-500', 'font-bold');
                }, 500);
            }

            if (flashEl) {
                flashEl.classList.remove('opacity-0');
                flashEl.classList.add('opacity-100');
                setTimeout(() => {
                    flashEl.classList.remove('opacity-100');
                    flashEl.classList.add('opacity-0');
                }, 1000);
            }
        }

        function updateCircularProgress(pct) {
            const circle = document.getElementById('circularProgress');
            const circumference = 2 * Math.PI * 40;
            circle.style.strokeDashoffset = circumference - (pct / 100) * circumference;
            document.getElementById('circularPercent').textContent = pct + '%';
        }

        function getRowClass(status) {
            if (['generating_audio', 'generating_video', 'pending'].includes(status)) return 'bg-amber-50';
            if (status === 'ready') return 'bg-green-50';
            if (status === 'failed') return 'bg-red-50';
            return '';
        }

        function getStatusBadge(status) {
            const badges = {
                'none': '<span class="px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-600">Waiting</span>',
                'pending': '<span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700">Queued</span>',
                'generating_audio': '<span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700 status-pulse">Audio...</span>',
                'generating_video': '<span class="px-2 py-0.5 text-xs rounded bg-amber-100 text-amber-700 status-pulse">Video...</span>',
                'ready': '<span class="px-2 py-0.5 text-xs rounded bg-green-100 text-green-700">Done</span>',
                'failed': '<span class="px-2 py-0.5 text-xs rounded bg-red-100 text-red-700">Failed</span>'
            };
            return badges[status] || badges['none'];
        }

        function closeProgressModal(reload = false) {
            document.getElementById('progressModal').classList.add('hidden');
            stopPolling('Closed');

            if (reload) {
                // Reload page to update stats on cards
                location.reload();
            }
        }

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                if (!document.getElementById('confirmModal').classList.contains('hidden')) {
                    closeConfirmModal();
                } else if (!document.getElementById('progressModal').classList.contains('hidden')) {
                    closeProgressModal();
                }
            }
        });
    </script>
    @endpush
</x-admin-layout>
