<x-admin-layout>
    <x-slot:title>Edit Branch</x-slot>

    <div class="max-w-4xl mx-auto">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.branches.show', $branch) }}"
                   class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Edit Branch</h1>
                    <p class="mt-1 text-sm text-gray-600">Update branch information for {{ $branch->name }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <form action="{{ route('admin.branches.update', $branch) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Branch Identity -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Branch Identity</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Branch Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $branch->name) }}"
                                   required
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Branch Code <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="code"
                                   value="{{ old('code', $branch->code) }}"
                                   required
                                   maxlength="10"
                                   style="text-transform: uppercase;"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('code') border-red-300 @enderror">
                            @error('code')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Location Details -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Location Details</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Address
                            </label>
                            <textarea name="address"
                                      rows="2"
                                      class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-300 @enderror">{{ old('address', $branch->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    City
                                </label>
                                <input type="text"
                                       name="city"
                                       value="{{ old('city', $branch->city) }}"
                                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('city') border-red-300 @enderror">
                                @error('city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone
                                </label>
                                <input type="text"
                                       name="phone"
                                       value="{{ old('phone', $branch->phone) }}"
                                       class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-300 @enderror">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact & Status -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact & Status</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Email
                            </label>
                            <input type="email"
                                   name="email"
                                   value="{{ old('email', $branch->email) }}"
                                   class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Status
                            </label>
                            <label class="flex items-center mt-2">
                                <input type="checkbox"
                                       name="active"
                                       value="1"
                                       {{ $branch->active ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Active (Branch is operational)</span>
                            </label>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Test Retakes
                            </label>
                            <label class="flex items-center mt-2">
                                <input type="checkbox"
                                       name="allow_test_retakes"
                                       value="1"
                                       {{ old('allow_test_retakes', $branch->allow_test_retakes) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">Allow students to retake completed tests (free, no quota deducted)</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t">
                    <button type="button"
                            onclick="document.getElementById('delete-form').submit();"
                            class="px-4 py-2 text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg">
                        <i class="fas fa-trash mr-2"></i> Delete Branch
                    </button>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.branches.show', $branch) }}"
                           class="px-4 py-2 text-gray-700 hover:text-gray-900">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i> Update Branch
                        </button>
                    </div>
                </div>
            </form>

            <!-- Delete Form (separate to avoid nesting) -->
            <form id="delete-form" action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="hidden" onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.');">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>
</x-admin-layout>
