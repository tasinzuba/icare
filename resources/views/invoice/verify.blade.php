<x-student-layout>
    <x-slot:title>Verify Invoice</x-slot>

    <section class="px-4 sm:px-6 lg:px-8 py-12">
        <div class="max-w-2xl mx-auto">
            <div class="glass rounded-2xl p-8 text-center">
                @if($isValid)
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-green-500 to-emerald-500 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-check text-white text-3xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-4">Invoice Verified</h1>
                    <p class="text-gray-300 mb-6">This is a valid invoice from CD IELTS Master</p>
                    
                    <div class="glass rounded-xl p-6 text-left mb-6">
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Invoice ID:</span>
                                <span class="text-white font-medium">{{ $transaction->transaction_id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Amount:</span>
                                <span class="text-white font-medium">৳{{ number_format($transaction->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Date:</span>
                                <span class="text-white font-medium">{{ $transaction->created_at->format('d M Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Status:</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400 border border-green-500/30">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Paid
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-500 to-rose-500 flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-times text-white text-3xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-4">Invalid Invoice</h1>
                    <p class="text-gray-300">This invoice could not be verified or is still pending.</p>
                @endif
                
                <a href="{{ route('home') }}" class="inline-flex items-center mt-6 text-purple-400 hover:text-purple-300">
                    <i class="fas fa-home mr-2"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </section>
</x-student-layout>