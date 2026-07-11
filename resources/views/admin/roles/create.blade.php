<x-admin-layout>
    <x-slot name="title">Create New Role</x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-4 py-5 sm:p-6">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Create New Role</h1>
                    <p class="mt-1 text-sm text-gray-600">Create a custom role with specific permissions</p>
                </div>

                <form action="{{ route('admin.roles.store') }}" method="POST">
                    @csrf

                    <!-- Role Name -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Role Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Description
                        </label>
                        <textarea name="description" id="description" rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Permissions -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-4">
                            Select Permissions
                        </label>

                        @foreach($permissions as $module => $modulePermissions)
                            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-base font-semibold text-gray-900 capitalize">{{ $module }}</h3>
                                    <button type="button" onclick="toggleModulePermissions('{{ $module }}')" 
                                        class="text-sm text-indigo-600 hover:text-indigo-800">
                                        Select All
                                    </button>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3" data-module="{{ $module }}">
                                    @foreach($modulePermissions as $permission)
                                        <div class="flex items-start">
                                            <div class="flex items-center h-5">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                                    id="permission_{{ $permission->id }}"
                                                    {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}
                                                    class="permission-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            </div>
                                            <div class="ml-3">
                                                <label for="permission_{{ $permission->id }}" class="text-sm font-medium text-gray-700">
                                                    {{ $permission->name }}
                                                </label>
                                                @if($permission->description)
                                                    <p class="text-xs text-gray-500">{{ $permission->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @error('permissions')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.roles.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleModulePermissions(module) {
            const moduleDiv = document.querySelector(`[data-module="${module}"]`);
            const checkboxes = moduleDiv.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
        }
    </script>
</x-admin-layout>
