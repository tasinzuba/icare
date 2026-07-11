<x-admin-layout>
    <x-slot:title>Branch Pricing - {{ $offlinePackage->name }}</x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">Branch-Specific Pricing</h2>
                        <p class="mt-1 text-sm text-gray-600">Configure different prices for each branch for "{{ $offlinePackage->name }}"</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                        Base Price: {{ number_format($offlinePackage->price, 0) }} BDT
                    </span>
                </div>
            </div>

            <!-- Package Info -->
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-sm text-gray-500">Full Tests</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $offlinePackage->full_tests_allowed }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Section Tests</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $offlinePackage->section_tests_allowed }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Validity</p>
                        <p class="text-lg font-semibold text-gray-900">{{ $offlinePackage->validity_days }} days</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Base Price</p>
                        <p class="text-lg font-semibold text-gray-900">{{ number_format($offlinePackage->price, 0) }} BDT</p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.offline-packages.update-branch-pricing', $offlinePackage) }}" method="POST" class="p-6">
                @csrf

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                        Leave price empty to use the base price. Uncheck "Available" to hide this package from a specific branch.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Branch
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Custom Price (BDT)
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Available
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Effective Price
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($branches as $branch)
                                @php
                                    $existingPrice = $existingPrices->get($branch->id);
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="hidden" name="branch_prices[{{ $loop->index }}][branch_id]" value="{{ $branch->id }}">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr($branch->code, 0, 2)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $branch->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $branch->code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="number"
                                               name="branch_prices[{{ $loop->index }}][custom_price]"
                                               value="{{ $existingPrice?->custom_price }}"
                                               min="0"
                                               step="0.01"
                                               placeholder="{{ number_format($offlinePackage->price, 0) }}"
                                               class="branch-price w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                               data-branch-index="{{ $loop->index }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="hidden" name="branch_prices[{{ $loop->index }}][is_available]" value="0">
                                        <input type="checkbox"
                                               name="branch_prices[{{ $loop->index }}][is_available]"
                                               value="1"
                                               {{ ($existingPrice === null || $existingPrice->is_available) ? 'checked' : '' }}
                                               class="branch-available h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                               data-branch-index="{{ $loop->index }}">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="effective-price text-sm font-medium text-gray-900" data-branch-index="{{ $loop->index }}">
                                            {{ number_format($existingPrice?->custom_price ?? $offlinePackage->price, 0) }} BDT
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <a href="{{ route('admin.offline-packages.edit', $offlinePackage) }}"
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Save Branch Pricing
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        const basePrice = {{ $offlinePackage->price }};

        // Update effective price display
        document.querySelectorAll('.branch-price').forEach(input => {
            input.addEventListener('input', function() {
                const index = this.dataset.branchIndex;
                const effectiveSpan = document.querySelector(`.effective-price[data-branch-index="${index}"]`);
                const price = parseFloat(this.value) || basePrice;
                effectiveSpan.textContent = price.toLocaleString() + ' BDT';
            });
        });

        // Update availability visual feedback
        document.querySelectorAll('.branch-available').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const row = this.closest('tr');
                if (this.checked) {
                    row.classList.remove('bg-red-50', 'opacity-50');
                } else {
                    row.classList.add('bg-red-50', 'opacity-50');
                }
            });

            // Initial state
            if (!checkbox.checked) {
                checkbox.closest('tr').classList.add('bg-red-50', 'opacity-50');
            }
        });
    </script>
    @endpush
</x-admin-layout>
