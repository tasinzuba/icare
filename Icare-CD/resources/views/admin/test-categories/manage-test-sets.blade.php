<x-admin-layout>
    <x-slot:title>Manage Test Sets - {{ $testCategory->name }}</x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-2xl font-semibold">Manage Test Sets</h2>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">
                            Category: <span class="font-medium">{{ $testCategory->name }}</span>
                        </p>
                    </div>
                    <a href="{{ route('admin.test-categories.show', $testCategory) }}" 
                       class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        Back to Category
                    </a>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.test-categories.update-test-sets', $testCategory) }}" method="POST">
                    @csrf

                    <!-- Test Sets by Section -->
                    <div class="space-y-6">
                        @foreach($sections as $section)
                            <div class="border rounded-lg p-4 dark:border-gray-700">
                                <h3 class="text-lg font-medium mb-3 flex items-center">
                                    @if($section->slug == 'listening')
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                        </svg>
                                    @elseif($section->slug == 'reading')
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                    @elseif($section->slug == 'writing')
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    @elseif($section->slug == 'speaking')
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                        </svg>
                                    @endif
                                    {{ $section->name }}
                                </h3>
                                
                                @if($section->testSets->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        @foreach($section->testSets as $testSet)
                                            <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-2 rounded">
                                                <input type="checkbox" 
                                                       name="test_sets[]" 
                                                       value="{{ $testSet->id }}"
                                                       {{ in_array($testSet->id, $assignedTestSetIds) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                                                <span class="text-sm">{{ $testSet->title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 dark:text-gray-400 text-sm">No test sets available for this section.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <span id="selected-count">0</span> test sets selected
                        </div>
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('admin.test-categories.show', $testCategory) }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                            <button type="submit" 
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Test Sets
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="test_sets[]"]');
    const selectedCount = document.getElementById('selected-count');
    
    function updateCount() {
        const count = document.querySelectorAll('input[name="test_sets[]"]:checked').length;
        selectedCount.textContent = count;
    }
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateCount);
    });
    
    // Initial count
    updateCount();
});
</script>
@endpush
</x-admin-layout>
