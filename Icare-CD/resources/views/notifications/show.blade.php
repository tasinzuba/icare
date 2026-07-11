<x-student-layout>
    <x-slot name="title">{{ $notification->data['title'] ?? 'Notification' }}</x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-3xl mx-auto">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="{{ route('notifications.index') }}"
                   class="inline-flex items-center text-sm font-medium transition-colors"
                   :class="darkMode ? 'text-gray-400 hover:text-white' : 'text-gray-600 hover:text-gray-900'">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Notifications
                </a>
            </div>

            <!-- Notification Card -->
            <div class="glass rounded-xl overflow-hidden">
                <!-- Header -->
                <div class="p-6 border-b" :class="darkMode ? 'border-white/10' : 'border-gray-200'">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-lg bg-[#C8102E] flex items-center justify-center">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">
                                    {{ $notification->data['title'] ?? 'Notification' }}
                                </h1>
                                <p class="mt-1 text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                                    {{ $notification->created_at->format('F j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        </div>
                        <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to delete this notification?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="p-2 rounded-lg transition-colors"
                                    :class="darkMode ? 'hover:bg-red-900/20 text-gray-400 hover:text-red-400' : 'hover:bg-red-50 text-gray-400 hover:text-red-600'">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="prose max-w-none" :class="darkMode ? 'prose-invert' : ''">
                        <p class="text-base leading-relaxed" :class="darkMode ? 'text-gray-300' : 'text-gray-700'">
                            {{ $notification->data['message'] ?? 'No message content' }}
                        </p>

                        @if(isset($notification->data['action_url']))
                            <div class="mt-6">
                                <a href="{{ $notification->data['action_url'] }}"
                                   class="inline-flex items-center px-6 py-3 rounded-lg bg-[#C8102E] text-white font-medium hover:bg-[#A00E27] transition-colors">
                                    {{ $notification->data['action_text'] ?? 'View Details' }}
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Additional Info -->
                @if(isset($notification->data['details']))
                    <div class="p-6 border-t" :class="darkMode ? 'border-white/10 bg-white/5' : 'border-gray-200 bg-gray-50'">
                        <h3 class="text-sm font-semibold mb-3" :class="darkMode ? 'text-white' : 'text-gray-900'">
                            Additional Information
                        </h3>
                        <div class="space-y-2">
                            @foreach($notification->data['details'] as $key => $value)
                                <div class="flex justify-between text-sm">
                                    <span :class="darkMode ? 'text-gray-400' : 'text-gray-600'">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                    <span class="font-medium" :class="darkMode ? 'text-white' : 'text-gray-900'">{{ $value }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Footer -->
                <div class="p-4 border-t" :class="darkMode ? 'border-white/10 bg-white/5' : 'border-gray-200 bg-gray-50'">
                    <div class="flex items-center justify-between text-xs" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                        <span>
                            <i class="fas fa-clock mr-1"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        @if($notification->read_at)
                            <span class="flex items-center">
                                <i class="fas fa-check-double mr-1 text-green-500"></i>
                                Read on {{ $notification->read_at->format('M j, Y \a\t g:i A') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-student-layout>
