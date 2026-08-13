{{--
    Per-student section result cards (Reading / Listening / Writing).

    Shared by the branch, teacher and admin dashboards so results look identical everywhere.
    Colours are inline styles on purpose: the branch panel uses the Tailwind CDN while the
    teacher/admin panels use a built (purged) stylesheet, so dynamic colour utilities are not safe.

    Expects:
      $studentResults  Collection from StudentSectionResultService::recentStudents()
      $viewAllUrl      (optional) URL for the "View all" link
      $title           (optional) panel heading
      $showBranch      (optional) show the student's branch under their name
--}}
@php
    $title = $title ?? 'Student Results';
    $showBranch = $showBranch ?? false;

    $sectionMeta = [
        'reading'   => ['label' => 'Reading',   'icon' => 'fa-book-open',  'color' => '#2563eb', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
        'listening' => ['label' => 'Listening', 'icon' => 'fa-headphones', 'color' => '#7c3aed', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
        'writing'   => ['label' => 'Writing',   'icon' => 'fa-pen-fancy',  'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fde68a'],
    ];

    $bandColor = function ($band) {
        if ($band === null) return '#6b7280';
        if ($band >= 7) return '#15803d';   // green
        if ($band >= 6) return '#1d4ed8';   // blue
        if ($band >= 5) return '#b45309';   // amber
        return '#b91c1c';                   // red
    };
@endphp

<div class="bg-white border border-gray-200 rounded-xl mb-6 md:mb-8">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
        <div>
            <h2 class="text-sm font-semibold text-gray-800">{{ $title }}</h2>
            <p class="text-[11px] text-gray-400 mt-0.5">Latest Reading, Listening and Writing result for each student</p>
        </div>
        @isset($viewAllUrl)
            <a href="{{ $viewAllUrl }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap">
                View all <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        @endisset
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($studentResults as $row)
            @php $student = $row['student']; @endphp
            <article class="p-5">
                {{-- Student identity --}}
                <div class="flex items-center justify-between mb-3 gap-3">
                    <div class="flex items-center min-w-0">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0 mr-3"
                             style="background:#f3f4f6;color:#4b5563;font-weight:700;font-size:12px;">
                            {{ strtoupper(mb_substr($student->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $student->name ?? 'Unknown student' }}</p>
                            <p class="text-[11px] text-gray-400 truncate">
                                {{ $student->email }}@if($showBranch && $student->branch) &middot; {{ $student->branch->name }}@endif
                            </p>
                        </div>
                    </div>
                    @if(!empty($row['last_activity']))
                        <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ $row['last_activity']->diffForHumans() }}</span>
                    @endif
                </div>

                {{-- One card per section --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    @foreach(\App\Services\StudentSectionResultService::SECTIONS as $sectionName)
                        @php
                            $meta = $sectionMeta[$sectionName];
                            $card = \App\Services\StudentSectionResultService::cardData($row['sections'][$sectionName] ?? null, $sectionName);
                        @endphp
                        <div class="rounded-lg p-3"
                             style="background:{{ $meta['bg'] }};border:1px solid {{ $meta['border'] }};">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-[11px] font-bold uppercase tracking-wide" style="color:{{ $meta['color'] }};">
                                    <i class="fas {{ $meta['icon'] }} mr-1"></i>{{ $meta['label'] }}
                                </span>
                                @if($card['state'] === 'scored' && $card['total'])
                                    <span class="text-[11px] font-semibold" style="color:#4b5563;">
                                        {{ $card['correct'] ?? 0 }}/{{ $card['total'] }}
                                    </span>
                                @endif
                            </div>

                            @if($card['state'] === 'scored')
                                <div class="flex items-baseline gap-1.5">
                                    <span style="font-size:24px;font-weight:800;line-height:1.1;color:{{ $bandColor($card['band']) }};">
                                        {{ number_format($card['band'], 1) }}
                                    </span>
                                    <span class="text-[11px] text-gray-500">band</span>
                                </div>
                                @if($card['total'])
                                    <p class="text-[10px] text-gray-500 mt-0.5">
                                        {{ $card['total'] > 0 ? round(($card['correct'] ?? 0) / $card['total'] * 100) : 0 }}% correct
                                    </p>
                                @else
                                    <p class="text-[10px] text-gray-500 mt-0.5">Evaluated</p>
                                @endif

                            @elseif($card['state'] === 'ai')
                                <div class="flex items-baseline gap-1.5">
                                    <span style="font-size:24px;font-weight:800;line-height:1.1;color:{{ $bandColor($card['band']) }};">
                                        {{ number_format($card['band'], 1) }}
                                    </span>
                                    <span class="text-[11px] text-gray-500">band</span>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-0.5">AI evaluated</p>

                            @elseif($card['state'] === 'pending')
                                <p style="font-size:15px;font-weight:700;color:#b45309;line-height:1.4;">Awaiting evaluation</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Submitted {{ optional($card['attempt']->created_at)->diffForHumans() }}</p>

                            @else
                                <p style="font-size:15px;font-weight:700;color:#9ca3af;line-height:1.4;">Not attempted</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">No completed test yet</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">No student results yet</p>
            </div>
        @endforelse
    </div>
</div>
