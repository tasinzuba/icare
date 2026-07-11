{{-- resources/views/partials/leaderboard-content.blade.php --}}
@if($leaderboard->isNotEmpty())
    <div class="space-y-2">
        @foreach($leaderboard->take(10) as $entry)
            <div class="flex items-center justify-between p-3 rounded-lg {{ $entry->user_id === auth()->id() ? 'bg-[#C8102E]/10 border-2 border-[#C8102E]' : 'bg-gray-50' }}">
                <div class="flex items-center space-x-3">
                    <span class="text-lg font-bold {{ $loop->iteration <= 3 ? 'text-[#C8102E]' : 'text-gray-600' }}">
                        #{{ $loop->iteration }}
                    </span>
                    <span class="font-semibold text-gray-800">
                        {{ $entry->user_id === auth()->id() ? 'You' : Str::limit($entry->user->name, 20) }}
                    </span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="font-bold text-[#C8102E]">{{ $entry->total_points }} pts</span>
                    @if($loop->iteration <= 3)
                        <i class="fas fa-trophy {{ $loop->iteration === 1 ? 'text-yellow-500' : ($loop->iteration === 2 ? 'text-gray-400' : 'text-orange-500') }}"></i>
                    @endif
                </div>
            </div>
        @endforeach

        @if(!$userInLeaderboard)
            <div class="pt-3 border-t border-gray-200">
                <p class="text-xs text-gray-500 text-center">
                    You're not in top 10. Keep practicing!
                </p>
            </div>
        @endif
    </div>
@else
    <div class="text-center py-6">
        <i class="fas fa-users text-4xl text-gray-300 mb-3"></i>
        <p class="text-gray-600 text-sm">No leaderboard data yet</p>
        <p class="text-xs text-gray-500 mt-1">Be the first to set a record!</p>
    </div>
@endif