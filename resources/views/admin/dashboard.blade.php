<x-admin-layout>
    <x-slot:title>Dashboard</x-slot>

    
    <!-- Key Metrics Grid -->
    <div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $metrics = [
                [
                    'title' => 'Total Students',
                    'value' => \App\Models\User::where('is_admin', false)->count(),
                    'change' => '+12.5%',
                    'changeType' => 'positive',
                    'icon' => 'users',
                    'color' => 'blue',
                    'subtext' => 'Active users'
                ],
                [
                    'title' => 'Pending Reviews',
                    'value' => \App\Models\StudentAttempt::where('status', 'completed')->whereNull('band_score')->count(),
                    'change' => '-5',
                    'changeType' => 'negative',
                    'icon' => 'clock',
                    'color' => 'orange',
                    'subtext' => 'Needs attention'
                ]
            ];
        @endphp

        @foreach($metrics as $metric)
            <div class="metric-card rounded-xl bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600">{{ $metric['title'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">{{ $metric['value'] }}</p>
                        <div class="mt-2 flex items-center text-sm">
                            <span class="font-medium {{ $metric['changeType'] === 'positive' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $metric['change'] }}
                            </span>
                            <span class="ml-2 text-gray-500">{{ $metric['subtext'] }}</span>
                        </div>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg 
                        @if($metric['color'] === 'blue') bg-blue-100
                        @elseif($metric['color'] === 'green') bg-green-100
                        @elseif($metric['color'] === 'purple') bg-purple-100
                        @else bg-orange-100
                        @endif">
                        @if($metric['icon'] === 'users')
                            <svg class="h-6 w-6 @if($metric['color'] === 'blue') text-blue-600 @elseif($metric['color'] === 'green') text-green-600 @elseif($metric['color'] === 'purple') text-purple-600 @else text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        @elseif($metric['icon'] === 'trending-up')
                            <svg class="h-6 w-6 @if($metric['color'] === 'blue') text-blue-600 @elseif($metric['color'] === 'green') text-green-600 @elseif($metric['color'] === 'purple') text-purple-600 @else text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        @elseif($metric['icon'] === 'crown')
                            <svg class="h-6 w-6 @if($metric['color'] === 'blue') text-blue-600 @elseif($metric['color'] === 'green') text-green-600 @elseif($metric['color'] === 'purple') text-purple-600 @else text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        @else
                            <svg class="h-6 w-6 @if($metric['color'] === 'blue') text-blue-600 @elseif($metric['color'] === 'green') text-green-600 @elseif($metric['color'] === 'purple') text-purple-600 @else text-orange-600 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Activity Feed & Quick Actions -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Recent Activity -->
        <div class="rounded-xl bg-white p-6 shadow-sm lg:col-span-2">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                <a href="{{ route('admin.attempts.index') }}" class="text-sm font-medium text-primary hover:text-primary-dark">
                    View all →
                </a>
            </div>
            
            <div class="space-y-4">
                @php
                    $recentAttempts = \App\Models\StudentAttempt::with(['user', 'testSet.section'])
                        ->latest()
                        ->take(5)
                        ->get();
                @endphp
                
                @forelse($recentAttempts as $attempt)
                    <div class="flex items-center rounded-lg border border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-100">
                            <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $attempt->user->name }}</p>
                            <p class="text-xs text-gray-500">
                                Completed {{ $attempt->testSet->section->name }} test • {{ $attempt->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <div class="ml-4">
                            @if($attempt->band_score)
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                    Band {{ $attempt->band_score }}
                                </span>
                            @else
                                <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-center text-sm text-gray-500">No recent activity</p>
                @endforelse
            </div>
        </div>

        <!-- Quick Actions & Stats -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('admin.questions.create') }}" 
                       class="flex items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Add Question</p>
                            <p class="text-xs text-gray-500">Create new test content</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('admin.test-sets.create') }}" 
                       class="flex items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">New Test Set</p>
                            <p class="text-xs text-gray-500">Create test collection</p>
                        </div>
                    </a>
                    
                </div>
            </div>

            <!-- System Health -->
            <div class="rounded-xl bg-white p-6 shadow-sm">
                <h3 class="mb-4 text-lg font-semibold text-gray-900">System Health</h3>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Server Status</span>
                            <span class="font-medium text-green-600">Operational</span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 w-full rounded-full bg-green-500"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">API Response</span>
                            <span class="font-medium text-gray-900">45ms</span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 w-3/4 rounded-full bg-blue-500"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Storage Used</span>
                            <span class="font-medium text-gray-900">62%</span>
                        </div>
                        <div class="mt-2 h-2 w-full rounded-full bg-gray-200">
                            <div class="h-2 w-3/5 rounded-full bg-yellow-500"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Real-time updates simulation
        setInterval(() => {
            // Add animation to metrics
            document.querySelectorAll('.metric-card').forEach(card => {
                card.classList.add('animate-pulse');
                setTimeout(() => card.classList.remove('animate-pulse'), 1000);
            });
        }, 30000);
    </script>
    @endpush
</x-admin-layout>