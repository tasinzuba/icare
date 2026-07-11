<x-admin-layout>
    <x-slot:title>Select Test Set - Add Question</x-slot>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Select Test Set</h1>
                    <p class="mt-1 text-sm text-gray-600">Choose a test set to add new questions</p>
                </div>
                <a href="{{ route('admin.questions.index') }}" 
                   class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Questions
                </a>
            </div>
        </div>
    </div>

    <!-- Content Card -->
    <div class="rounded-xl bg-white shadow-sm border border-gray-200">
        <!-- Section Tabs -->
        <div class="border-b border-gray-200 px-6 pt-6">
            <nav class="-mb-px flex space-x-8">
                @foreach($sections as $section)
                    <button type="button" 
                            onclick="switchTab('{{ $section->name }}')"
                            id="tab-{{ $section->name }}"
                            class="py-3 px-1 border-b-2 font-medium text-sm transition-colors cursor-pointer {{ $loop->first ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <span class="inline-flex items-center pointer-events-none">
                            @switch($section->name)
                                @case('listening')
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" />
                                    </svg>
                                    @break
                                @case('reading')
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    @break
                                @case('writing')
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    @break
                                @case('speaking')
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                    </svg>
                                    @break
                            @endswitch
                            {{ ucfirst($section->name) }}
                        </span>
                    </button>
                @endforeach
            </nav>
        </div>
        
        <!-- Test Sets Content -->
        <div class="p-6">
            @foreach($sections as $section)
                <div id="content-{{ $section->name }}" class="section-content {{ !$loop->first ? 'hidden' : '' }}">
                    @php
                        $sectionTestSets = $testSets->where('section_id', $section->id);
                    @endphp
                    
                    @if($sectionTestSets->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($sectionTestSets as $testSet)
                                <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}" 
                                   class="group block rounded-lg border-2 border-gray-200 bg-white p-5 hover:border-indigo-500 hover:shadow-md transition-all">
                                    <div class="flex items-start justify-between mb-3">
                                        <h3 class="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $testSet->title }}
                                        </h3>
                                        <svg class="h-5 w-5 text-gray-400 group-hover:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex items-center text-gray-600">
                                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ $testSet->questions->count() }} questions
                                        </div>
                                        <div class="flex items-center">
                                            @if($testSet->active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                    </svg>
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                    Inactive
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-gray-100 mb-4">
                                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">No test sets found</h3>
                            <p class="text-sm text-gray-500 mb-6">Get started by creating a test set for {{ ucfirst($section->name) }}.</p>
                            <a href="{{ route('admin.test-sets.create', ['section' => $section->id]) }}" 
                               class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create Test Set
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-admin-layout>

<script>
function switchTab(sectionName) {
    console.log('Switching to:', sectionName);
    
    // Get all tabs and contents
    const allTabs = document.querySelectorAll('[id^="tab-"]');
    const allContents = document.querySelectorAll('[id^="content-"]');
    
    // Reset all tabs
    allTabs.forEach(tab => {
        tab.classList.remove('border-indigo-500', 'text-indigo-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Hide all contents
    allContents.forEach(content => {
        content.classList.add('hidden');
    });
    
    // Activate clicked tab
    const activeTab = document.getElementById('tab-' + sectionName);
    if (activeTab) {
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        activeTab.classList.add('border-indigo-500', 'text-indigo-600');
    }
    
    // Show corresponding content
    const activeContent = document.getElementById('content-' + sectionName);
    if (activeContent) {
        activeContent.classList.remove('hidden');
    }
}
</script>
