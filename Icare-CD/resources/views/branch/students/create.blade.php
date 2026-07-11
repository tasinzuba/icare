@extends('layouts.branch')

@section('title', 'Enroll New Student')

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.students.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Students
    </a>
</div>

<div class="max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Enroll New Student</h1>

    @if(session('error'))
    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    <form action="{{ route('branch.students.store') }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf

        <!-- Student Information -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-user text-indigo-500 mr-2"></i> Student Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Login credentials will be sent to this email.</p>
                    @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="text" name="password" value="{{ old('password') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Leave empty to auto-generate" minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Min 6 characters. Leave empty for auto-generated password.</p>
                    @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- Batch Selection -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-layer-group text-indigo-500 mr-2"></i> Select Batch *
            </h2>
            @if($batches->count() > 0)
            <div>
                <select name="batch_id" id="batch_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">-- Select a Batch --</option>
                    @foreach($batches as $batch)
                        <option value="{{ $batch->id }}"
                                data-full-tests="{{ $batch->full_tests_allowed }}"
                                data-section-tests="{{ $batch->section_tests_allowed }}"
                                data-validity="{{ $batch->validity_days }}"
                                {{ old('batch_id', request('batch_id')) == $batch->id ? 'selected' : '' }}>
                            {{ $batch->name }} ({{ $batch->full_tests_allowed }} Full, {{ $batch->section_tests_allowed }} Section, {{ $batch->validity_days }} Days)
                        </option>
                    @endforeach
                </select>
                @error('batch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Batch Summary -->
            <div id="batchSummary" class="mt-4 p-4 bg-indigo-50 rounded-lg hidden">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-indigo-600" id="batchFullTests">0</p>
                        <p class="text-xs text-indigo-700">Full Tests</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600" id="batchSectionTests">0</p>
                        <p class="text-xs text-green-700">Section Tests</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-orange-600" id="batchValidity">0</p>
                        <p class="text-xs text-orange-700">Days Validity</p>
                    </div>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-2">
                <a href="{{ route('branch.batches.create') }}" class="text-indigo-600 hover:underline">
                    <i class="fas fa-plus mr-1"></i>Create new batch
                </a> if you need a different configuration.
            </p>
            @else
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No batches available. <a href="{{ route('branch.batches.create') }}" class="font-semibold underline">Create a batch first</a>.
                </p>
            </div>
            @endif
        </div>

        <!-- Evaluation Type -->
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-robot text-purple-500 mr-2"></i> Evaluation Type *
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="evaluation_type" value="ai" class="hidden peer" {{ old('evaluation_type', 'ai') === 'ai' ? 'checked' : '' }}>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:border-blue-300 transition-all text-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-robot text-blue-600 text-xl"></i>
                        </div>
                        <p class="font-semibold text-gray-800">AI Only</p>
                        <p class="text-xs text-gray-500 mt-1">Instant AI evaluation</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="evaluation_type" value="human" class="hidden peer" {{ old('evaluation_type') === 'human' ? 'checked' : '' }}>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:border-purple-300 transition-all text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-user-tie text-purple-600 text-xl"></i>
                        </div>
                        <p class="font-semibold text-gray-800">Human Only</p>
                        <p class="text-xs text-gray-500 mt-1">Teacher evaluation</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="evaluation_type" value="both" class="hidden peer" {{ old('evaluation_type') === 'both' ? 'checked' : '' }}>
                    <div class="p-4 border-2 rounded-xl peer-checked:border-green-500 peer-checked:bg-green-50 hover:border-green-300 transition-all text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-balance-scale text-green-600 text-xl"></i>
                        </div>
                        <p class="font-semibold text-gray-800">Both</p>
                        <p class="text-xs text-gray-500 mt-1">AI & Human options</p>
                    </div>
                </label>
            </div>
            @error('evaluation_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <!-- Notes -->
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-sticky-note text-yellow-500 mr-1"></i> Notes (Optional)
            </label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Any additional notes about this enrollment...">{{ old('notes') }}</textarea>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-4">
            <a href="{{ route('branch.students.index') }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Enroll Student
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const batchSelect = document.getElementById('batch_id');
    const batchSummary = document.getElementById('batchSummary');

    if (batchSelect) {
        batchSelect.addEventListener('change', function() {
            const opt = this.options[this.selectedIndex];
            if (this.value && opt) {
                document.getElementById('batchFullTests').textContent = opt.dataset.fullTests || 0;
                document.getElementById('batchSectionTests').textContent = opt.dataset.sectionTests || 0;
                document.getElementById('batchValidity').textContent = opt.dataset.validity || 0;
                batchSummary.classList.remove('hidden');
            } else {
                batchSummary.classList.add('hidden');
            }
        });
        // Trigger on page load if batch pre-selected
        batchSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
