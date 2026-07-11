{{-- Drag and Drop Panel for Reading --}}
<div id="reading-drag-drop-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-indigo-50">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">
            Drag & Drop Configuration
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Create drag and drop items with draggable options
        </p>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">How to use:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Add drop zones where students will drag answers</li>
                <li>• Define draggable options that students can use</li>
                <li>• Set correct answers for each drop zone</li>
                <li>• Options can be used multiple times or only once</li>
            </ul>
        </div>

        {{-- Drop Zones Section --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">Drop Zones</h4>
                <button type="button" onclick="ReadingQuestionTypes.addDropZone()" 
                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                    + Add Drop Zone
                </button>
            </div>
            <div id="reading-drop-zones-container" class="space-y-3">
                <!-- Drop zones will be added here -->
            </div>
        </div>

        {{-- Draggable Options Section --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">Draggable Options</h4>
                <button type="button" onclick="ReadingQuestionTypes.addDraggableOption()" 
                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                    + Add Option
                </button>
            </div>
            <div id="reading-draggable-options-container" class="space-y-2">
                <!-- Draggable options will be added here -->
            </div>
        </div>

        {{-- Options Reusability --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" id="reading-allow-reuse" name="drag_drop_allow_reuse" value="1" 
                       class="h-4 w-4 text-indigo-600 rounded" checked>
                <span class="ml-2 text-sm text-gray-700">
                    <strong>Allow options to be reused</strong> - Students can drag the same option to multiple zones
                </span>
            </label>
        </div>
    </div>
</div>

{{-- Hidden inputs for data storage --}}
<input type="hidden" id="reading-question-data" name="reading_question_data" value="">
