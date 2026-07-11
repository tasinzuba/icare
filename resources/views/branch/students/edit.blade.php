@extends('layouts.branch')

@section('title', 'Edit ' . $enrollment->student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.students.show', $enrollment) }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Student
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Edit Student</h1>
    <p class="text-gray-600 mb-6">{{ $enrollment->student_id }}</p>

    <form action="{{ route('branch.students.update', $enrollment) }}" method="POST" class="bg-white rounded-xl shadow-md p-6">
        @csrf
        @method('PUT')

        {{-- Student Information --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-user text-indigo-500 mr-2"></i> Student Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input type="text" name="name" value="{{ old('name', $enrollment->student->name) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $enrollment->student->email) }}" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" name="phone_number" value="{{ old('phone_number', $enrollment->student->phone_number) }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('phone_number')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="text" name="new_password" value=""
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                           placeholder="Leave empty to keep current" minlength="6">
                    <p class="text-xs text-gray-500 mt-1">Min 6 characters. Empty = no change.</p>
                    @error('new_password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Batch Selection --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-layer-group text-indigo-500 mr-2"></i> Batch
            </h2>
            <select name="batch_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">-- No Batch --</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch->id }}" {{ old('batch_id', $enrollment->batch_id) == $batch->id ? 'selected' : '' }}>
                        {{ $batch->name }} ({{ $batch->full_tests_allowed }} Full, {{ $batch->section_tests_allowed }} Section, {{ $batch->validity_days }} Days)
                    </option>
                @endforeach
            </select>
            @if($enrollment->batch)
                <p class="text-xs text-gray-500 mt-1">Current batch: <strong>{{ $enrollment->batch->name }}</strong></p>
            @endif
            @error('batch_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        {{-- Status --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b flex items-center">
                <i class="fas fa-toggle-on text-indigo-500 mr-2"></i> Enrollment Status
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="enrollmentStatus" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500" onchange="toggleValidityField()">
                        <option value="active" {{ old('status', $enrollment->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $enrollment->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ old('status', $enrollment->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                        <option value="completed" {{ old('status', $enrollment->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Validity Extension (shows when reactivating expired/completed enrollment) --}}
            <div id="validitySection" class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-lg" style="display: none;">
                <div class="flex items-center gap-2 mb-3">
                    <i class="fas fa-calendar-plus text-amber-600"></i>
                    <p class="text-sm font-medium text-amber-800">Validity date expired। Active করতে হলে নতুন validity date দিন:</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Current Valid Until</label>
                        <input type="text" disabled value="{{ $enrollment->valid_until?->format('Y-m-d') ?? 'N/A' }}"
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">New Valid Until *</label>
                        <input type="date" name="valid_until" id="validUntilInput"
                               value="{{ old('valid_until', now()->addDays(30)->format('Y-m-d')) }}"
                               min="{{ now()->format('Y-m-d') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        @error('valid_until')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p class="text-xs text-amber-600 mt-2">
                    <i class="fas fa-info-circle mr-1"></i> টেস্ট সংখ্যা বা নতুন টেস্ট add করতে হলে <strong>Renew / Modify Package</strong> ব্যবহার করুন।
                </p>
            </div>
        </div>

        {{-- Notes --}}
        <div class="mb-8">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                <i class="fas fa-sticky-note text-yellow-500 mr-1"></i> Notes
            </label>
            <textarea name="notes" rows="3"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                      placeholder="Any additional notes...">{{ old('notes', $enrollment->notes) }}</textarea>
        </div>

        <div class="flex justify-end space-x-4">
            <a href="{{ route('branch.students.show', $enrollment) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                <i class="fas fa-save"></i> Update
            </button>
        </div>
    </form>

    {{-- Current Package Info (Read Only) --}}
    <div class="bg-gray-50 rounded-xl p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                <i class="fas fa-box text-gray-500 mr-2"></i> Current Package
            </h2>
            <a href="{{ route('branch.students.renew.form', $enrollment) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm flex items-center gap-2">
                <i class="fas fa-sync-alt"></i> Renew / Modify Package
            </a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div class="bg-white p-3 rounded-lg">
                <p class="text-gray-500">Full Tests</p>
                <p class="text-xl font-bold text-indigo-600">{{ $enrollment->full_tests_taken }} / {{ $enrollment->full_tests_allowed }}</p>
            </div>
            <div class="bg-white p-3 rounded-lg">
                <p class="text-gray-500">Section Tests</p>
                @if($enrollment->hasPerSectionLimits())
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach(['listening' => 'L', 'reading' => 'R', 'writing' => 'W', 'speaking' => 'S'] as $secType => $secLabel)
                            @php $secLimit = $enrollment->getSectionTestLimit($secType); @endphp
                            @if($secLimit > 0)
                                <span class="px-1.5 py-0.5 bg-green-100 text-green-700 rounded text-xs font-medium" title="{{ ucfirst($secType) }}">
                                    {{ $secLabel }}: {{ $enrollment->getSectionTestsTaken($secType) }}/{{ $secLimit }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-xl font-bold text-green-600">{{ $enrollment->section_tests_taken }} / {{ $enrollment->section_tests_allowed }}</p>
                @endif
            </div>
            <div class="bg-white p-3 rounded-lg">
                <p class="text-gray-500">Valid Until</p>
                <p class="text-xl font-bold {{ $enrollment->isExpired() ? 'text-red-600' : 'text-gray-800' }}">
                    {{ $enrollment->valid_until?->format('M d, Y') ?? 'N/A' }}
                </p>
            </div>
            <div class="bg-white p-3 rounded-lg">
                <p class="text-gray-500">Evaluation</p>
                <p class="text-xl font-bold text-purple-600">{{ ucfirst($enrollment->evaluation_type ?? 'AI') }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-3">
            <i class="fas fa-info-circle mr-1"></i> To change package, validity, tests, or evaluation type, use the <strong>Renew / Modify Package</strong> option.
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleValidityField() {
    const status = document.getElementById('enrollmentStatus').value;
    const validitySection = document.getElementById('validitySection');
    const validUntilInput = document.getElementById('validUntilInput');
    const currentStatus = '{{ $enrollment->status }}';
    const isExpiredDate = {{ $enrollment->isExpired() ? 'true' : 'false' }};

    // Show validity field when changing TO active from expired/completed/inactive
    // OR when the validity date itself is expired
    if (status === 'active' && (currentStatus !== 'active' || isExpiredDate)) {
        validitySection.style.display = 'block';
        validUntilInput.required = true;
    } else {
        validitySection.style.display = 'none';
        validUntilInput.required = false;
    }
}

// Run on page load
document.addEventListener('DOMContentLoaded', toggleValidityField);
</script>
@endpush
