@extends('layouts.branch')

@section('title', 'Payments')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-800">Payments</h1>
    <p class="text-gray-600">Manage student payments and dues</p>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl shadow-md p-4">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-full">
                <i class="fas fa-check-circle text-green-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-2xl font-bold text-green-600">{{ $stats['paid_count'] }}</p>
                <p class="text-sm text-gray-600">Fully Paid</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-4">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-full">
                <i class="fas fa-exclamation-circle text-yellow-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['partial_count'] }}</p>
                <p class="text-sm text-gray-600">Partial</p>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-md p-4">
        <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-full">
                <i class="fas fa-times-circle text-red-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-2xl font-bold text-red-600">{{ $stats['pending_count'] }}</p>
                <p class="text-sm text-gray-600">Pending</p>
            </div>
        </div>
    </div>
    <a href="{{ route('branch.payments.due') }}" class="bg-white rounded-xl shadow-md p-4 hover:shadow-lg transition">
        <div class="flex items-center">
            <div class="p-3 bg-purple-100 rounded-full">
                <i class="fas fa-money-bill text-purple-600"></i>
            </div>
            <div class="ml-3">
                <p class="text-2xl font-bold text-purple-600">৳{{ number_format($stats['pending_amount']) }}</p>
                <p class="text-sm text-gray-600">Total Due</p>
            </div>
        </div>
    </a>
</div>

<!-- Filter -->
<div class="bg-white rounded-xl shadow-md p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            <option value="">All Status</option>
            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <button type="submit" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200">
            <i class="fas fa-filter mr-2"></i> Filter
        </button>
    </form>
</div>

<!-- Payments Table -->
<div class="bg-white rounded-xl shadow-md overflow-hidden">
    <table class="w-full">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Paid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($enrollments as $enrollment)
            @if($enrollment->student)
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                    <p class="font-medium text-gray-800">{{ $enrollment->student->name }}</p>
                    <p class="text-sm text-gray-500">{{ $enrollment->student_id }}</p>
                </td>
                <td class="px-6 py-4 font-medium">৳{{ number_format($enrollment->total_amount) }}</td>
                <td class="px-6 py-4 text-green-600 font-medium">৳{{ number_format($enrollment->paid_amount) }}</td>
                <td class="px-6 py-4 text-red-600 font-medium">৳{{ number_format($enrollment->due_amount) }}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $enrollment->payment_color }}-100 text-{{ $enrollment->payment_color }}-800">
                        {{ ucfirst($enrollment->payment_status) }}
                    </span>
                </td>
                <td class="px-6 py-4 text-right">
                    @if($enrollment->due_amount > 0)
                    <button onclick="openPaymentModal({{ $enrollment->id }}, {{ $enrollment->due_amount }})"
                            class="text-green-600 hover:text-green-800">
                        <i class="fas fa-plus-circle mr-1"></i> Add Payment
                    </button>
                    @else
                    <span class="text-gray-400">Paid</span>
                    @endif
                </td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <p>No payments found</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $enrollments->links() }}
</div>

<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Record Payment</h3>
        <form id="paymentForm" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount (৳)</label>
                    <input type="number" name="amount" id="paymentAmount" min="1" step="0.01" required
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Max: ৳<span id="maxAmount">0</span></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Method</label>
                    <select name="method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="online">Online</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
            </div>
            <div class="flex justify-end space-x-4 mt-6">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-check mr-2"></i> Record Payment
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openPaymentModal(enrollmentId, maxAmount) {
    document.getElementById('paymentForm').action = '/branch-admin/payments/' + enrollmentId;
    document.getElementById('paymentAmount').max = maxAmount;
    document.getElementById('maxAmount').textContent = maxAmount.toLocaleString();
    document.getElementById('paymentModal').classList.remove('hidden');
    document.getElementById('paymentModal').classList.add('flex');
}

function closePaymentModal() {
    document.getElementById('paymentModal').classList.add('hidden');
    document.getElementById('paymentModal').classList.remove('flex');
}
</script>
@endpush
@endsection
