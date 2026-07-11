<div class="bg-white rounded-lg shadow-sm">
    <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Media Files</h3>
    </div>
    
    <div class="p-6">
        <div class="border-2 border-dashed border-gray-300 rounded-md p-8 text-center hover:border-gray-400 transition-colors cursor-pointer"
             id="drop-zone" onclick="document.getElementById('media').click()">
            <input type="file" id="media" name="media" class="hidden" 
                   accept="{{ $acceptedFormats ?? 'image/*,.mp3,.wav,.ogg' }}">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            <p class="mt-2 text-sm text-gray-600">
                <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span> or drag and drop
            </p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $mediaHelpText ?? 'Images: PNG, JPG, GIF (max 10MB) or Audio files' }}
            </p>
        </div>
        <div id="media-preview" class="mt-4 hidden">
            <!-- Preview will be shown here -->
        </div>
    </div>
</div>