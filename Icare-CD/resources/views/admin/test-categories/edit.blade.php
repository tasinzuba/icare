<x-admin-layout>
    <x-slot:title>Edit Test Category - {{ $testCategory->name }}</x-slot>

    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.test-categories.show', $testCategory) }}"
                   class="w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 inline-flex items-center justify-center transition-colors shadow-sm">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Edit — {{ $testCategory->name }}</h1>
                    <p class="mt-0.5 text-sm text-slate-600">Update category details, appearance, and settings</p>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                <div class="flex items-center gap-2 mb-1">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span class="font-semibold">Please fix the following:</span>
                </div>
                <ul class="list-disc list-inside text-xs ml-1">
                    @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.test-categories.update', $testCategory) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Basic Info --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> Basic Information
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Category Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $testCategory->name) }}" required
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none @error('name') border-rose-300 @enderror" />
                        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="slug" class="block text-sm font-medium text-slate-700 mb-1.5">Slug</label>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $testCategory->slug) }}"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none @error('slug') border-rose-300 @enderror" />
                        @error('slug')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                        <textarea name="description" id="description" rows="3" placeholder="Optional. Describe what this category is for..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">{{ old('description', $testCategory->description) }}</textarea>
                        @error('description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Appearance --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-palette text-indigo-500"></i> Appearance
                </h3>

                @php
                    $iconMap = [
                        'academic-cap' => 'fa-graduation-cap',
                        'briefcase' => 'fa-briefcase',
                        'clipboard-list' => 'fa-clipboard-list',
                        'book-open' => 'fa-book-open',
                        'light-bulb' => 'fa-lightbulb',
                        'puzzle' => 'fa-puzzle-piece',
                    ];
                @endphp

                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-2">
                        Icon <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        @foreach($icons as $iconKey => $iconLabel)
                            <label class="cursor-pointer">
                                <input type="radio" name="icon" value="{{ $iconKey }}" class="sr-only peer"
                                       {{ old('icon', $testCategory->icon) == $iconKey ? 'checked' : '' }} required>
                                <div class="p-4 border-2 border-slate-200 rounded-lg text-center
                                            peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-700
                                            hover:border-slate-300 hover:bg-slate-50 transition-all">
                                    <i class="fas {{ $iconMap[$iconKey] ?? 'fa-circle-question' }} text-2xl mb-2 text-slate-500 peer-checked:text-indigo-600"></i>
                                    <p class="text-xs font-medium text-slate-700">{{ $iconLabel }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('icon')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="color" class="block text-sm font-medium text-slate-700 mb-2">
                        Color <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="color" id="color" value="{{ old('color', $testCategory->color) }}" required
                               class="h-11 w-14 rounded-lg cursor-pointer border border-slate-300" />
                        <input type="text" id="color-text" value="{{ old('color', $testCategory->color) }}"
                               pattern="^#[0-9A-Fa-f]{6}$"
                               class="px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm font-mono shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none w-40" />
                        <div id="color-preview" class="ml-2 px-3 py-1.5 rounded-md text-xs font-medium" style="background-color: {{ old('color', $testCategory->color) }}20; color: {{ old('color', $testCategory->color) }};">
                            Preview
                        </div>
                    </div>
                    @error('color')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Settings --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <i class="fas fa-sliders text-indigo-500"></i> Settings
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-1.5">
                            Sort Order <span class="text-slate-400 text-xs">(lower = first)</span>
                        </label>
                        <input type="number" name="sort_order" id="sort_order"
                               value="{{ old('sort_order', $testCategory->sort_order) }}" min="0"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 bg-white text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none" />
                        @error('sort_order')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="md:pt-7">
                        <label class="inline-flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1"
                                   {{ old('is_active', $testCategory->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                            <span class="text-sm font-medium text-slate-700">Active</span>
                            <span class="text-xs text-slate-500">(visible to users)</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('admin.test-categories.show', $testCategory) }}"
                   class="px-5 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-100 transition-colors">
                    Cancel
                </a>
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 rounded-lg bg-indigo-600 text-sm font-semibold text-white hover:bg-indigo-700 shadow-sm transition-colors">
                    <i class="fas fa-save mr-2"></i> Update Category
                </button>
            </div>
        </form>
    </div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    const preview = document.getElementById('color-preview');
    const updatePreview = (val) => {
        if (preview) {
            preview.style.backgroundColor = val + '20';
            preview.style.color = val;
        }
    };
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value;
        updatePreview(this.value);
    });
    colorText.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            colorPicker.value = this.value;
            updatePreview(this.value);
        }
    });
});
</script>
@endpush
</x-admin-layout>
