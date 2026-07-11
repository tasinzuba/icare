<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Actions</h3>
    </div>
    
    <div class="p-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" name="action" value="save" class="flex-1 py-3 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 transition-colors">
                Save Question
            </button>
            <button type="submit" name="action" value="save_and_new" class="flex-1 py-3 bg-green-600 text-white font-medium rounded-md hover:bg-green-700 transition-colors">
                Save & Add Another
            </button>
            <button type="button" onclick="previewQuestion()" class="flex-1 py-3 border border-gray-300 text-gray-700 font-medium rounded-md hover:bg-gray-50 transition-colors">
                Preview
            </button>
        </div>
    </div>
</div>