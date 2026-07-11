<!-- Question Status Card -->
<div class="bg-white rounded-lg shadow-sm mb-6 border-l-4 border-blue-500">
    <div class="p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Creating Question</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Total questions in this test: <span class="font-semibold">{{ $existingQuestions->count() }}</span>
                </p>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-blue-600" id="question-number-display">
                    #{{ $nextQuestionNumber }}
                </div>
                <p class="text-xs text-gray-500">Question Number</p>
            </div>
        </div>
        
        @if($existingQuestions->count() > 0)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <p class="text-xs text-gray-500 mb-2">Existing questions:</p>
            <div class="flex flex-wrap gap-1">
                @foreach($existingQuestions->sortBy('order_number') as $q)
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-700">
                    #{{ $q->order_number }}
                </span>
                @endforeach
                <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-700 border border-blue-300">
                    #{{ $nextQuestionNumber }} (new)
                </span>
            </div>
        </div>
        @endif
    </div>
</div>