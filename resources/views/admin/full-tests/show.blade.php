<x-admin-layout>
    <x-slot:title>{{ $fullTest->title }} - Full Test Details</x-slot>

    <!-- Page Header with Actions -->
    <div class="mb-8">
        <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <a href="{{ route('admin.full-tests.index') }}" 
                           class="text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $fullTest->title }}</h1>
                        @if($fullTest->is_premium)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800">
                                <svg class="mr-1.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                                Premium
                            </span>
                        @endif
                        @if($fullTest->active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <span class="mr-1.5 h-2 w-2 rounded-full bg-green-600"></span>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <span class="mr-1.5 h-2 w-2 rounded-full bg-red-600"></span>
                                Inactive
                            </span>
                        @endif
                    </div>
                    @if($fullTest->description)
                        <p class="text-gray-600">{{ $fullTest->description }}</p>
                    @endif
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.full-tests.edit', $fullTest) }}" 
                       class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Test
                    </a>
                    <form action="{{ route('admin.full-tests.toggle-status', $fullTest) }}" 
                          method="POST" 
                          class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center rounded-lg px-4 py-2.5 text-sm font-semibold transition-colors
                                {{ $fullTest->active ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            {{ $fullTest->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Test Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Test Sections Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Test Sections</h2>
                
                @php
                    $sections = [
                        'listening' => ['icon' => 'fa-headphones', 'color' => 'violet', 'title' => 'Listening'],
                        'reading' => ['icon' => 'fa-book-open', 'color' => 'emerald', 'title' => 'Reading'],
                        'writing' => ['icon' => 'fa-pen-fancy', 'color' => 'amber', 'title' => 'Writing'],
                        'speaking' => ['icon' => 'fa-microphone', 'color' => 'rose', 'title' => 'Speaking']
                    ];
                    $availableSections = $fullTest->getAvailableSections();
                @endphp
                
                <div class="space-y-4">
                    @foreach($sections as $key => $section)
                        @php
                            $testSet = $fullTest->{$key . 'TestSet'}();
                            $isAvailable = in_array($key, $availableSections);
                        @endphp
                        <div class="flex items-center p-4 rounded-lg border {{ $isAvailable ? 'bg-gray-50 border-gray-200' : 'bg-gray-50/50 border-gray-100' }}">
                            <div class="flex-shrink-0 w-12 h-12 rounded-lg {{ $isAvailable ? 'bg-'.$section['color'].'-100' : 'bg-gray-100' }} flex items-center justify-center">
                                <i class="fas {{ $section['icon'] }} text-xl {{ $isAvailable ? 'text-'.$section['color'].'-600' : 'text-gray-400' }}"></i>
                            </div>
                            <div class="ml-4 flex-1">
                                <h3 class="text-sm font-medium {{ $isAvailable ? 'text-gray-900' : 'text-gray-500' }}">
                                    {{ $section['title'] }}
                                </h3>
                                @if($isAvailable && $testSet)
                                    <p class="text-sm text-gray-600">{{ $testSet->title }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $testSet->questions()->count() }} questions • 
                                        {{ $testSet->section->name }}
                                    </p>
                                @else
                                    <p class="text-sm text-gray-400 italic">Not assigned</p>
                                @endif
                            </div>
                            @if($isAvailable && $testSet)
                                <a href="{{ route('admin.test-sets.show', $testSet) }}" 
                                   class="flex-shrink-0 ml-4 text-indigo-600 hover:text-indigo-900"
                                   title="View Test Set">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
                
                @if(count($availableSections) < 3)
                    <div class="mt-4 p-4 rounded-lg bg-amber-50 border border-amber-200">
                        <div class="flex">
                            <svg class="h-5 w-5 text-amber-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <p class="ml-3 text-sm text-amber-800">
                                This test has only {{ count($availableSections) }} sections. Minimum 3 sections are recommended for a complete full test.
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Recent Attempts Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-semibold text-gray-900">Recent Attempts</h2>
                    <span class="text-sm text-gray-500">Total: {{ $fullTest->attempts()->count() }}</span>
                </div>
                
                @php
                    $recentAttempts = $fullTest->attempts()
                        ->with('user')
                        ->latest()
                        ->take(10)
                        ->get();
                @endphp
                
                @if($recentAttempts->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Student
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Started
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Overall Score
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($recentAttempts as $attempt)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <img class="h-8 w-8 rounded-full" 
                                                     src="{{ $attempt->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($attempt->user->name) }}" 
                                                     alt="{{ $attempt->user->name }}">
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-gray-900">{{ $attempt->user->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $attempt->user->email }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $attempt->start_time->format('M d, Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $attempt->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($attempt->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($attempt->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if($attempt->overall_band_score)
                                                <span class="text-sm font-semibold text-gray-900">
                                                    {{ number_format($attempt->overall_band_score, 1) }}
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.full-test-attempts.show', $attempt) }}"
                                                   class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                                    View Details
                                                </a>
                                                <span class="text-gray-300">|</span>
                                                <a href="{{ route('admin.full-tests.user-attempts', $attempt->user->id) }}"
                                                   class="text-gray-600 hover:text-gray-900 text-sm">
                                                    All Attempts
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">No attempts yet</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column - Statistics -->
        <div class="space-y-6">
            <!-- Quick Stats Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-6">Statistics</h2>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-600">Total Attempts</p>
                            <p class="text-xl font-semibold text-gray-900">{{ $fullTest->attempts()->count() }}</p>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Completed: {{ $fullTest->attempts()->where('status', 'completed')->count() }}</span>
                            <span>In Progress: {{ $fullTest->attempts()->where('status', 'in_progress')->count() }}</span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-600">Average Score</p>
                            <p class="text-xl font-semibold text-gray-900">
                                @php
                                    $avgScore = $fullTest->attempts()
                                        ->where('status', 'completed')
                                        ->whereNotNull('overall_band_score')
                                        ->avg('overall_band_score');
                                @endphp
                                {{ $avgScore ? number_format($avgScore, 1) : 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-600">Completion Rate</p>
                            <p class="text-xl font-semibold text-gray-900">
                                @php
                                    $totalAttempts = $fullTest->attempts()->count();
                                    $completedAttempts = $fullTest->attempts()->where('status', 'completed')->count();
                                    $completionRate = $totalAttempts > 0 ? ($completedAttempts / $totalAttempts) * 100 : 0;
                                @endphp
                                {{ number_format($completionRate, 0) }}%
                            </p>
                        </div>
                        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-500" 
                                 style="width: {{ $completionRate }}%"></div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-sm text-gray-600 mb-3">Section Scores Average</p>
                        <div class="space-y-2">
                            @foreach(['listening', 'reading', 'writing', 'speaking'] as $section)
                                @if(in_array($section, $availableSections))
                                    @php
                                        $sectionAvg = $fullTest->attempts()
                                            ->where('status', 'completed')
                                            ->whereNotNull($section . '_score')
                                            ->avg($section . '_score');
                                    @endphp
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-600 capitalize">{{ $section }}</span>
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ $sectionAvg ? number_format($sectionAvg, 1) : '-' }}
                                        </span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Info Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Test Information</h2>
                
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-600">Created</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $fullTest->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Last Updated</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $fullTest->updated_at->format('M d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Order Number</dt>
                        <dd class="text-sm font-medium text-gray-900">#{{ $fullTest->order_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-600">Test ID</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $fullTest->id }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Actions Card -->
            <div class="rounded-xl bg-white p-6 shadow-sm border border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                
                <div class="space-y-2">
                    <a href="{{ route('admin.full-tests.edit', $fullTest) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 border border-indigo-600 rounded-lg text-sm font-medium text-indigo-600 hover:bg-indigo-50 transition-colors">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Edit Full Test
                    </a>
                    
                    @if($fullTest->attempts()->count() === 0)
                        <form action="{{ route('admin.full-tests.destroy', $fullTest) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to delete this full test?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-2 border border-red-600 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Delete Full Test
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
