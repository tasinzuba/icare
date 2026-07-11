<x-admin-layout>
    <x-slot:title>Test Categories</x-slot>

    @php
        $totalCategories = $categories->total();
        $activeCount = $categories->getCollection()->where('is_active', true)->count();
        $totalTestSets = $categories->getCollection()->sum('test_sets_count');
        $totalActiveTestSets = $categories->getCollection()->sum('active_test_sets_count');
    @endphp

    {{-- Page Header --}}
    <div class="mb-6 rounded-xl bg-white p-6 shadow-sm border border-slate-200 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Test Categories</h1>
            <p class="mt-1 text-sm text-slate-600">Organize test sets into categories for better navigation</p>
        </div>
        <a href="{{ route('admin.test-categories.create') }}"
           class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors shadow-sm">
            <i class="fas fa-plus mr-2"></i> Add New Category
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="mb-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $stats = [
                ['label' => 'Total Categories', 'value' => $totalCategories, 'color' => 'indigo', 'icon' => 'fa-folder-tree'],
                ['label' => 'Active', 'value' => $activeCount, 'color' => 'emerald', 'icon' => 'fa-check-circle'],
                ['label' => 'Total Test Sets', 'value' => $totalTestSets, 'color' => 'blue', 'icon' => 'fa-layer-group'],
                ['label' => 'Active Test Sets', 'value' => $totalActiveTestSets, 'color' => 'purple', 'icon' => 'fa-play-circle'],
            ];
        @endphp
        @foreach($stats as $stat)
            <div class="rounded-xl bg-white p-5 shadow-sm border border-slate-200 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-slate-600 truncate">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($stat['value']) }}</p>
                    </div>
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-{{ $stat['color'] }}-100 flex-shrink-0">
                        <i class="fas {{ $stat['icon'] }} text-{{ $stat['color'] }}-600"></i>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Categories Table --}}
    <div class="rounded-xl bg-white shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                <i class="fas fa-list text-slate-400"></i>
                <span>All Categories</span>
                <span class="ml-1 text-xs text-slate-500 font-normal">({{ $totalCategories }})</span>
            </div>
            <p class="text-xs text-slate-500 flex items-center gap-1">
                <i class="fas fa-grip-vertical text-slate-400"></i>
                Drag to reorder
            </p>
        </div>

        @if($categories->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="w-12 px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500"></th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Icon</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Name</th>
                        <th class="px-6 py-3 text-center text-[11px] font-semibold uppercase tracking-wider text-slate-500">Test Sets</th>
                        <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-[11px] font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white" id="sortable-categories">
                    @foreach($categories as $category)
                        <tr class="hover:bg-slate-50 transition-colors group" data-id="{{ $category->id }}">
                            <td class="px-4 py-4">
                                <span class="cursor-move text-slate-300 group-hover:text-slate-500 transition-colors">
                                    <i class="fas fa-grip-vertical"></i>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center w-10 h-10 rounded-lg" style="background-color: {{ $category->color }}1a;">
                                    @php
                                        $iconMap = [
                                            'academic-cap' => 'fa-graduation-cap',
                                            'briefcase' => 'fa-briefcase',
                                            'clipboard-list' => 'fa-clipboard-list',
                                            'book-open' => 'fa-book-open',
                                            'light-bulb' => 'fa-lightbulb',
                                        ];
                                        $faIcon = $iconMap[$category->icon] ?? 'fa-circle-question';
                                    @endphp
                                    <i class="fas {{ $faIcon }}" style="color: {{ $category->color }};"></i>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-slate-900">{{ $category->name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $category->slug }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                    <span class="font-semibold text-slate-900">{{ $category->active_test_sets_count }}</span>
                                    <span class="mx-1 text-slate-400">/</span>
                                    <span>{{ $category->test_sets_count }}</span>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($category->is_active)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium bg-emerald-100 text-emerald-800">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3 text-sm">
                                    <a href="{{ route('admin.test-categories.show', $category) }}"
                                       class="text-slate-500 hover:text-indigo-600 transition-colors" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.test-categories.edit', $category) }}"
                                       class="text-slate-500 hover:text-yellow-600 transition-colors" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <a href="{{ route('admin.test-categories.manage-test-sets', $category) }}"
                                       class="text-slate-500 hover:text-emerald-600 transition-colors" title="Manage Test Sets">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                    <form action="{{ route('admin.test-categories.toggle-status', $category) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="{{ $category->is_active ? 'text-slate-500 hover:text-amber-600' : 'text-slate-500 hover:text-emerald-600' }} transition-colors"
                                                title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas {{ $category->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                        </button>
                                    </form>
                                    @if($category->test_sets_count == 0)
                                        <form action="{{ route('admin.test-categories.destroy', $category) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Delete this category? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-500 hover:text-red-600 transition-colors" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-5 py-3">
                {{ $categories->links() }}
            </div>
        @endif

        @else
        {{-- Empty State --}}
        <div class="p-12 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                <i class="fas fa-folder-tree text-slate-400 text-2xl"></i>
            </div>
            <h3 class="text-base font-semibold text-slate-900">No categories yet</h3>
            <p class="mt-1 text-sm text-slate-500 max-w-md mx-auto">
                Create your first category to organize test sets by topic, difficulty, or any custom grouping.
            </p>
            <a href="{{ route('admin.test-categories.create') }}"
               class="mt-5 inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors shadow-sm">
                <i class="fas fa-plus mr-2"></i> Create First Category
            </a>
        </div>
        @endif
    </div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tbody = document.getElementById('sortable-categories');
    if (!tbody) return;
    Sortable.create(tbody, {
        handle: '.cursor-move',
        animation: 150,
        ghostClass: 'bg-indigo-50',
        onEnd: function() {
            const categories = [];
            document.querySelectorAll('#sortable-categories tr').forEach((row, index) => {
                categories.push({ id: row.dataset.id, sort_order: index + 1 });
            });
            fetch('{{ route('admin.test-categories.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ categories })
            });
        }
    });
});
</script>
@endpush
</x-admin-layout>
