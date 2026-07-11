<x-admin-layout>
    <x-slot:title>Teacher Details</x-slot>

    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-start justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-16 w-16">
                        <div class="h-16 w-16 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-2xl font-semibold">
                            {{ substr($teacher->user->name, 0, 1) }}
                        </div>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-gray-900">{{ $teacher->user->name }}</h2>
                        <p class="text-gray-500">{{ $teacher->user->email }}</p>
                        <div class="mt-2 flex items-center">
                            @if($teacher->is_available)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Available
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Unavailable
                                </span>
                            @endif
                            <span class="ml-3 text-sm text-gray-500">Member since {{ $teacher->created_at->format('M d, Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.teachers.edit', $teacher) }}" 
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('admin.teachers.toggle-availability', $teacher) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-md text-gray-700 hover:bg-gray-50">
                            {{ $teacher->is_available ? 'Disable' : 'Enable' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Statistics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Evaluations</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_evaluations'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Pending</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_evaluations'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Average Rating</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $teacher->rating }}/5</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-10 w-10 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $stats['total_earnings'] }} tokens</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Teacher Info -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Teacher Information</h3>
                    
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Specialization</dt>
                            <dd class="mt-1 flex flex-wrap gap-1">
                                @foreach($teacher->specialization as $spec)
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    {{ $spec == 'writing' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $spec == 'speaking' ? 'bg-blue-100 text-blue-800' : '' }}
                                    {{ $spec == 'reading' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $spec == 'listening' ? 'bg-purple-100 text-purple-800' : '' }}">
                                    {{ ucfirst($spec) }}
                                </span>
                                @endforeach
                            </dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Experience</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $teacher->experience_years }} years</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Token Price</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $teacher->evaluation_price_tokens }} tokens per evaluation</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Average Turnaround</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $teacher->average_turnaround_hours }} hours</dd>
                        </div>
                        
                        @if($teacher->qualifications)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Qualifications</dt>
                            <dd class="mt-1 space-y-1">
                                @foreach($teacher->qualifications as $qualification)
                                <span class="block text-sm text-gray-900">• {{ $qualification }}</span>
                                @endforeach
                            </dd>
                        </div>
                        @endif
                        
                        @if($teacher->languages)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Languages</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ implode(', ', $teacher->languages) }}</dd>
                        </div>
                        @endif
                        
                        @if($teacher->profile_description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Profile Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $teacher->profile_description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
            
            <!-- Recent Evaluations -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Evaluations</h3>
                    </div>
                    <div class="overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Test</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($teacher->evaluationRequests->take(10) as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->student->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->studentAttempt->testSet->title }}
                                        <span class="text-xs text-gray-400">({{ ucfirst($request->studentAttempt->testSet->section->name) }})</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ $request->status == 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $request->status == 'in_progress' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $request->status == 'pending' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($request->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500 text-sm">
                                        No evaluations yet
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
