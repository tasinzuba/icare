<x-admin-layout>
    <x-slot:title>Report #{{ $questionReport->id }}</x-slot>

    @php $question = $questionReport->question; @endphp

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white shadow-sm rounded-lg px-6 py-5">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <a href="{{ route('admin.question-reports.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; All reports</a>
                    <h1 class="text-xl font-semibold text-gray-900 mt-1">Report #{{ $questionReport->id }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $questionReport->issue_label }} &middot; {{ $questionReport->created_at->format('M d, Y g:i A') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $questionReport->status_classes }}">
                    {{ $questionReport->status_label }}
                </span>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- What the student said --}}
                <div class="bg-white shadow-sm rounded-lg px-6 py-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">What the student reported</h2>
                    <dl class="grid grid-cols-2 gap-3 text-sm mb-4">
                        <div>
                            <dt class="text-gray-500 text-xs">Student</dt>
                            <dd class="font-medium text-gray-900">{{ $questionReport->student?->name ?? 'Deleted user' }}</dd>
                            <dd class="text-xs text-gray-500">{{ $questionReport->student?->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 text-xs">Issue</dt>
                            <dd class="font-medium text-gray-900">{{ $questionReport->issue_label }}</dd>
                        </div>
                    </dl>

                    @if($questionReport->description)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $questionReport->description }}</p>
                        </div>
                    @else
                        <p class="text-sm text-gray-400 italic">No description given.</p>
                    @endif
                </div>

                {{-- The question itself --}}
                <div class="bg-white shadow-sm rounded-lg px-6 py-5">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <h2 class="text-sm font-bold text-gray-900">The question</h2>
                        @if($question)
                            <div class="flex gap-2">
                                <a href="{{ route('admin.questions.show', $question) }}"
                                   class="px-3 py-1.5 text-xs border border-gray-200 rounded-md text-gray-600 hover:bg-gray-50">View</a>
                                <a href="{{ route('admin.questions.edit', $question) }}"
                                   class="px-3 py-1.5 text-xs bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Edit question</a>
                            </div>
                        @endif
                    </div>

                    @if($question)
                        <p class="text-xs text-gray-500 mb-2">
                            {{ $question->testSet?->title }}
                            &middot; {{ ucfirst($question->testSet?->section?->name ?? '?') }}
                            &middot; Part {{ $question->part_number }}
                            &middot; Q{{ $question->order_number }}
                            &middot; {{ ucfirst(str_replace('_', ' ', $question->question_type)) }}
                        </p>
                        <div class="prose prose-sm max-w-none text-gray-800 border border-gray-200 rounded-lg px-4 py-3">
                            {!! $question->content !!}
                        </div>

                        @if($question->options->count())
                            <ul class="mt-3 space-y-1.5">
                                @foreach($question->options as $option)
                                    <li class="flex items-start gap-2 text-sm">
                                        <span class="mt-0.5 shrink-0 {{ $option->is_correct ? 'text-emerald-600' : 'text-gray-300' }}">
                                            {!! $option->is_correct ? '&#10003;' : '&bull;' !!}
                                        </span>
                                        <span class="{{ $option->is_correct ? 'font-semibold text-emerald-800' : 'text-gray-700' }}">
                                            {!! $option->content !!}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    @else
                        <p class="text-sm text-gray-400 italic">This question has been deleted.</p>
                    @endif
                </div>

                {{-- Other students who flagged the same question --}}
                @if($related->count())
                    <div class="bg-white shadow-sm rounded-lg px-6 py-5">
                        <h2 class="text-sm font-bold text-gray-900 mb-1">
                            Also reported by {{ $related->count() }} other {{ \Illuminate\Support\Str::plural('student', $related->count()) }}
                        </h2>
                        <p class="text-xs text-gray-500 mb-3">Several reports on one question usually means it really is wrong.</p>
                        <ul class="divide-y divide-gray-100">
                            @foreach($related as $other)
                                <li class="py-2.5">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-sm font-medium text-gray-900">{{ $other->student?->name ?? 'Deleted user' }}</span>
                                        <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 text-gray-600">{{ $other->issue_label }}</span>
                                        <span class="text-xs text-gray-400">{{ $other->created_at->diffForHumans() }}</span>
                                    </div>
                                    @if($other->description)
                                        <p class="text-xs text-gray-600">{{ \Illuminate\Support\Str::limit($other->description, 180) }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Action --}}
            <div class="space-y-6">
                <div class="bg-white shadow-sm rounded-lg px-6 py-5">
                    <h2 class="text-sm font-bold text-gray-900 mb-3">Review</h2>
                    <form method="POST" action="{{ route('admin.question-reports.update', $questionReport) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500">
                                @foreach(\App\Models\QuestionReport::STATUSES as $key => $label)
                                    <option value="{{ $key }}" @selected($questionReport->status === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Internal note <span class="text-gray-400">(optional)</span></label>
                            <textarea name="admin_note" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-indigo-500"
                                      placeholder="What you did about it. Students never see this.">{{ old('admin_note', $questionReport->admin_note) }}</textarea>
                        </div>

                        <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                            Save
                        </button>
                    </form>

                    @if($questionReport->reviewed_at)
                        <p class="text-xs text-gray-500 mt-3 pt-3 border-t border-gray-100">
                            Last reviewed by {{ $questionReport->reviewer?->name ?? 'someone since removed' }}
                            on {{ $questionReport->reviewed_at->format('M d, Y g:i A') }}
                        </p>
                    @endif
                </div>

                <div class="bg-white shadow-sm rounded-lg px-6 py-5">
                    <form method="POST" action="{{ route('admin.question-reports.destroy', $questionReport) }}"
                          onsubmit="return confirm('Delete this report? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 border border-red-200 text-red-600 rounded-md text-sm font-medium hover:bg-red-50">
                            Delete report
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
