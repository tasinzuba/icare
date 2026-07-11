<div id="options-card" class="bg-white rounded-lg shadow-sm hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Answer Options</h3>
        <button type="button" onclick="showBulkOptions()" class="text-sm text-blue-600 hover:text-blue-700">
            Add Bulk Options
        </button>
    </div>
    
    <div class="p-6">
        <div id="options-container" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <!-- Options will be dynamically added here -->
        </div>
        
        <button type="button" id="add-option-btn" 
                class="mt-4 w-full md:w-auto px-4 py-2 border-2 border-dashed border-gray-300 text-gray-500 rounded-md hover:border-gray-400 hover:text-gray-600 transition-all">
            + Add Option
        </button>
    </div>
</div>