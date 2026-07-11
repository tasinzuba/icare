<x-admin-layout>
    <x-slot:title>AI Credits - {{ $branch->name }}</x-slot>

    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-4 mb-6">
            <a href="{{ route('admin.branches.show', $branch) }}" class="text-gray-600 hover:text-gray-900">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">AI Credits - {{ $branch->name }}</h1>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 p-4 border border-green-200">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="ml-3 text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <!-- Credit Balance Card -->
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl shadow-xl p-8 mb-8 text-white">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <!-- Balance -->
            <div class="lg:col-span-1">
                <p class="text-purple-200 text-sm font-medium">Current Balance</p>
                <p class="text-5xl font-bold mt-2">{{ number_format($creditSummary['balance'], 2) }}</p>
                <p class="text-purple-200 text-sm mt-1">≈ ৳{{ number_format($creditSummary['balance_in_bdt'], 0) }} BDT</p>
            </div>

            <!-- Stats -->
            <div class="lg:col-span-2 grid grid-cols-3 gap-4">
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold">{{ number_format($creditSummary['total_purchased'], 2) }}</p>
                    <p class="text-xs text-purple-200 mt-1">Total Purchased</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold">{{ number_format($creditSummary['total_used'], 2) }}</p>
                    <p class="text-xs text-purple-200 mt-1">Total Used</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-4 text-center">
                    <p class="text-3xl font-bold">{{ $creditSummary['usage_this_month']['total_evaluations'] }}</p>
                    <p class="text-xs text-purple-200 mt-1">Evaluations (Month)</p>
                </div>
            </div>

            <!-- Add Credits Form -->
            <div class="lg:col-span-1">
                <form action="{{ route('admin.branches.add-credits', $branch) }}" method="POST" class="bg-white/10 backdrop-blur rounded-xl p-4">
                    @csrf
                    <label class="block text-sm font-medium text-purple-200 mb-2">Add Credits</label>
                    <div class="flex gap-2">
                        <input type="number" name="amount" step="0.01" min="0.01" max="10000" required
                               placeholder="Amount"
                               class="flex-1 rounded-lg bg-white/20 border-0 text-white placeholder-purple-300 focus:ring-2 focus:ring-white">
                        <button type="submit" class="px-4 py-2 bg-white text-purple-600 font-semibold rounded-lg hover:bg-purple-50 transition">
                            Add
                        </button>
                    </div>
                    <input type="text" name="description" placeholder="Note (optional)"
                           class="mt-2 w-full rounded-lg bg-white/20 border-0 text-white placeholder-purple-300 text-sm focus:ring-2 focus:ring-white">
                </form>
            </div>
        </div>
    </div>

    <!-- Usage Stats & Rates -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- This Month Usage -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">This Month Usage</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Writing</p>
                            <p class="text-xs text-gray-500">{{ $creditSummary['usage_this_month']['writing_evaluations'] }} evaluations</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($creditSummary['usage_this_month']['writing_cost'], 2) }}</p>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Speaking</p>
                            <p class="text-xs text-gray-500">{{ $creditSummary['usage_this_month']['speaking_evaluations'] }} evaluations</p>
                        </div>
                    </div>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($creditSummary['usage_this_month']['speaking_cost'], 2) }}</p>
                </div>
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Total Cost</p>
                        <p class="text-xl font-bold text-indigo-600">{{ number_format($creditSummary['usage_this_month']['total_cost'], 2) }} credits</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Usage -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Today's Usage</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Writing Evaluations</span>
                    <span class="font-semibold">{{ $creditSummary['usage_today']['writing_evaluations'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Speaking Evaluations</span>
                    <span class="font-semibold">{{ $creditSummary['usage_today']['speaking_evaluations'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Total Evaluations</span>
                    <span class="font-semibold">{{ $creditSummary['usage_today']['total_evaluations'] }}</span>
                </div>
                <div class="pt-4 border-t border-gray-200">
                    <div class="flex items-center justify-between">
                        <p class="font-medium text-gray-700">Today's Cost</p>
                        <p class="text-xl font-bold text-indigo-600">{{ number_format($creditSummary['usage_today']['total_cost'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Current Rates -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Current Rates</h3>
            <div class="space-y-4">
                <div class="p-4 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-amber-800">Writing Evaluation</p>
                            <p class="text-xs text-amber-600">Per task (Task 1 or 2)</p>
                        </div>
                        <p class="text-2xl font-bold text-amber-700">{{ $creditSummary['rates']['writing'] }}</p>
                    </div>
                </div>
                <div class="p-4 bg-purple-50 rounded-lg border border-purple-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-purple-800">Speaking Evaluation</p>
                            <p class="text-xs text-purple-600">Including transcription</p>
                        </div>
                        <p class="text-2xl font-bold text-purple-700">{{ $creditSummary['rates']['speaking'] }}</p>
                    </div>
                </div>
                <div class="text-xs text-gray-500 mt-4">
                    <p>1 Credit = 1 USD = ~৳120 BDT</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-sm font-semibold text-gray-700">Recent Transactions</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Reason</th>
                        <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Balance After</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($recentTransactions as $transaction)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($transaction->type === 'credit')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Credit
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-red-100 text-red-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                    Debit
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium
                                {{ $transaction->reason === 'admin_topup' ? 'bg-blue-100 text-blue-800' :
                                   ($transaction->reason === 'writing_evaluation' ? 'bg-amber-100 text-amber-800' :
                                   ($transaction->reason === 'speaking_evaluation' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $transaction->reason_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                            {{ $transaction->description ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 4) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                            {{ number_format($transaction->balance_after, 4) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No transactions yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-admin-layout>
