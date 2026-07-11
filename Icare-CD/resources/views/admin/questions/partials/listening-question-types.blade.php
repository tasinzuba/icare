{{-- Fill in the Blanks Panel --}}
<div id="fill-blanks-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-amber-50">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">
            Fill in the Blanks Configuration
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Insert blanks in the question content and provide correct answers
        </p>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">How to use:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Click "Insert Blank" or press Alt+B to add blanks in the question</li>
                <li>• Each blank will be numbered automatically: [____1____]</li>
                <li>• Enter the correct answer for each blank below</li>
                <li>• Use | to add alternate answers (e.g., "color|colour")</li>
            </ul>
        </div>
        
        <div id="blanks-manager-listening" class="hidden">
            <div class="bg-white border border-gray-200 rounded-md p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-900">Blank Answers</h4>
                    <span id="blank-counter-listening" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">0</span>
                </div>
                <div id="blanks-list-listening" class="space-y-2 max-h-64 overflow-y-auto">
                    <!-- Dynamically populated -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Single Choice Panel --}}
<div id="single-choice-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-blue-50">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">
            Single Choice Options
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Add options and select the correct answer (radio button)
        </p>
    </div>
    
    <div class="p-4 sm:p-6">
        <div id="single-choice-options-container" class="space-y-3">
            <!-- Options will be dynamically added here -->
        </div>
        
        <button type="button" onclick="ListeningQuestionTypes.addSingleChoiceOption()" 
                class="mt-4 px-4 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">
            + Add Option
        </button>
    </div>
</div>

{{-- Multiple Choice Panel --}}
<div id="multiple-choice-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-purple-50">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">
            Multiple Choice Options
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Add options and select correct answers (checkboxes)
        </p>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mb-4">
            <p class="text-sm text-yellow-800">
                <strong>Note:</strong> Multiple answers can be correct. Marks will be based on the number of correct options.
            </p>
        </div>
        
        <div id="multiple-choice-options-container" class="space-y-3">
            <!-- Options will be dynamically added here -->
        </div>
        
        <button type="button" onclick="ListeningQuestionTypes.addMultipleChoiceOption()" 
                class="mt-4 px-4 py-2 bg-purple-600 text-white text-sm rounded hover:bg-purple-700">
            + Add Option
        </button>
    </div>
</div>

{{-- Dropdown Selection Panel --}}
<div id="dropdown-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 bg-green-50">
        <h3 class="text-base sm:text-lg font-medium text-gray-900">
            Matching Letters Configuration
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Insert dropdowns in the question and configure options
        </p>
    </div>
    
    <div class="p-4 sm:p-6">
        <div class="bg-gray-50 rounded-lg p-4 mb-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">How to use:</h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li>• Click "Insert Dropdown" or press Alt+D to add dropdowns</li>
                <li>• Each dropdown will be numbered: [DROPDOWN_1]</li>
                <li>• Configure options and correct answer for each dropdown</li>
                <li>• All dropdowns will share the same options list</li>
            </ul>
        </div>
        
        <div id="dropdown-manager-listening" class="hidden">
            <div class="bg-white border border-gray-200 rounded-md p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-900">Dropdown Configuration</h4>
                    <span id="dropdown-counter-listening" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">0</span>
                </div>
                <div id="dropdown-list-listening" class="space-y-3">
                    <!-- Dynamically populated -->
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Drag and Drop Panel --}}
<div id="drag-drop-panel" class="type-specific-panel bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
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
                <li>• Write your question content in the editor above</li>
                <li>• Click "Insert Drag Zone" or press Alt+G where you want students to drag answers</li>
                <li>• Example: "The capital of France is [DRAG_1] and Germany is [DRAG_2]"</li>
                <li>• Add draggable options below that students can use</li>
                <li>• Select correct answer for each [DRAG_X] from the dropdown</li>
                <li>• Options can be used multiple times or only once</li>
            </ul>
        </div>

        {{-- Drag Zones Manager - Auto-populated from content --}}
        <div id="drag-zones-manager" class="mb-6 hidden">
            <div class="bg-white border border-gray-200 rounded-md p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-900">Drag Zones Configuration</h4>
                    <span id="drag-zone-counter" class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">0</span>
                </div>
                <div id="drag-zones-list" class="space-y-3 max-h-96 overflow-y-auto">
                    <!-- Drag zones will be auto-populated here -->
                </div>
            </div>
        </div>

        {{-- Draggable Options Section --}}
        <div class="mb-4">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">Draggable Options</h4>
                <button type="button" onclick="ListeningQuestionTypes.addDraggableOption()" 
                        class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700">
                    + Add Option
                </button>
            </div>
            <div id="draggable-options-container" class="space-y-2">
                <!-- Draggable options will be added here -->
            </div>
        </div>
    </div>
</div>

{{-- Hidden inputs for data storage --}}
<input type="hidden" id="listening-question-data" name="listening_question_data" value="">
