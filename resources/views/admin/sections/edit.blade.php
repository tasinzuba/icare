<x-layout>
    <x-slot:title>Edit Test Section - Admin</x-slot>
    
    <x-slot:header>
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Test Section') }}
            </h2>
            <a href="{{ route('admin.sections.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-300">
                Back to Sections
            </a>
        </div>
    </x-slot:header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form action="{{ route('admin.sections.update', $section) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-6">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900">Section Name</label>
                            <select id="name" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                                <option value="">Select a section</option>
                                <option value="listening" {{ old('name', $section->name) == 'listening' ? 'selected' : '' }}>Listening</option>
                                <option value="reading" {{ old('name', $section->name) == 'reading' ? 'selected' : '' }}>Reading</option>
                                <option value="writing" {{ old('name', $section->name) == 'writing' ? 'selected' : '' }}>Writing</option>
                                <option value="speaking" {{ old('name', $section->name) == 'speaking' ? 'selected' : '' }}>Speaking</option>
                            </select>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label for="description" class="block mb-2 text-sm font-medium text-gray-900">Description</label>
                            <textarea id="description" name="description" rows="4" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Enter section description">{{ old('description', $section->description) }}</textarea>
                            @error('description')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div class="mb-6">
                            <label for="time_limit" class="block mb-2 text-sm font-medium text-gray-900">Time Limit (minutes)</label>
                            <input type="number" id="time_limit" name="time_limit" value="{{ old('time_limit', $section->time_limit) }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" min="1" max="180">
                            @error('time_limit')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                            Update Section
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>