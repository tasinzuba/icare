@extends('layouts.branch')

@section('title', 'Students')

@section('content')
<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Students</h1>
        <p class="text-sm text-gray-500 mt-0.5">Manage offline student enrollments</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('branch.students.import.form') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition">
            <i class="fas fa-file-import text-xs"></i> Bulk Import
        </a>
        <a href="{{ route('branch.students.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-800 text-sm font-medium text-white rounded-lg hover:bg-blue-900 transition">
            <i class="fas fa-plus text-xs"></i> New Student
        </a>
    </div>
</div>

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Total</p>
            <p class="text-lg font-bold text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="w-8 h-8 bg-gray-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-users text-gray-400 text-xs"></i>
        </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Active</p>
            <p class="text-lg font-bold text-emerald-600">{{ $stats['active'] }}</p>
        </div>
        <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-check-circle text-emerald-500 text-xs"></i>
        </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Expired</p>
            <p class="text-lg font-bold text-red-600">{{ $stats['expired'] }}</p>
        </div>
        <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-clock text-red-400 text-xs"></i>
        </div>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 flex items-center justify-between">
        <div>
            <p class="text-[11px] text-gray-400 font-medium">Payment Due</p>
            <p class="text-lg font-bold text-amber-600">{{ $stats['pending_payment'] }}</p>
        </div>
        <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
            <i class="fas fa-exclamation-circle text-amber-400 text-xs"></i>
        </div>
    </div>
</div>

@if(session('import_results'))
{{-- Import Results --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-6">
    <div class="flex items-center justify-between mb-3">
        <h3 class="text-sm font-semibold text-gray-800"><i class="fas fa-check-circle text-emerald-500 mr-2"></i>Import Results</h3>
        <button onclick="this.closest('.bg-white').remove()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times text-sm"></i>
        </button>
    </div>
    @php $results = session('import_results'); @endphp
    <div class="grid grid-cols-3 gap-3 mb-3">
        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 text-center">
            <p class="text-xl font-bold text-emerald-600">{{ $results['success'] ?? 0 }}</p>
            <p class="text-[11px] text-emerald-700">Imported</p>
        </div>
        <div class="bg-amber-50 border border-amber-100 rounded-lg p-3 text-center">
            <p class="text-xl font-bold text-amber-600">{{ $results['skipped'] ?? 0 }}</p>
            <p class="text-[11px] text-amber-700">Skipped</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
            <p class="text-xl font-bold text-red-600">{{ count($results['errors'] ?? []) }}</p>
            <p class="text-[11px] text-red-700">Errors</p>
        </div>
    </div>

    @if(!empty($results['errors']))
    <details class="mb-3">
        <summary class="cursor-pointer text-red-600 text-xs font-medium">View Errors</summary>
        <div class="mt-2 max-h-40 overflow-y-auto">
            <table class="w-full text-xs">
                <thead class="bg-red-50">
                    <tr>
                        <th class="px-2 py-1 text-left">Row</th>
                        <th class="px-2 py-1 text-left">Error</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results['errors'] as $error)
                    <tr class="border-t border-red-100">
                        <td class="px-2 py-1">{{ $error['row'] }}</td>
                        <td class="px-2 py-1 text-red-600">{{ $error['message'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
    @endif

    @if(!empty($results['imported']))
    <a href="{{ route('branch.students.import.export-results') }}"
       class="inline-flex items-center text-blue-600 hover:text-blue-800 text-xs font-medium">
        <i class="fas fa-download mr-1.5"></i> Download Credentials CSV
    </a>
    @endif
</div>
@endif

{{-- Filters --}}
<div class="bg-white border border-gray-200 rounded-xl p-4 mb-4">
    <form method="GET" class="flex flex-wrap gap-3">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, phone or ID..."
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none">
        </div>
        <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none">
            <option value="">All Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
        </select>
        <select name="payment_status" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none">
            <option value="">All Payments</option>
            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <select name="batch_id" class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-blue-500 outline-none">
            <option value="">All Batches</option>
            @foreach($batches as $batch)
                <option value="{{ $batch->id }}" {{ request('batch_id') == $batch->id ? 'selected' : '' }}>{{ $batch->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 bg-gray-50 border border-gray-200 rounded-lg hover:bg-gray-100 transition">
            <i class="fas fa-search text-[10px]"></i> Search
        </button>
        @if(request()->hasAny(['search', 'status', 'payment_status', 'batch_id']))
        <a href="{{ route('branch.students.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <i class="fas fa-times text-[10px]"></i> Clear
        </a>
        @endif
    </form>
</div>

{{-- Students Table --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50/50">
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Tests</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Validity</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Enrolled By</th>
                    <th class="px-4 py-3 text-right text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($enrollments as $enrollment)
                @if($enrollment->student)
                <tr class="hover:bg-gray-50/50 transition-colors">
                    {{-- Student --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-gray-600 font-semibold text-xs">{{ strtoupper(substr($enrollment->student->name, 0, 1)) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $enrollment->student->name }}</p>
                                <p class="text-[11px] text-gray-400">{{ $enrollment->student_id }}</p>
                                <p class="text-[11px] text-gray-400 truncate">{{ $enrollment->student->email }}</p>
                                @if($enrollment->batch)
                                    <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-medium rounded bg-indigo-100 text-indigo-700 mt-0.5">
                                        <i class="fas fa-users mr-1"></i>{{ $enrollment->batch->name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    {{-- Tests --}}
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-800">{{ $enrollment->full_tests_taken }}</span>
                            <span class="text-[11px] text-gray-400">/</span>
                            <span class="text-sm text-gray-500">{{ $enrollment->full_tests_allowed }}</span>
                        </div>
                        <div class="w-20 bg-gray-100 rounded-full h-1.5 mt-1.5">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ min(($enrollment->full_tests_taken / max($enrollment->full_tests_allowed, 1)) * 100, 100) }}%"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1">Sec: {{ $enrollment->section_tests_taken }}/{{ $enrollment->section_tests_allowed }}</p>
                    </td>

                    {{-- Validity --}}
                    <td class="px-4 py-3">
                        <p class="text-sm text-gray-800">{{ $enrollment->valid_until->format('M d, Y') }}</p>
                        @if($enrollment->days_remaining > 0)
                        <p class="text-[11px] text-emerald-600 font-medium mt-0.5">{{ $enrollment->days_remaining }} days left</p>
                        @else
                        <p class="text-[11px] text-red-500 font-medium mt-0.5">Expired</p>
                        @endif
                    </td>

                    {{-- Payment --}}
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full
                            {{ $enrollment->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $enrollment->payment_status === 'partial' ? 'bg-amber-50 text-amber-700' : '' }}
                            {{ $enrollment->payment_status === 'pending' ? 'bg-red-50 text-red-600' : '' }}
                            {{ $enrollment->payment_status === 'refunded' ? 'bg-gray-100 text-gray-500' : '' }}">
                            {{ ucfirst($enrollment->payment_status) }}
                        </span>
                        @if($enrollment->due_amount > 0)
                        <p class="text-[11px] text-red-500 mt-1">Due: ৳{{ number_format($enrollment->due_amount) }}</p>
                        @endif
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 text-[10px] font-medium rounded-full
                            {{ $enrollment->status === 'active' ? 'bg-emerald-50 text-emerald-700' : '' }}
                            {{ $enrollment->status === 'expired' ? 'bg-red-50 text-red-600' : '' }}
                            {{ $enrollment->status === 'completed' ? 'bg-blue-50 text-blue-600' : '' }}
                            {{ $enrollment->status === 'inactive' ? 'bg-gray-100 text-gray-500' : '' }}">
                            {{ ucfirst($enrollment->status) }}
                        </span>
                    </td>

                    {{-- Enrolled By --}}
                    <td class="px-4 py-3">
                        @if($enrollment->enrolledByUser)
                        <p class="text-sm text-gray-700">{{ $enrollment->enrolledByUser->name }}</p>
                        <p class="text-[10px] text-gray-400">{{ $enrollment->created_at->format('M d, Y') }}</p>
                        @else
                        <span class="text-[11px] text-gray-400">--</span>
                        @endif
                    </td>

                    {{-- Actions --}}
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="{{ route('branch.students.show', $enrollment) }}" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('branch.students.edit', $enrollment) }}" class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <button type="button" onclick="confirmDelete({{ $enrollment->id }}, '{{ addslashes($enrollment->student->name) }}')"
                                    class="w-7 h-7 flex items-center justify-center rounded-md text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="Delete">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endif
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <div class="text-gray-400">
                            <i class="fas fa-user-graduate text-3xl mb-3"></i>
                            <p class="text-sm font-medium text-gray-500">No students found</p>
                            <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filters</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($enrollments->hasPages())
<div class="mt-4">
    {{ $enrollments->withQueryString()->links() }}
</div>
@endif

{{-- Delete Modal --}}
<div id="quickDeleteModal" class="fixed inset-0 bg-black/40 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-sm w-full">
        <div class="p-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-900">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>Delete Student
                </h3>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <div class="p-5">
            <p class="text-sm text-gray-600 mb-3">
                Are you sure you want to delete <strong id="deleteStudentName" class="text-gray-900"></strong>?
            </p>
            <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-xs text-red-700">
                <i class="fas fa-info-circle mr-1"></i>
                This will remove the enrollment and convert them to a public user.
            </div>
        </div>

        <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
            <button type="button" onclick="closeDeleteModal()"
                    class="px-3.5 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 transition">
                Cancel
            </button>
            <form id="quickDeleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3.5 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash mr-1 text-xs"></i> Delete
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function confirmDelete(enrollmentId, studentName) {
        document.getElementById('deleteStudentName').textContent = studentName;
        document.getElementById('quickDeleteForm').action = '{{ route("branch.students.destroy", "__ID__") }}'.replace('__ID__', enrollmentId);
        document.getElementById('quickDeleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('quickDeleteModal').classList.add('hidden');
    }

    // Close modal on backdrop click
    document.getElementById('quickDeleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });
</script>
@endpush
@endsection
