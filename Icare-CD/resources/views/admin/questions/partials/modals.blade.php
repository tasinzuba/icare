<!-- Template Modal -->
<div id="template-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Instruction Templates</h3>
        <div class="space-y-2" id="template-list">
            <!-- Templates will be loaded based on section -->
        </div>
        <button onclick="closeTemplates()" class="mt-4 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Close</button>
    </div>
</div>

<!-- Preview Modal -->
<div id="preview-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Question Preview</h3>
            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="preview-content" class="border rounded-lg p-6 bg-gray-50">
            <!-- Preview content will be inserted here -->
        </div>
    </div>
</div>

<!-- Bulk Options Modal -->
<div id="bulk-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Bulk Options</h3>
        <p class="text-sm text-gray-600 mb-2">Enter each option on a new line:</p>
        <textarea id="bulk-text" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"></textarea>
        <div class="flex justify-end space-x-3 mt-4">
            <button onclick="closeBulkOptions()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">Cancel</button>
            <button onclick="addBulkOptions()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Options</button>
        </div>
    </div>
</div>