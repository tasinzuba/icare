{{--
    Compact result cards, one card per TEST ATTEMPT.

      - Full test        -> one card with a chip per section plus the overall band
      - Single section   -> one card with that single chip
      - Several attempts -> a separate card each, newest first

    Shared by the branch, teacher and admin dashboards so results look identical everywhere.
    Colours are inline styles on purpose: the branch panel uses the Tailwind CDN while the
    teacher/admin panels use a built (purged) stylesheet, so dynamic colour utilities are not safe.

    Expects:
      $testGroups   Collection from StudentSectionResultService::recentTestGroups()
      $studentUrl   (optional) fn(User $student): string — link to all of that student's tests
      $viewAllUrl   (optional) URL for the panel's "View all" link
      $title        (optional) panel heading
      $showBranch   (optional) show the student's branch next to their name
--}}
@php
    $title = $title ?? 'Results';
    $showBranch = $showBranch ?? false;
    $studentUrl = $studentUrl ?? null;

    $sectionMeta = [
        'reading'   => ['short' => 'R', 'label' => 'Reading',   'icon' => 'fa-book-open',  'color' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
        'listening' => ['short' => 'L', 'label' => 'Listening', 'icon' => 'fa-headphones', 'color' => '#6d28d9', 'bg' => '#f5f3ff', 'border' => '#ddd6fe'],
        'writing'   => ['short' => 'W', 'label' => 'Writing',   'icon' => 'fa-pen-fancy',  'color' => '#b45309', 'bg' => '#fffbeb', 'border' => '#fde68a'],
    ];

    $bandColor = function ($band) {
        if ($band === null) return '#6b7280';
        if ($band >= 7) return '#15803d';
        if ($band >= 6) return '#1d4ed8';
        if ($band >= 5) return '#b45309';
        return '#b91c1c';
    };
@endphp

<div class="bg-white border border-gray-200 rounded-xl mb-6 md:mb-8">
    <div class="px-5 py-3.5 border-b border-gray-100 flex justify-between items-center">
        <h2 class="text-sm font-semibold text-gray-800">{{ $title }}</h2>
        @isset($viewAllUrl)
            <a href="{{ $viewAllUrl }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium whitespace-nowrap">
                View all <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
            </a>
        @endisset
    </div>

    @if($testGroups->isEmpty())
        <div class="px-5 py-10 text-center">
            <p class="text-sm text-gray-400">No student results yet</p>
        </div>
    @else
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($testGroups as $group)
                @php
                    $student = $group['student'];
                    $sections = \App\Services\StudentSectionResultService::orderSections($group['sections']);
                    $link = $studentUrl ? $studentUrl($student) : null;
                    $isFull = $group['kind'] === 'full';
                @endphp

                <div class="rounded-lg border border-gray-200 hover:border-gray-300 hover:shadow-sm transition p-3">
                    {{-- Student --}}
                    <div class="flex items-center gap-2.5 mb-2.5">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0"
                             style="background:#f3f4f6;color:#4b5563;font-weight:700;font-size:11px;">
                            {{ strtoupper(mb_substr($student->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            @if($link)
                                <a href="{{ $link }}" class="text-[13px] font-semibold text-gray-900 hover:text-blue-700 truncate block leading-tight">
                                    {{ $student->name ?? 'Unknown student' }}
                                </a>
                            @else
                                <p class="text-[13px] font-semibold text-gray-900 truncate leading-tight">{{ $student->name ?? 'Unknown student' }}</p>
                            @endif
                            <p class="text-[10px] text-gray-400 truncate leading-tight">
                                @if($showBranch && $student->branch){{ $student->branch->name }} &middot; @endif{{ $student->email }}
                            </p>
                        </div>
                        <span class="text-[9px] font-bold uppercase tracking-wide rounded px-1.5 py-0.5 shrink-0"
                              style="{{ $isFull ? 'background:#ecfdf5;color:#047857;' : 'background:#f1f5f9;color:#64748b;' }}">
                            {{ $isFull ? 'Full' : 'Single' }}
                        </span>
                    </div>

                    {{-- Section chips --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($sections as $sectionName => $sectionAttempt)
                            @php
                                $meta = $sectionMeta[$sectionName] ?? $sectionMeta['reading'];
                                $card = \App\Services\StudentSectionResultService::cardData($sectionAttempt, $sectionName);
                            @endphp
                            <div class="rounded-md px-2 py-1.5 flex-1" style="background:{{ $meta['bg'] }};border:1px solid {{ $meta['border'] }};min-width:84px;">
                                <div class="flex items-center justify-between gap-1">
                                    <span style="font-size:9px;font-weight:800;letter-spacing:.04em;color:{{ $meta['color'] }};">
                                        <i class="fas {{ $meta['icon'] }}"></i> {{ strtoupper($meta['label']) }}
                                    </span>
                                    @if(($card['state'] === 'scored') && !empty($card['total']))
                                        <span style="font-size:9px;color:#64748b;font-weight:600;">{{ $card['correct'] ?? 0 }}/{{ $card['total'] }}</span>
                                    @endif
                                </div>
                                @if($card['state'] === 'scored' || $card['state'] === 'ai')
                                    <div class="flex items-baseline gap-1 mt-0.5">
                                        <span style="font-size:18px;font-weight:800;line-height:1;color:{{ $bandColor($card['band']) }};">
                                            {{ number_format($card['band'], 1) }}
                                        </span>
                                        <span style="font-size:9px;color:#94a3b8;">band</span>
                                    </div>
                                @elseif($card['state'] === 'pending')
                                    <p style="font-size:11px;font-weight:700;color:#b45309;line-height:1.5;" class="mt-0.5">Pending</p>
                                @else
                                    <p style="font-size:11px;font-weight:700;color:#cbd5e1;line-height:1.5;" class="mt-0.5">&mdash;</p>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Test + date (+ overall for a full test) --}}
                    <div class="flex items-center justify-between gap-2 mt-2.5 pt-2 border-t border-gray-100">
                        <p class="text-[11px] text-gray-500 truncate min-w-0">{{ $group['title'] }}</p>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($group['overall'] !== null)
                                <span class="rounded px-1.5 py-0.5" style="background:#ecfdf5;">
                                    <span style="font-size:9px;font-weight:800;color:#047857;">OVERALL</span>
                                    <span style="font-size:12px;font-weight:800;color:{{ $bandColor($group['overall']) }};">{{ number_format($group['overall'], 1) }}</span>
                                </span>
                            @endif
                            <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ optional($group['taken_at'])->format('M d') }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
