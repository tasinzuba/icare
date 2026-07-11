<div class="teacher-cards">

    @if($teachers->isEmpty())
        <div class="text-center py-12 bg-white rounded-2xl border border-gray-200">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-user-slash text-gray-400 text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">No Teachers Available</h3>
            <p class="text-gray-500 text-sm">No teachers available for {{ $section }} evaluation at the moment.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($teachers as $teacher)
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all p-4">
                <div class="flex items-center gap-4">
                    <!-- Avatar -->
                    @if($teacher->user->avatar_url ?? false)
                        <img src="{{ $teacher->user->avatar_url }}"
                             alt="{{ $teacher->user->name }}"
                             class="w-12 h-12 rounded-xl object-cover border border-gray-100">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-gray-800 flex items-center justify-center text-white font-bold">
                            {{ substr($teacher->user->name ?? 'T', 0, 1) }}
                        </div>
                    @endif

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-semibold text-gray-900 truncate">{{ $teacher->user->name ?? 'Unknown Teacher' }}</h4>
                            @if(($teacher->rating ?? 0) >= 4.5)
                                <span class="px-1.5 py-0.5 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">
                                    <i class="fas fa-crown" style="font-size: 8px;"></i>
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="flex text-amber-400">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star {{ $i <= ($teacher->rating ?? 0) ? '' : 'text-gray-200' }}" style="font-size: 10px;"></i>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500">({{ $teacher->total_evaluations_done ?? 0 }})</span>
                            <span class="text-gray-300">•</span>
                            <span class="text-xs text-gray-500">{{ $teacher->experience_years ?? 0 }}y exp</span>
                            <span class="text-gray-300">•</span>
                            <span class="text-xs text-gray-500">~{{ $teacher->average_turnaround_hours ?? 24 }}h</span>
                        </div>
                    </div>

                    <!-- Price & Action -->
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-lg font-bold text-gray-900">{{ $teacher->token_price ?? 0 }}</p>
                            <p class="text-xs text-gray-500">tokens</p>
                        </div>

                        @if($tokenBalance && $tokenBalance->available_tokens >= ($teacher->token_price ?? 0))
                            <button onclick="selectTeacher({{ $teacher->id }}, '{{ addslashes($teacher->user->name ?? 'Unknown') }}', 'normal')"
                                    class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm font-semibold hover:bg-gray-800 transition-all">
                                Select
                            </button>
                        @else
                            <button disabled
                                    class="px-4 py-2 rounded-lg bg-gray-100 text-gray-400 text-sm font-medium cursor-not-allowed">
                                Need tokens
                            </button>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
