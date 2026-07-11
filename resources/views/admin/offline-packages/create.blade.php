<x-admin-layout>
    <x-slot:title>Create Offline Package</x-slot>

    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Create New Offline Package</h2>
                <p class="mt-1 text-sm text-gray-600">Create a test package for branch/offline students</p>
            </div>

            <form action="{{ route('admin.offline-packages.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Package Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="e.g., Premium Full Test Package">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Brief description of the package">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="full_tests_allowed" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-clipboard-list text-indigo-500 mr-1"></i>Full Tests Allowed
                        </label>
                        <input type="number" name="full_tests_allowed" id="full_tests_allowed" value="{{ old('full_tests_allowed', 5) }}" required min="0" max="100"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('full_tests_allowed')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="section_tests_allowed" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-file-alt text-green-500 mr-1"></i>Section Tests Allowed
                        </label>
                        <input type="number" name="section_tests_allowed" id="section_tests_allowed" value="{{ old('section_tests_allowed', 0) }}" min="0" max="500"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('section_tests_allowed')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="validity_days" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-calendar-alt text-orange-500 mr-1"></i>Validity (Days)
                        </label>
                        <input type="number" name="validity_days" id="validity_days" value="{{ old('validity_days', 30) }}" required min="1" max="365"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('validity_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700">
                            <i class="fas fa-tag text-emerald-500 mr-1"></i>Price (BDT)
                        </label>
                        <input type="number" name="price" id="price" value="{{ old('price') }}" required min="0" step="0.01"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="5000">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700">
                        <i class="fas fa-building text-purple-500 mr-1"></i>Branch (Optional)
                    </label>
                    <select name="branch_id" id="branch_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Global (All Branches)</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-500">Leave empty to make this package available to all branches</p>
                    @error('branch_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="display_order" class="block text-sm font-medium text-gray-700">Display Order</label>
                        <input type="number" name="display_order" id="display_order" value="{{ old('display_order', 0) }}" min="0"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                        @error('display_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
                    </div>
                </div>

                <!-- Live Preview -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Package Preview</h3>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <div class="flex items-start justify-between mb-3">
                            <div class="text-lg font-semibold text-gray-900" id="preview-name">Package Name</div>
                            <span id="preview-branch" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-globe mr-1"></i>Global
                            </span>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div><i class="fas fa-clipboard-list mr-2 text-indigo-500"></i><span id="preview-full">5</span> Full Tests</div>
                            <div><i class="fas fa-file-alt mr-2 text-green-500"></i><span id="preview-section">0</span> Section Tests</div>
                            <div><i class="fas fa-calendar-alt mr-2 text-orange-500"></i><span id="preview-days">30</span> Days Validity</div>
                        </div>
                        <div class="pt-3 border-t border-gray-200">
                            <span class="text-2xl font-bold text-gray-900" id="preview-price">0</span>
                            <span class="text-gray-500 ml-1">BDT</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.offline-packages.index') }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Create Package
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        // Live preview
        document.getElementById('name').addEventListener('input', function() {
            document.getElementById('preview-name').textContent = this.value || 'Package Name';
        });

        document.getElementById('full_tests_allowed').addEventListener('input', function() {
            document.getElementById('preview-full').textContent = this.value || '0';
        });

        document.getElementById('section_tests_allowed').addEventListener('input', function() {
            document.getElementById('preview-section').textContent = this.value || '0';
        });

        document.getElementById('validity_days').addEventListener('input', function() {
            document.getElementById('preview-days').textContent = this.value || '0';
        });

        document.getElementById('price').addEventListener('input', function() {
            document.getElementById('preview-price').textContent = parseFloat(this.value || 0).toLocaleString();
        });

        document.getElementById('branch_id').addEventListener('change', function() {
            const branchSpan = document.getElementById('preview-branch');
            if (this.value) {
                branchSpan.innerHTML = '<i class="fas fa-building mr-1"></i>' + this.options[this.selectedIndex].text;
                branchSpan.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800';
            } else {
                branchSpan.innerHTML = '<i class="fas fa-globe mr-1"></i>Global';
                branchSpan.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800';
            }
        });
    </script>
    @endpush
</x-admin-layout>
