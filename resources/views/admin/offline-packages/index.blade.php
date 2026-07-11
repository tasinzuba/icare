<x-admin-layout>
    <x-slot:title>Offline Student Packages</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Offline Student Packages</h2>
                    <p class="mt-1 text-sm text-gray-600">Manage test packages for branch/offline students</p>
                </div>
                <a href="{{ route('admin.offline-packages.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Create Package
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-box text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Packages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalPackages }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Packages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activePackages }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                        <i class="fas fa-globe text-purple-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Global Packages</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $globalPackages }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <form action="{{ route('admin.offline-packages.index') }}" method="GET" class="flex flex-wrap gap-4 items-end">
                <div>
                    <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Branch</label>
                    <select name="branch_id" id="branch_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Packages</option>
                        <option value="global" {{ request('branch_id') === 'global' ? 'selected' : '' }}>Global Only</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filter
                    </button>
                    @if(request()->hasAny(['branch_id', 'status']))
                        <a href="{{ route('admin.offline-packages.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-times mr-2"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Packages Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($packages as $package)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 {{ !$package->is_active ? 'opacity-60' : '' }}">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $package->name }}</h3>
                        @if($package->isGlobal())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                <i class="fas fa-globe mr-1"></i>Global
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                <i class="fas fa-building mr-1"></i>{{ $package->branch->name }}
                            </span>
                        @endif
                    </div>

                    @if($package->description)
                        <p class="text-sm text-gray-500 mb-4">{{ Str::limit($package->description, 80) }}</p>
                    @endif

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fas fa-clipboard-list mr-2"></i>Full Tests</span>
                            <span class="font-semibold text-gray-900">{{ $package->full_tests_allowed }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fas fa-file-alt mr-2"></i>Section Tests</span>
                            <span class="font-semibold text-gray-900">{{ $package->section_tests_allowed }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500"><i class="fas fa-calendar-alt mr-2"></i>Validity</span>
                            <span class="font-semibold text-gray-900">{{ $package->validity_days }} days</span>
                        </div>
                    </div>

                    <div class="mb-6 pt-4 border-t border-gray-200">
                        <span class="text-3xl font-bold text-gray-900">{{ number_format($package->price, 0) }}</span>
                        <span class="text-gray-500 ml-1">BDT</span>
                    </div>

                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $package->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $package->is_active ? 'Active' : 'Inactive' }}
                        </span>

                        <div class="flex items-center gap-3">
                            @if($package->isGlobal())
                                <a href="{{ route('admin.offline-packages.branch-pricing', $package) }}"
                                   class="text-purple-600 hover:text-purple-900 text-sm" title="Branch Pricing">
                                    <i class="fas fa-tags"></i>
                                </a>
                            @endif

                            <a href="{{ route('admin.offline-packages.edit', $package) }}"
                               class="text-indigo-600 hover:text-indigo-900 text-sm">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('admin.offline-packages.toggle-status', $package) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-yellow-600 hover:text-yellow-900 text-sm" title="{{ $package->is_active ? 'Disable' : 'Enable' }}">
                                    <i class="fas fa-{{ $package->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>
                            </form>

                            <form action="{{ route('admin.offline-packages.destroy', $package) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Are you sure you want to delete this package?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-sm">
                <i class="fas fa-box-open text-4xl text-gray-400 mb-3"></i>
                <h3 class="text-sm font-medium text-gray-900">No packages found</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by creating a new offline package.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.offline-packages.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create Package
                    </a>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($packages->hasPages())
        <div class="bg-white px-4 py-3 rounded-lg shadow-sm">
            {{ $packages->links() }}
        </div>
        @endif
    </div>
</x-admin-layout>
