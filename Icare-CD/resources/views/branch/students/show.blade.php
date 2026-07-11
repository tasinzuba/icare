@extends('layouts.branch')

@section('title', $enrollment->student->name)

@section('content')
<div class="mb-6">
    <a href="{{ route('branch.students.index') }}" class="text-indigo-600 hover:text-indigo-800">
        <i class="fas fa-arrow-left mr-2"></i> Back to Students
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Student Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-center mb-6">
                <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-indigo-600 text-3xl font-semibold">{{ substr($enrollment->student->name, 0, 1) }}</span>
                </div>
                <h2 class="text-xl font-bold text-gray-800">{{ $enrollment->student->name }}</h2>
                <p class="text-gray-500">{{ $enrollment->student->student_id }}</p>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $enrollment->student->email }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $enrollment->student->phone_number ?? 'N/A' }}</p>
                </div>
                @if($enrollment->initial_password)
                <div>
                    <p class="text-sm text-gray-500">Password</p>
                    <div class="flex items-center gap-2">
                        <code id="passwordText" class="font-medium bg-gray-100 px-2 py-1 rounded text-sm">
                            ********
                        </code>
                        <button type="button" onclick="togglePassword()" class="text-gray-400 hover:text-indigo-600 transition" title="Toggle password visibility">
                            <i id="passwordIcon" class="fas fa-eye text-sm"></i>
                        </button>
                        <button type="button" onclick="copyPassword()" class="text-gray-400 hover:text-indigo-600 transition" title="Copy password">
                            <i class="fas fa-copy text-sm"></i>
                        </button>
                    </div>
                </div>
                @endif
                <div>
                    <p class="text-sm text-gray-500">Status</p>
                    <span class="px-3 py-1 text-sm rounded-full bg-{{ $enrollment->status_color }}-100 text-{{ $enrollment->status_color }}-800">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Enrolled On</p>
                    <p class="font-medium">{{ $enrollment->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Enrolled By</p>
                    <p class="font-medium">{{ $enrollment->enrolledByUser->name ?? 'System' }}</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t space-y-2">
                <a href="{{ route('branch.students.edit', $enrollment) }}" class="block w-full text-center bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-edit mr-2"></i> Edit Details
                </a>
                <a href="{{ route('branch.students.renew.form', $enrollment) }}" class="block w-full text-center bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-sync-alt mr-2"></i> Renew Package
                </a>
                <button type="button" onclick="document.getElementById('resetPasswordModal').classList.remove('hidden')"
                        class="block w-full text-center bg-yellow-600 text-white py-2 rounded-lg hover:bg-yellow-700 transition">
                    <i class="fas fa-key mr-2"></i> Reset Password
                </button>
                <button type="button" onclick="document.getElementById('deleteModal').classList.remove('hidden')"
                        class="block w-full text-center bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash mr-2"></i> Delete Student
                </button>
            </div>

            {{-- Renewal History --}}
            @if(($enrollment->renewal_count ?? 0) > 0)
            <div class="mt-4 pt-4 border-t">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-history text-indigo-500"></i>
                    <span>Renewed {{ $enrollment->renewal_count }} {{ Str::plural('time', $enrollment->renewal_count) }}</span>
                </div>
                @if($enrollment->last_renewed_at)
                <p class="text-xs text-gray-500 mt-1">
                    Last renewed: {{ $enrollment->last_renewed_at->format('M d, Y') }}
                </p>
                @endif
                @if(count($enrollment->previously_completed_full_tests ?? []) > 0)
                <p class="text-xs text-blue-600 mt-1">
                    <i class="fas fa-lock mr-1"></i>{{ count($enrollment->previously_completed_full_tests) }} tests completed in previous packages
                </p>
                @endif
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Test Stats -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Test Package</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-blue-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $enrollment->full_tests_taken }}</p>
                    <p class="text-sm text-gray-600">Tests Taken</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">{{ $enrollment->remaining_full_tests }}</p>
                    <p class="text-sm text-gray-600">Remaining</p>
                </div>
                <div class="text-center p-4 bg-purple-50 rounded-lg">
                    <p class="text-2xl font-bold text-purple-600">{{ $enrollment->full_tests_allowed }}</p>
                    <p class="text-sm text-gray-600">Total Allowed</p>
                </div>
                <div class="text-center p-4 bg-yellow-50 rounded-lg">
                    <p class="text-2xl font-bold text-yellow-600">{{ $enrollment->days_remaining }}</p>
                    <p class="text-sm text-gray-600">Days Left</p>
                </div>
            </div>

            <!-- Evaluation Type & Test Restrictions -->
            <div class="mt-4 p-4 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg border border-indigo-100">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Evaluation:</span>
                        <span class="px-3 py-1 text-sm font-medium rounded-full bg-{{ $enrollment->evaluation_type_color }}-100 text-{{ $enrollment->evaluation_type_color }}-700">
                            @if($enrollment->evaluation_type === 'ai')
                                <i class="fas fa-robot mr-1"></i>
                            @elseif($enrollment->evaluation_type === 'human')
                                <i class="fas fa-user-tie mr-1"></i>
                            @else
                                <i class="fas fa-balance-scale mr-1"></i>
                            @endif
                            {{ $enrollment->evaluation_type_label }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Test Access:</span>
                        @if($enrollment->hasTestRestrictions())
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-amber-100 text-amber-700">
                                <i class="fas fa-lock mr-1"></i>
                                {{ $enrollment->allowed_tests_count }} Specific Tests
                            </span>
                        @else
                            <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-700">
                                <i class="fas fa-unlock mr-1"></i>
                                All Offline Tests
                            </span>
                        @endif
                    </div>
                </div>
                @if($enrollment->hasTestRestrictions() && is_array($enrollment->allowed_full_tests))
                    <div class="mt-3 pt-3 border-t border-indigo-200">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs text-gray-500">Assigned Tests:</p>
                            <button type="button" onclick="document.getElementById('manageTestsModal').classList.remove('hidden')"
                                    class="text-xs text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-cog mr-1"></i> Manage Tests
                            </button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @php
                                $testAssignments = $enrollment->testAssignments()->with('fullTest')->get()->keyBy('full_test_id');
                            @endphp
                            @foreach(\App\Models\FullTest::whereIn('id', $enrollment->allowed_full_tests)->get() as $test)
                                @php
                                    $assignment = $testAssignments->get($test->id);
                                    $isExpiringSoon = $assignment && $assignment->days_remaining <= 7;
                                @endphp
                                <span class="text-xs px-2 py-1 bg-white rounded border {{ $isExpiringSoon ? 'border-red-300' : 'border-gray-200' }} flex items-center gap-1">
                                    {{ $test->title }}
                                    @if($assignment)
                                        <span class="text-gray-400">•</span>
                                        <span class="{{ $isExpiringSoon ? 'text-red-600' : 'text-gray-400' }}">
                                            {{ $assignment->valid_until->format('M d') }}
                                        </span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="mt-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progress</span>
                    <span>{{ $enrollment->full_tests_taken }} / {{ $enrollment->full_tests_allowed }}</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div class="bg-indigo-600 h-3 rounded-full" style="width: {{ ($enrollment->full_tests_taken / max($enrollment->full_tests_allowed, 1)) * 100 }}%"></div>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-600">
                <p><strong>Valid:</strong> {{ $enrollment->valid_from->format('M d, Y') }} - {{ $enrollment->valid_until->format('M d, Y') }}</p>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Payment Details</h3>
                <span class="px-3 py-1 text-sm rounded-full bg-{{ $enrollment->payment_color }}-100 text-{{ $enrollment->payment_color }}-800">
                    {{ ucfirst($enrollment->payment_status) }}
                </span>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-xl font-bold text-gray-800">৳{{ number_format($enrollment->total_amount) }}</p>
                    <p class="text-sm text-gray-600">Total Amount</p>
                </div>
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-xl font-bold text-green-600">৳{{ number_format($enrollment->paid_amount) }}</p>
                    <p class="text-sm text-gray-600">Paid</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-xl font-bold text-red-600">৳{{ number_format($enrollment->due_amount) }}</p>
                    <p class="text-sm text-gray-600">Due</p>
                </div>
            </div>

            @if($enrollment->due_amount > 0)
            <form action="{{ route('branch.payments.store', $enrollment) }}" method="POST" class="mt-4 p-4 bg-yellow-50 rounded-lg">
                @csrf
                <h4 class="font-medium text-gray-800 mb-3">Record Payment</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="number" name="amount" placeholder="Amount" min="1" max="{{ $enrollment->due_amount }}" step="0.01" required
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <select name="method" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                    </select>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        <i class="fas fa-plus mr-1"></i> Add Payment
                    </button>
                </div>
            </form>
            @endif
        </div>

        <!-- Test History -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Test History</h3>
            @if($attempts->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Test Type</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($attempts as $attempt)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $attempt->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-800">{{ $attempt->testSet->section->name ?? 'Full Test' }}</td>
                            <td class="px-4 py-3">
                                @if($attempt->band_score)
                                <span class="text-lg font-bold text-indigo-600">{{ $attempt->band_score }}</span>
                                @else
                                <span class="text-gray-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($attempt->is_completed)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                                @else
                                <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">In Progress</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-4">No tests taken yet</p>
            @endif
        </div>

        <!-- Notes -->
        @if($enrollment->notes)
        <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Notes</h3>
            <p class="text-gray-600 whitespace-pre-line">{{ $enrollment->notes }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Reset Password Modal --}}
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-5 border-b bg-gradient-to-r from-yellow-50 to-orange-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-key text-yellow-600 mr-2"></i>Reset Password
                </h3>
                <button onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('branch.students.reset-password', $enrollment) }}" method="POST" id="resetPasswordForm">
            @csrf
            <div class="p-5">
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    This will generate a new random password for <strong>{{ $enrollment->student->name }}</strong>.
                    The new password will be displayed after generation. You can copy and share it with the student.
                </p>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    The student's current password will be replaced. Make sure to note down the new password.
                </div>

                {{-- Generated password display area --}}
                <div id="newPasswordDisplay" class="hidden mt-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                    <p class="text-sm text-green-700 mb-2"><i class="fas fa-check-circle mr-1"></i> New password generated:</p>
                    <div class="flex items-center gap-2">
                        <code id="newPasswordText" class="flex-1 text-lg font-mono bg-white px-3 py-2 rounded border"></code>
                        <button type="button" onclick="copyPassword()" class="px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Click the copy button to copy the password</p>
                </div>
            </div>

            <div class="p-5 border-t bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('resetPasswordModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit" id="resetPasswordBtn" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition">
                    <i class="fas fa-key mr-1"></i> Generate New Password
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Delete Student Modal --}}
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full">
        <div class="p-5 border-b bg-gradient-to-r from-red-50 to-pink-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>Delete Student
                </h3>
                <button onclick="document.getElementById('deleteModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <div class="p-5">
            <p class="text-sm text-gray-600 mb-4">
                Are you sure you want to delete <strong>{{ $enrollment->student->name }}</strong> ({{ $enrollment->student_id }})?
            </p>

            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-800 mb-4">
                <i class="fas fa-exclamation-circle mr-1"></i>
                <strong>Warning:</strong> This action will:
                <ul class="list-disc list-inside mt-2 ml-4">
                    <li>Remove the student's enrollment from your branch</li>
                    <li>Convert them to a public (online) user</li>
                    <li>They will no longer be able to access offline tests</li>
                </ul>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Type <strong>DELETE</strong> to confirm:
                </label>
                <input type="text" id="deleteConfirmInput"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                       placeholder="Type DELETE here">
            </div>
        </div>

        <div class="p-5 border-t bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="document.getElementById('deleteModal').classList.add('hidden')"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                Cancel
            </button>
            <form action="{{ route('branch.students.destroy', $enrollment) }}" method="POST" id="deleteForm" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" id="deleteBtn" disabled
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-trash mr-1"></i> Delete Student
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Password toggle/copy
    let passwordVisible = false;
    let storedPassword = @json($enrollment->initial_password);

    function togglePassword() {
        const el = document.getElementById('passwordText');
        const icon = document.getElementById('passwordIcon');
        if (!storedPassword) return;
        passwordVisible = !passwordVisible;
        el.textContent = passwordVisible ? storedPassword : '********';
        icon.className = passwordVisible ? 'fas fa-eye-slash text-sm' : 'fas fa-eye text-sm';
    }

    function copyPassword() {
        if (!storedPassword) return;
        navigator.clipboard.writeText(storedPassword).then(() => {
            const btn = event.currentTarget;
            const icon = btn.querySelector('i');
            icon.className = 'fas fa-check text-sm text-green-500';
            setTimeout(() => { icon.className = 'fas fa-copy text-sm'; }, 1500);
        });
    }

    // Delete confirmation
    document.getElementById('deleteConfirmInput').addEventListener('input', function() {
        const deleteBtn = document.getElementById('deleteBtn');
        deleteBtn.disabled = this.value !== 'DELETE';
    });

    // Reset Password Form - AJAX submission
    document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const btn = document.getElementById('resetPasswordBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Generating...';

        fetch(this.action, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('newPasswordText').textContent = data.password;
                document.getElementById('newPasswordDisplay').classList.remove('hidden');
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Password Reset';
                btn.classList.remove('bg-yellow-600', 'hover:bg-yellow-700');
                btn.classList.add('bg-green-600', 'cursor-default');

                // Update the stored password for toggle/copy
                storedPassword = data.password;
                if (passwordVisible) {
                    document.getElementById('passwordText').textContent = data.password;
                }
            } else {
                alert(data.message || 'Failed to reset password');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        })
        .catch(error => {
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    });

    // Copy password to clipboard
    function copyPassword() {
        const password = document.getElementById('newPasswordText').textContent;
        navigator.clipboard.writeText(password).then(function() {
            // Show temporary success
            const btn = event.target.closest('button');
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        });
    }
</script>
@endpush

{{-- Manage Tests Modal --}}
<div id="manageTestsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
        <div class="p-5 border-b bg-gradient-to-r from-indigo-50 to-purple-50">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-800">
                    <i class="fas fa-cog text-indigo-600 mr-2"></i>Manage Test Assignments
                </h3>
                <button onclick="document.getElementById('manageTestsModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form action="{{ route('branch.students.update-tests', $enrollment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-5 overflow-y-auto max-h-[60vh]">
                <p class="text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                    Remove tests or extend validity dates. Completed tests cannot be modified.
                </p>

                <div class="space-y-3">
                    @php
                        $testAssignments = $enrollment->testAssignments()->with('fullTest')->get();
                        $completedTestIds = $enrollment->getCurrentCompletedFullTestIds();
                    @endphp

                    @forelse($testAssignments as $assignment)
                        @php
                            $isCompleted = in_array($assignment->full_test_id, $completedTestIds);
                        @endphp
                        <div class="flex items-center gap-4 p-3 border rounded-lg {{ $isCompleted ? 'bg-gray-50 opacity-60' : 'bg-white' }}">
                            <div class="flex-shrink-0">
                                @if($isCompleted)
                                    <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                @else
                                    <input type="checkbox" name="keep_tests[]" value="{{ $assignment->id }}"
                                           checked class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-800">{{ $assignment->fullTest->title ?? 'Unknown Test' }}</p>
                                <p class="text-xs text-gray-500">
                                    Assigned: {{ $assignment->assigned_at->format('M d, Y') }}
                                    @if($isCompleted)
                                        <span class="text-green-600 ml-2">• Completed</span>
                                    @endif
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                @if(!$isCompleted)
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-gray-500">Expires:</label>
                                        <input type="date" name="validity[{{ $assignment->id }}]"
                                               value="{{ $assignment->valid_until->format('Y-m-d') }}"
                                               min="{{ now()->format('Y-m-d') }}"
                                               class="text-sm px-2 py-1 border border-gray-300 rounded focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                @else
                                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full">
                                        Completed
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-folder-open text-4xl mb-2"></i>
                            <p>No test assignments found</p>
                        </div>
                    @endforelse
                </div>

                @if($testAssignments->count() > 0)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        <strong>Warning:</strong> Unchecking a test will remove it from the student's allowed tests.
                        This cannot be undone (you'll need to renew to add it back).
                    </p>
                </div>
                @endif
            </div>

            <div class="p-5 border-t bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('manageTestsModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
