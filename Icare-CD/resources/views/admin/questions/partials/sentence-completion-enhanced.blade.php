<!-- Enhanced Sentence Completion Manager -->
<div id="sentence-completion-card" class="bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Sentence Completion Configuration</h3>
                <p class="text-sm text-gray-600 mt-1">Configure sentences with gaps and answer options</p>
            </div>
            <div class="text-sm text-gray-500">
                <span id="sentence-count" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">0 sentences</span>
            </div>
        </div>
    </div>

    <div class="p-4 sm:p-6">
        <!-- Order Number for First Question -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Starting Question Number <span class="text-red-500">*</span>
            </label>
            <input type="number"
                   id="sc_start_number"
                   value="{{ isset($question) ? $question->order_number : ($nextQuestionNumber ?? 1) }}"
                   min="1"
                   class="w-32 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   onchange="SentenceCompletionManager.updatePreview(); document.querySelector('#order-number-wrapper input[name=order_number]').value = this.value;">
            <p class="text-xs text-gray-500 mt-1">First question number in the sequence</p>
        </div>

        <!-- Instructions -->
        <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <h4 class="text-sm font-semibold text-yellow-800 mb-2">How to Use:</h4>
            <ol class="text-sm text-yellow-700 space-y-1 list-decimal list-inside">
                <li>Add sentence templates with gaps marked as [GAP]</li>
                <li>Add answer options that will appear in a dropdown list</li>
                <li>Select the correct answer for each sentence</li>
                <li>Students will see dropdowns in place of [GAP] markers</li>
            </ol>
        </div>

        <!-- Answer Options Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-medium text-gray-900">Answer Options</h4>
                <button type="button" 
                        id="add-answer-option-btn" 
                        class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Option
                </button>
            </div>
            <div id="answer-options-container" class="space-y-2 mb-4">
                <!-- Dynamic answer options will be added here -->
            </div>
        </div>

        <!-- Sentences Section -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-base font-medium text-gray-900">Sentences</h4>
                <button type="button" 
                        id="add-sentence-btn" 
                        class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add Sentence
                </button>
            </div>
            <div id="sentences-container" class="space-y-3">
                <!-- Dynamic sentences will be added here -->
            </div>
        </div>

        <!-- Preview Section -->
        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Preview</h4>
            <div id="sentence-completion-preview" class="text-sm text-gray-700">
                <p class="text-gray-500 italic">Add sentences to see preview...</p>
            </div>
        </div>

        <!-- Hidden input to store the complete data -->
        <input type="hidden" id="sentence_completion_data" name="sentence_completion_json" value="">
    </div>
</div>

<style>
    .sentence-input {
        transition: all 0.2s ease;
    }
    
    .sentence-input:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .option-letter {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background-color: #E5E7EB;
        border-radius: 50%;
        font-weight: 600;
        font-size: 12px;
        color: #374151;
    }
    
    .sentence-preview {
        font-family: Georgia, serif;
        line-height: 1.8;
    }
    
    .gap-dropdown {
        display: inline-block;
        padding: 2px 8px;
        background-color: #FEF3C7;
        border: 1px solid #F59E0B;
        border-radius: 4px;
        font-weight: 500;
        color: #92400E;
        margin: 0 4px;
    }
</style>
