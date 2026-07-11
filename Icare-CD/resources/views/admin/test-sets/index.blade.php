<x-admin-layout>
    <x-slot:title>Test Sets Management</x-slot>
    
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Test Sets Management</h2>
                        <p class="mt-1 text-sm text-gray-600">Manage all IELTS test sets across different sections</p>
                    </div>
                    <a href="{{ route('admin.test-sets.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Test Set
                    </a>
                </div>
            </div>

            <!-- Section Tabs -->
            <div class="px-6">
                <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Sections">
                    @php
                        $currentSection = request('section', 'all');
                        $sectionData = [
                            'all' => ['name' => 'All Sections', 'icon' => 'fas fa-th', 'color' => 'gray'],
                            'reading' => ['name' => 'Reading', 'icon' => 'fas fa-book-open', 'color' => 'green'],
                            'listening' => ['name' => 'Listening', 'icon' => 'fas fa-headphones', 'color' => 'purple'],
                            'writing' => ['name' => 'Writing', 'icon' => 'fas fa-pen-fancy', 'color' => 'blue'],
                            'speaking' => ['name' => 'Speaking', 'icon' => 'fas fa-microphone', 'color' => 'pink'],
                        ];
                    @endphp
                    
                    @foreach($sectionData as $key => $section)
                        <a href="{{ route('admin.test-sets.index', $key === 'all' ? [] : ['section' => $key]) }}" 
                           class="group inline-flex items-center px-1 py-4 border-b-2 font-medium text-sm transition-all
                                  {{ $currentSection === $key 
                                     ? 'border-indigo-600 text-indigo-600' 
                                     : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <i class="{{ $section['icon'] }} mr-2 {{ $currentSection === $key ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}"></i>
                            {{ $section['name'] }}
                            @if($key !== 'all')
                                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $currentSection === $key ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $testSets->where('section.name', $key)->count() }}
                                </span>
                            @else
                                <span class="ml-2 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $currentSection === $key ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $testSets->total() }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @php
                $stats = [
                    [
                        'title' => 'Total Test Sets',
                        'value' => $testSets->total(),
                        'icon' => 'fas fa-layer-group',
                        'color' => 'indigo',
                        'description' => 'All sections combined'
                    ],
                    [
                        'title' => 'Active Tests',
                        'value' => $testSets->where('active', true)->count(),
                        'icon' => 'fas fa-check-circle',
                        'color' => 'green',
                        'description' => 'Currently available'
                    ],
                    [
                        'title' => 'Total Questions',
                        'value' => \App\Models\Question::count(),
                        'icon' => 'fas fa-question-circle',
                        'color' => 'purple',
                        'description' => 'Across all test sets'
                    ],
                    [
                        'title' => 'Recent Attempts',
                        'value' => \App\Models\StudentAttempt::whereDate('created_at', '>=', now()->subDays(7))->count(),
                        'icon' => 'fas fa-users',
                        'color' => 'orange',
                        'description' => 'Last 7 days'
                    ]
                ];
            @endphp

            @foreach($stats as $stat)
                <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-200">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="p-3 bg-{{ $stat['color'] }}-100 rounded-lg">
                                <i class="{{ $stat['icon'] }} text-2xl text-{{ $stat['color'] }}-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    {{ $stat['title'] }}
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-gray-900">
                                        {{ number_format($stat['value']) }}
                                    </div>
                                </dd>
                                <dd class="text-xs text-gray-500">
                                    {{ $stat['description'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Test Sets Table -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            @if($testSets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Test Set
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Section
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Questions
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Created
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($testSets as $testSet)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $testSet->title }}</div>
                                                <div class="text-sm text-gray-500">ID: #{{ $testSet->id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $sectionIcons = [
                                                'listening' => 'fas fa-headphones',
                                                'reading' => 'fas fa-book-open',
                                                'writing' => 'fas fa-pen-fancy',
                                                'speaking' => 'fas fa-microphone'
                                            ];
                                            $sectionColors = [
                                                'listening' => 'purple',
                                                'reading' => 'green',
                                                'writing' => 'blue',
                                                'speaking' => 'pink'
                                            ];
                                            $icon = $sectionIcons[$testSet->section->name] ?? 'fas fa-file';
                                            $color = $sectionColors[$testSet->section->name] ?? 'gray';
                                        @endphp
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                                            <i class="{{ $icon }} mr-1.5 text-{{ $color }}-500"></i>
                                            {{ ucfirst($testSet->section->name) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $testSet->questions->count() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center gap-1">
                                            @if ($testSet->active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <span class="w-2 h-2 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
                                                    Active
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <span class="w-2 h-2 bg-gray-400 rounded-full mr-1.5"></span>
                                                    Inactive
                                                </span>
                                            @endif
                                            @if ($testSet->is_premium)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    <i class="fas fa-star mr-1 text-amber-500"></i>
                                                    Premium
                                                </span>
                                            @endif
                                            @if ($testSet->is_for_offline && $testSet->is_for_online)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-users mr-1 text-purple-500"></i>
                                                    Both
                                                </span>
                                            @elseif ($testSet->is_for_offline)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                    <i class="fas fa-building mr-1 text-orange-500"></i>
                                                    Branch Only
                                                </span>
                                            @elseif ($testSet->is_for_online)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    <i class="fas fa-globe mr-1 text-emerald-500"></i>
                                                    Online Only
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $testSet->created_at->format('M d, Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $testSet->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex items-center justify-center space-x-1">
                                            <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                                               class="inline-flex items-center p-2 text-gray-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.test-sets.edit', $testSet) }}" 
                                               class="inline-flex items-center p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-all"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.test-sets.destroy', $testSet) }}" method="POST" class="inline-block"
                                                  onsubmit="return confirm('Are you sure you want to delete this test set? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="inline-flex items-center p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                        title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if ($testSets->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $testSets->withQueryString()->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12 px-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                        <i class="fas fa-folder-open text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No test sets found</h3>
                    <p class="text-sm text-gray-500 mb-6">Get started by creating your first test set.</p>
                    <a href="{{ route('admin.test-sets.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Create Test Set
                    </a>
                </div>
            @endif
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
                @php
                    $sectionColors = [
                        'listening' => 'purple',
                        'reading' => 'green', 
                        'writing' => 'blue',
                        'speaking' => 'pink'
                    ];
                    $sectionIcons = [
                        'listening' => 'fas fa-headphones',
                        'reading' => 'fas fa-book-open',
                        'writing' => 'fas fa-pen-fancy',
                        'speaking' => 'fas fa-microphone'
                    ];
                    $color = $sectionColors[$section];
                    $icon = $sectionIcons[$section];
                @endphp
                <a href="{{ route('admin.test-sets.create', ['section' => $section]) }}" 
                   class="bg-white border-2 border-dashed border-{{ $color }}-300 rounded-lg p-6 hover:border-{{ $color }}-400 hover:bg-{{ $color }}-50 transition-all group">
                    <div class="flex items-center justify-between">
                        <div>
                            <i class="{{ $icon }} text-2xl text-{{ $color }}-500 group-hover:text-{{ $color }}-600"></i>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Create {{ ucfirst($section) }} Test</h3>
                        </div>
                        <i class="fas fa-arrow-right text-{{ $color }}-400 group-hover:text-{{ $color }}-600 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        // Add any specific JavaScript for this page
    </script>
    @endpush
</x-admin-layout>
