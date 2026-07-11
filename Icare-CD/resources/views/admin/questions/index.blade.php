<x-admin-layout>
    <x-slot:title>Questions Management</x-slot>
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Question Bank</h2>
                        <p class="mt-1 text-sm text-gray-600">Manage all IELTS questions across different test sets</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <button class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <i class="fas fa-file-import mr-2"></i>
                            Import Questions
                        </button>
                        <a href="{{ route('admin.questions.create') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            <i class="fas fa-plus mr-2"></i>
                            New Question
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            @php
                $stats = [
                    'total' => ['count' => \App\Models\Question::count(), 'icon' => 'fas fa-question-circle', 'color' => 'indigo', 'label' => 'Total Questions'],
                    'listening' => ['count' => \App\Models\Question::whereHas('testSet.section', fn($q) => $q->where('name', 'listening'))->count(), 'icon' => 'fas fa-headphones', 'color' => 'purple', 'label' => 'Listening'],
                    'reading' => ['count' => \App\Models\Question::whereHas('testSet.section', fn($q) => $q->where('name', 'reading'))->count(), 'icon' => 'fas fa-book-open', 'color' => 'green', 'label' => 'Reading'],
                    'writing' => ['count' => \App\Models\Question::whereHas('testSet.section', fn($q) => $q->where('name', 'writing'))->count(), 'icon' => 'fas fa-pen-fancy', 'color' => 'blue', 'label' => 'Writing'],
                    'speaking' => ['count' => \App\Models\Question::whereHas('testSet.section', fn($q) => $q->where('name', 'speaking'))->count(), 'icon' => 'fas fa-microphone', 'color' => 'pink', 'label' => 'Speaking'],
                ];
            @endphp
            
            @foreach($stats as $key => $stat)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-{{ $stat['color'] }}-100 rounded-lg">
                                <i class="{{ $stat['icon'] }} text-2xl text-{{ $stat['color'] }}-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    {{ $stat['label'] }}
                                </dt>
                                <dd class="text-2xl font-bold text-gray-900">
                                    {{ number_format($stat['count']) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Main Content Area with Sidebar -->
        <div class="flex gap-6">
            <!-- Sidebar - Test Sets -->
            <div class="w-80 flex-shrink-0">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 sticky top-6">
                    <div class="px-4 py-3 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Test Sets</h3>
                        <p class="mt-1 text-sm text-gray-500">Select a test set to view questions</p>
                    </div>

                    <div class="max-h-[calc(100vh-300px)] overflow-y-auto">
                        @php
                            $selectedTestSetId = request('test_set');
                            $groupedTestSets = $testSets->groupBy('section.name');
                        @endphp
                        
                        @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
                            @if(isset($groupedTestSets[$section]) && $groupedTestSets[$section]->count() > 0)
                                <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
                                    <div class="flex items-center">
                                        @php
                                            $sectionIcons = [
                                                'listening' => 'fas fa-headphones',
                                                'reading' => 'fas fa-book-open',
                                                'writing' => 'fas fa-pen-fancy',
                                                'speaking' => 'fas fa-microphone'
                                            ];
                                            $sectionColors = [
                                                'listening' => 'purple',
                                                'reading' => 'green',
                                                'writing' => 'blue',
                                                'speaking' => 'pink'
                                            ];
                                        @endphp
                                        <i class="{{ $sectionIcons[$section] }} text-{{ $sectionColors[$section] }}-500 mr-2"></i>
                                        <span class="text-sm font-medium text-gray-700 uppercase">{{ ucfirst($section) }}</span>
                                    </div>
                                </div>
                                @foreach($groupedTestSets[$section] as $testSet)
                                    <a href="#" 
                                       onclick="loadTestSetQuestions({{ $testSet->id }}); return false;"
                                       data-test-set-id="{{ $testSet->id }}"
                                       class="test-set-link block px-4 py-3 hover:bg-gray-50 transition-colors {{ $selectedTestSetId == $testSet->id ? 'bg-indigo-50 border-l-4 border-indigo-600' : 'border-l-4 border-transparent' }}">
                                        <div class="flex items-center justify-between">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium text-gray-900 truncate">
                                                    {{ $testSet->title }}
                                                </p>
                                                <div class="flex items-center mt-1">
                                                    <span class="text-xs text-gray-500">
                                                        <i class="fas fa-layer-group mr-1"></i>
                                                        {{ $testSet->questions_count }} questions
                                                    </span>
                                                    @if($testSet->active)
                                                        <span class="ml-2 w-2 h-2 bg-green-400 rounded-full"></span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($selectedTestSetId == $testSet->id)
                                                <i class="fas fa-chevron-right text-indigo-600"></i>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                        @endforeach
                        
                        @if($testSets->isEmpty())
                            <div class="p-6 text-center">
                                <i class="fas fa-folder-open text-3xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-500">No test sets available</p>
                                <a href="{{ route('admin.test-sets.create') }}" class="text-sm text-indigo-600 hover:text-indigo-700 mt-2 inline-block">
                                    Create Test Set
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Questions Display Area -->
            <div class="flex-1">
                <div id="questions-container" class="bg-white rounded-lg shadow-sm border border-gray-200 min-h-[600px]">
                    @if($selectedTestSetId && $questions->count() > 0)
                        @include('admin.questions.partials.questions-list', [
                            'questions' => $questions,
                            'selectedTestSet' => $testSets->find($selectedTestSetId)
                        ])
                    @else
                        <!-- Empty State -->
                        <div class="flex items-center justify-center h-[600px]">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-clipboard-list text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Select a test set</h3>
                                <p class="text-sm text-gray-500 max-w-sm">Choose a test set from the left panel to view and manage questions.</p>
                                <div class="mt-6">
                                    <a href="{{ route('admin.questions.create') }}" 
                                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                        <i class="fas fa-plus mr-2"></i>
                                        Create New Question
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <a href="{{ route('admin.questions.create') }}" 
                   class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <i class="fas fa-plus-circle text-2xl text-indigo-500 group-hover:text-indigo-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-900">Create Question</h4>
                        <p class="text-sm text-gray-500">Add new question</p>
                    </div>
                </a>
                
                <a href="#" 
                   class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <i class="fas fa-file-import text-2xl text-green-500 group-hover:text-green-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-900">Bulk Import</h4>
                        <p class="text-sm text-gray-500">Import from CSV</p>
                    </div>
                </a>
                
                <a href="#" 
                   class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <i class="fas fa-tags text-2xl text-purple-500 group-hover:text-purple-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-900">Question Types</h4>
                        <p class="text-sm text-gray-500">Manage types</p>
                    </div>
                </a>
                
                <a href="#" 
                   class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors group">
                    <i class="fas fa-chart-bar text-2xl text-orange-500 group-hover:text-orange-600 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-gray-900">Analytics</h4>
                        <p class="text-sm text-gray-500">View statistics</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-spinner" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
                <span class="ml-3 text-gray-700">Loading questions...</span>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Load test set questions via AJAX
        function loadTestSetQuestions(testSetId) {
            // Show loading
            document.getElementById('loading-spinner').classList.remove('hidden');
            
            // Update active state
            document.querySelectorAll('.test-set-link').forEach(link => {
                link.classList.remove('bg-indigo-50', 'border-indigo-600');
                link.classList.add('border-transparent');
                if (link.dataset.testSetId == testSetId) {
                    link.classList.add('bg-indigo-50', 'border-indigo-600');
                    link.classList.remove('border-transparent');
                }
            });

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('test_set', testSetId);
            window.history.pushState({}, '', url);

            // Fetch questions
            fetch(`/admin/questions/ajax/test-set/${testSetId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('questions-container').innerHTML = html;
                    
                    // Hide loading
                    document.getElementById('loading-spinner').classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('loading-spinner').classList.add('hidden');
                    
                    // Show error message
                    document.getElementById('questions-container').innerHTML = `
                        <div class="flex items-center justify-center h-[600px]">
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                                    <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Error loading questions</h3>
                                <p class="text-sm text-gray-500">Please try again later.</p>
                                <button onclick="loadTestSetQuestions(${testSetId})" class="mt-4 text-sm text-indigo-600 hover:text-indigo-700">
                                    Try Again
                                </button>
                            </div>
                        </div>
                    `;
                });
        }

        // Handle browser back/forward
        window.addEventListener('popstate', function(event) {
            const params = new URLSearchParams(window.location.search);
            const testSetId = params.get('test_set');
            if (testSetId) {
                loadTestSetQuestions(testSetId);
            } else {
                location.reload();
            }
        });

        // Initialize tooltips or other UI components if needed
        document.addEventListener('DOMContentLoaded', function() {
            // Any initialization code
        });
    </script>
    @endpush

    @push('styles')
    <style>
        /* Custom scrollbar for sidebar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f3f4f6;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }
    </style>
    @endpush
</x-admin-layout>
