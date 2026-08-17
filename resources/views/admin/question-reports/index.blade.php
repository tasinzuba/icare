<x-admin-layout>
    <x-slot:title>Question Reports</x-slot>

    @php
        $statusTabs = ['' => 'All'] + \App\Models\QuestionReport::STATUSES;
    @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white shadow-sm rounded-lg px-6 py-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Question Reports</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Mistakes students have flagged from their result pages</p>
                </div>
                <div class="flex items-center gap-6 text-center">
                    <div>
                        <p class="text-2xl font-bold text-gray-900">{{ $total }}</p>
                        <p class="text-xs text-gray-500">Total</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600">{{ $counts['pending'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Pending</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-emerald-600">{{ $counts['resolved'] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">Resolved</p>
                    </div>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white shadow-sm rounded-lg px-6 py-4">
            <div class="flex flex-wrap gap-2 mb-4">
                @foreach($statusTabs as $value => $label)
                    <a href="{{ route('admin.question-reports.index', array_filter(['status' => $value] + request()->except('status', 'page'))) }}"
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                              {{ request('status', '') === (string) $value
                                 ? 'bg-indigo-600 text-white'
                                 : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        {{ $label }}
                        <span class="ml-1 text-xs opacity-75">
                            {{ $value === '' ? $total : ($counts[$value] ?? 0) }}
                        </span>
                    </a>
                @endforeach
            </div>

            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <input type="hidden" name="status" value="{{ request('status') }}">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search student, test or description"
                       class="sm:col-span-2 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <select name="issue_type" class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="">All issues</option>
                    @foreach(\App\Models\QuestionReport::ISSUE_TYPES as $key => $label)
                        <option value="{{ $key }}" @selected(request('issue_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2">
                    <select name="section" class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">All sections</option>
                        @foreach(['listening', 'reading', 'writing', 'speaking'] as $s)
                            <option value="{{ $s }}" @selected(request('section') === $s)>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        {{-- List --}}
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            @forelse($reports as $report)
                @php $question = $report->question; @endphp
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-6 py-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50/60 transition-colors">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $report->status_classes }}">
                                {{ $report->status_label }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-gray-100 text-gray-700">
                                {{ $report->issue_label }}
                            </span>
                            @if($question?->testSet?->section)
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700">
                                    {{ ucfirst($question->testSet->section->name) }}
                                </span>
                            @endif
                        </div>

                        <p class="text-sm font-medium text-gray-900 truncate">
                            @if($question)
                                Q{{ $question->order_number }} —
                                {{ \Illuminate\Support\Str::limit(strip_tags($question->content), 70) ?: 'Untitled question' }}
                            @else
                                <span class="text-gray-400 italic">Question deleted</span>
                            @endif
                        </p>

                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $question?->testSet?->title ?? 'Unknown test' }}
                            &middot; reported by <span class="font-medium text-gray-700">{{ $report->student?->name ?? 'Deleted user' }}</span>
                            &middot; {{ $report->created_at->diffForHumans() }}
                        </p>

                        @if($report->description)
                            <p class="text-xs text-gray-600 mt-1.5 bg-gray-50 border border-gray-100 rounded px-2.5 py-1.5">
                                {{ \Illuminate\Support\Str::limit($report->description, 160) }}
                            </p>
                        @endif
                    </div>

                    <a href="{{ route('admin.question-reports.show', $report) }}"
                       class="shrink-0 px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 text-center">
                        Review
                    </a>
                </div>
            @empty
                <div class="px-6 py-16 text-center">
                    <p class="text-sm font-medium text-gray-900">No reports</p>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ request()->hasAny(['status', 'issue_type', 'section', 'search'])
                            ? 'Nothing matches these filters.'
                            : 'Students have not reported any question yet.' }}
                    </p>
                </div>
            @endforelse
        </div>

        @if($reports->hasPages())
            <div>{{ $reports->links() }}</div>
        @endif
    </div>
</x-admin-layout>
