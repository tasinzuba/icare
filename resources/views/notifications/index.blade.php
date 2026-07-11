<x-student-layout>
    <x-slot name="title">Notifications</x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold" :class="darkMode ? 'text-white' : 'text-gray-900'">Notifications</h1>
                        <p class="mt-1 text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                            Stay updated with your latest activities
                        </p>
                    </div>
                    @if($notifications->total() > 0)
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                    :class="darkMode ? 'bg-[#C8102E] text-white hover:bg-[#A00E27]' : 'bg-[#C8102E] text-white hover:bg-[#A00E27]'">
                                Mark All as Read
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-lg" :class="darkMode ? 'bg-green-900/20 border border-green-500/30' : 'bg-green-50 border border-green-200'">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle mr-3" :class="darkMode ? 'text-green-400' : 'text-green-600'"></i>
                        <p class="text-sm font-medium" :class="darkMode ? 'text-green-400' : 'text-green-800'">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <!-- Notifications List -->
            <div class="space-y-3">
                @forelse($notifications as $notification)
                    <div class="glass rounded-xl overflow-hidden transition-all duration-200 hover:border-[#C8102E]/50">
                        <a href="{{ route('notifications.show', $notification->id) }}" class="block p-6">
                            <div class="flex items-start space-x-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-lg flex items-center justify-center"
                                         :class="darkMode ? ({{ $notification->unread() ? "'bg-[#C8102E]'" : "'bg-gray-700'" }}) : ({{ $notification->unread() ? "'bg-[#C8102E]'" : "'bg-gray-200'" }})">
                                        <i class="fas fa-bell" :class="{{ $notification->unread() ? "'text-white'" : (darkMode ? "'text-gray-400'" : "'text-gray-600'") }}"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1">
                                            <h3 class="text-base font-semibold" :class="darkMode ? 'text-white' : 'text-gray-900'">
                                                {{ $notification->data['title'] ?? 'Notification' }}
                                            </h3>
                                            <p class="mt-1 text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">
                                                {{ $notification->data['message'] ?? 'New notification' }}
                                            </p>
                                            <p class="mt-2 text-xs" :class="darkMode ? 'text-gray-500' : 'text-gray-500'">
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        @if($notification->unread())
                                            <span class="ml-4 w-2.5 h-2.5 bg-[#C8102E] rounded-full flex-shrink-0"></span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Delete Button -->
                                <div class="flex-shrink-0">
                                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST"
                                          onclick="event.stopPropagation(); return confirm('Are you sure you want to delete this notification?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-2 rounded-lg transition-colors"
                                                :class="darkMode ? 'hover:bg-red-900/20 text-gray-400 hover:text-red-400' : 'hover:bg-red-50 text-gray-400 hover:text-red-600'">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </a>
                    </div>
                @empty
                    <div class="glass rounded-xl p-12 text-center">
                        <i class="fas fa-bell-slash text-6xl mb-4" :class="darkMode ? 'text-gray-600' : 'text-gray-400'"></i>
                        <h3 class="text-lg font-semibold mb-2" :class="darkMode ? 'text-white' : 'text-gray-900'">No Notifications</h3>
                        <p class="text-sm" :class="darkMode ? 'text-gray-400' : 'text-gray-600'">You don't have any notifications yet</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-student-layout>
