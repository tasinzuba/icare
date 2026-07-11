@extends('layouts.branch')

@section('title', 'Edit Batch: ' . $batch->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.batches.show', $batch) }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Batch
    </a>
</div>

<div class="max-w-4xl">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Edit Batch: {{ $batch->name }}</h1>
    <p class="text-gray-500 mb-6">Changes will apply to new enrollments only. Existing students keep their current config.</p>

    <form action="{{ route('branch.batches.update', $batch) }}" method="POST">
        @csrf @method('PUT')

        <!-- Batch Info -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-layer-group text-indigo-500 mr-2"></i> Batch Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Name *</label>
                    <input type="text" name="name" value="{{ old('name', $batch->name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <input type="text" name="description" value="{{ old('description', $batch->description) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>
        </div>

        <!-- Test Configuration -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-cog text-indigo-500 mr-2"></i> Test Configuration
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Tests Allowed *</label>
                    <input type="number" name="full_tests_allowed" value="{{ old('full_tests_allowed', $batch->full_tests_allowed) }}" required min="0" max="100"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Validity (Days) *</label>
                    <input type="number" name="validity_days" value="{{ old('validity_days', $batch->validity_days) }}" required min="1" max="365"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>
            </div>

            <h3 class="text-sm font-semibold text-gray-700 mb-3">Per-Section Test Limits</h3>
            @php $limits = $batch->section_test_limits ?? []; @endphp
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                @foreach(['listening' => 'Listening', 'reading' => 'Reading', 'writing' => 'Writing', 'speaking' => 'Speaking'] as $key => $label)
                <div class="bg-gray-50 rounded-lg p-3 text-center">
                    <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}</label>
                    <input type="number" name="section_limit_{{ $key }}" value="{{ old('section_limit_' . $key, $limits[$key] ?? 0) }}" min="0" max="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-center focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 section-limit-input">
                </div>
                @endforeach
            </div>
            <div class="text-sm text-gray-500">Total section tests: <strong id="totalSectionTests">0</strong></div>
        </div>

        <!-- Allowed Full Tests -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-clipboard-list text-indigo-500 mr-2"></i> Allowed Full Tests
                <span class="ml-auto text-sm font-normal text-gray-400">Leave unchecked = all tests allowed</span>
            </h2>
            @php $selectedFull = old('allowed_full_tests', $batch->allowed_full_tests ?? []); @endphp
            <div class="max-h-64 overflow-y-auto space-y-2">
                @foreach($fullTests as $test)
                <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
                    <input type="checkbox" name="allowed_full_tests[]" value="{{ $test->id }}"
                           {{ in_array($test->id, array_map('intval', $selectedFull)) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-gray-700">{{ $test->title }}</span>
                    @if($test->is_premium)
                        <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded bg-amber-100 text-amber-700">Premium</span>
                    @endif
                </label>
                @endforeach
            </div>
        </div>

        <!-- Allowed Section Tests -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-file-alt text-indigo-500 mr-2"></i> Allowed Section Tests
                <span class="ml-auto text-sm font-normal text-gray-400">Leave unchecked = all tests allowed</span>
            </h2>
            @php
                $selectedSection = old('allowed_section_tests', $batch->allowed_section_tests ?? []);
                $grouped = $sectionTests->groupBy(fn($t) => $t->section->name ?? 'other');
                $sectionColors = ['listening' => 'blue', 'reading' => 'green', 'writing' => 'purple', 'speaking' => 'orange'];
            @endphp
            <div class="max-h-80 overflow-y-auto space-y-4">
                @foreach($grouped as $sectionName => $tests)
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">
                        <span class="inline-block w-2 h-2 rounded-full bg-{{ $sectionColors[$sectionName] ?? 'gray' }}-500 mr-1"></span>
                        {{ ucfirst($sectionName) }} ({{ $tests->count() }})
                    </h4>
                    <div class="space-y-1 ml-3">
                        @foreach($tests as $test)
                        <label class="flex items-center gap-3 p-1.5 rounded hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="allowed_section_tests[]" value="{{ $test->id }}"
                                   {{ in_array($test->id, array_map('intval', $selectedSection)) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700">{{ $test->title }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Sync to Students -->
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="sync_students" value="1" checked
                       class="mt-1 rounded border-amber-400 text-amber-600 focus:ring-amber-500">
                <div>
                    <p class="font-semibold text-amber-800">Sync changes to active students</p>
                    <p class="text-xs text-amber-700 mt-0.5">
                        Enable this to update all active students in this batch with the new test config.
                        Disable if you only want to change the batch template for future enrollments.
                    </p>
                </div>
            </label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('branch.batches.show', $batch) }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-semibold">
                <i class="fas fa-save mr-2"></i> Update Batch
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.section-limit-input');
    const total = document.getElementById('totalSectionTests');
    function updateTotal() {
        let sum = 0;
        inputs.forEach(i => sum += parseInt(i.value) || 0);
        total.textContent = sum;
    }
    inputs.forEach(i => i.addEventListener('input', updateTotal));
    updateTotal();
});
</script>
@endpush
@endsection
