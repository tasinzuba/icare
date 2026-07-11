<x-admin-layout>
    <x-slot name="title">Ban User - {{ $user->name }}</x-slot>
    
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">Ban User: {{ $user->name }}</h1>
            
            <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="ban_type" class="block text-sm font-medium text-gray-700">Ban Type</label>
                        <select name="ban_type" 
                                id="ban_type" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('ban_type') border-red-300 @enderror" 
                                required>
                            <option value="">Select ban type</option>
                            <option value="temporary" {{ old('ban_type') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                            <option value="permanent" {{ old('ban_type') == 'permanent' ? 'selected' : '' }}>Permanent</option>
                        </select>
                        @error('ban_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div id="duration-field" style="display: none;">
                        <label for="ban_duration" class="block text-sm font-medium text-gray-700">Ban Duration (days)</label>
                        <input type="number" 
                               name="ban_duration" 
                               id="ban_duration" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('ban_duration') border-red-300 @enderror"
                               min="1" 
                               max="365" 
                               value="{{ old('ban_duration', 7) }}"
                               placeholder="Enter number of days">
                        <p class="mt-1 text-sm text-gray-500">How many days should the ban last? (1-365 days)</p>
                        @error('ban_duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="ban_reason" class="block text-sm font-medium text-gray-700">Ban Reason</label>
                        <textarea name="ban_reason" 
                                  id="ban_reason" 
                                  rows="4" 
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('ban_reason') border-red-300 @enderror" 
                                  placeholder="Provide a clear reason for the ban..."
                                  required>{{ old('ban_reason') }}</textarea>
                        <p class="mt-1 text-sm text-gray-500">This reason will be shown to the user.</p>
                        @error('ban_reason')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Warning</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>Banning this user will immediately prevent them from accessing their account.</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>The user will be logged out if currently logged in</li>
                                        <li>They will see the ban reason when trying to access the platform</li>
                                        <li>They will be able to submit an appeal</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between pt-6">
                        <a href="{{ route('admin.users.show', $user) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                            </svg>
                            Ban User
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const banTypeSelect = document.getElementById('ban_type');
    const durationField = document.getElementById('duration-field');
    
    function toggleDurationField() {
        if (banTypeSelect.value === 'temporary') {
            durationField.style.display = 'block';
        } else {
            durationField.style.display = 'none';
        }
    }
    
    banTypeSelect.addEventListener('change', toggleDurationField);
    toggleDurationField(); // Initial check
});
</script>
</x-admin-layout>
