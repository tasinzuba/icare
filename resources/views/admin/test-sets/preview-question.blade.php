@php
    $typeLabels = [
        'single_choice' => 'Single Choice',
        'multiple_choice' => 'Multiple Choice',
        'fill_blanks' => 'Fill Blanks',
        'dropdown_selection' => 'Matching Letters',
        'drag_drop' => 'Drag & Drop',
        'matching_headings' => 'Matching Headings',
        'sentence_completion' => 'Sentence Completion',
        'summary_completion' => 'Summary Completion',
        'note_completion' => 'Note Completion',
        'short_answer' => 'Short Answer',
        'true_false' => 'True/False/Not Given',
        'form_completion' => 'Form Completion',
    ];
    $typeLabel = $typeLabels[$q->question_type] ?? ucwords(str_replace('_', ' ', $q->question_type));
@endphp

<div class="preview-question">
    <div class="flex items-start justify-between gap-3 mb-2">
        <div class="flex items-start gap-3 flex-1 min-w-0">
            <span class="q-num">{{ $q->order_number ?? $displayNumber }}</span>
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="q-type-badge">
                        <i class="fas fa-tag text-[9px]"></i> {{ $typeLabel }}
                    </span>
                    @if($q->marks)
                        <span class="text-[11px] text-slate-500">{{ $q->marks }} mark{{ $q->marks > 1 ? 's' : '' }}</span>
                    @endif
                    @if($q->media_path)
                        <span class="text-[11px] inline-flex items-center gap-1 text-slate-500">
                            <i class="fas fa-paperclip text-[9px]"></i> Media attached
                        </span>
                    @endif
                </div>

                @if($q->instructions)
                    <div class="text-[12.5px] text-slate-600 italic mb-2 pl-3 border-l-2 border-slate-200">
                        {!! $q->instructions !!}
                    </div>
                @endif

                <div class="q-content">
                    @if($q->content)
                        {!! $q->content !!}
                    @else
                        <span class="text-slate-400 italic">(no content)</span>
                    @endif
                </div>

                @if($q->media_path)
                    @php
                        $section = $q->testSet->section->name ?? '';
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $q->media_path);
                        $isAudio = preg_match('/\.(mp3|wav|ogg|m4a)$/i', $q->media_path);
                    @endphp
                    @if($isImage)
                        <img src="{{ asset('storage/' . $q->media_path) }}" alt="Question media">
                    @elseif($isAudio)
                        <audio controls class="w-full mt-2"><source src="{{ asset('storage/' . $q->media_path) }}"></audio>
                    @endif
                @endif

                {{-- Options for choice-based questions --}}
                @if(in_array($q->question_type, ['single_choice', 'multiple_choice', 'true_false']) && $q->options && $q->options->count() > 0)
                    <div class="q-options">
                        @foreach($q->options as $idx => $opt)
                            <div class="q-option {{ $opt->is_correct ? 'correct' : '' }}">
                                <span class="opt-marker">{{ chr(65 + $idx) }}</span>
                                <span class="flex-1">{{ $opt->content ?? $opt->option_text ?? '—' }}</span>
                                @if($opt->is_correct)
                                    <i class="fas fa-check-circle text-emerald-600 text-sm"></i>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Dropdown / Matching Letters answer info --}}
                @if($q->question_type === 'dropdown_selection' && $q->section_specific_data)
                    @php $opts = $q->section_specific_data['dropdown_options'] ?? []; @endphp
                    @if(!empty($opts))
                        <div class="mt-2 text-[12px] text-slate-600">
                            <strong>Options:</strong>
                            @foreach($opts as $key => $val)
                                <span class="inline-block px-2 py-0.5 ml-1 bg-slate-100 rounded">{{ is_array($val) || is_object($val) ? $val[0] ?? '' : $val }}</span>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Drag & Drop options --}}
                @if($q->question_type === 'drag_drop' && $q->section_specific_data)
                    @php
                        $draggables = $q->section_specific_data['draggable_options'] ?? [];
                        $dropZones = $q->section_specific_data['drop_zones'] ?? [];
                    @endphp
                    @if(!empty($draggables))
                        <div class="mt-2 text-[12px] text-slate-600">
                            <strong>Draggable options:</strong>
                            @foreach($draggables as $w)
                                <span class="inline-block px-2 py-0.5 ml-1 bg-blue-50 text-blue-700 border border-blue-200 rounded">{{ $w }}</span>
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($dropZones))
                        <div class="mt-1 text-[12px] text-slate-600">
                            <strong>Correct answers:</strong>
                            @foreach($dropZones as $z)
                                <span class="inline-block px-2 py-0.5 ml-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded">
                                    Zone {{ $z['zone_number'] ?? '?' }} → {{ $z['answer'] ?? '?' }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                @endif

                {{-- Fill-blanks correct answers from question_blanks --}}
                @if(in_array($q->question_type, ['fill_blanks', 'sentence_completion', 'note_completion', 'summary_completion']) && $q->blanks && $q->blanks->count() > 0)
                    <div class="mt-2 text-[12px] text-slate-600">
                        <strong>Answers:</strong>
                        @foreach($q->blanks as $b)
                            <span class="inline-block px-2 py-0.5 ml-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded">
                                #{{ $b->blank_number }}: {{ $b->correct_answer }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <a href="{{ route('admin.questions.edit', $q) }}"
           class="flex-shrink-0 inline-flex items-center px-2.5 py-1.5 rounded-md text-[11px] font-medium text-slate-600 hover:text-[#C8102E] hover:bg-slate-50 transition pointer-events-auto select-auto">
            <i class="fas fa-pen mr-1 text-[10px]"></i> Edit
        </a>
    </div>
</div>
