<x-admin-layout>
    <x-slot:title>Preview — {{ $testSet->title }}</x-slot>

    <div class="max-w-6xl mx-auto">
        {{-- Top bar --}}
        <div class="mb-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.test-sets.show', $testSet) }}"
                   class="w-9 h-9 rounded-lg bg-white border border-slate-200 text-slate-600 hover:text-slate-900 hover:bg-slate-50 inline-flex items-center justify-center transition shadow-sm">
                    <i class="fas fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold text-slate-900">{{ $testSet->title }}</h1>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-semibold rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            <i class="fas fa-eye text-[10px]"></i> Preview Mode
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ ucfirst($testSet->section->name ?? '—') }} · {{ $testSet->questions->count() }} questions
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.test-sets.edit', $testSet) }}"
                   class="inline-flex items-center px-3 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-sm font-medium transition">
                    <i class="fas fa-edit mr-1.5 text-xs"></i> Edit
                </a>
                <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}"
                   class="inline-flex items-center px-3 py-2 rounded-lg bg-[#C8102E] text-white hover:bg-[#A00E27] text-sm font-medium transition shadow-sm">
                    <i class="fas fa-plus mr-1.5 text-xs"></i> Add Question
                </a>
            </div>
        </div>

        {{-- Read-only banner --}}
        <div class="mb-5 px-4 py-2.5 rounded-lg bg-amber-50 border border-amber-200 flex items-center gap-2 text-sm text-amber-800">
            <i class="fas fa-info-circle text-amber-600"></i>
            <span>This is a read-only preview. Inputs are visible but disabled — no answers are saved here.</span>
        </div>

        @if($testSet->questions->isEmpty())
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                <i class="fas fa-clipboard-question text-5xl text-slate-300 mb-3"></i>
                <p class="text-slate-500 mb-4">No questions added yet to this test set.</p>
                <a href="{{ route('admin.questions.create', ['test_set' => $testSet->id]) }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-[#C8102E] text-white hover:bg-[#A00E27] text-sm font-medium">
                    <i class="fas fa-plus mr-1.5"></i> Add First Question
                </a>
            </div>
        @else
            @foreach($questionsByPart as $partNumber => $partQuestions)
                <div class="mb-6 bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    {{-- Part header --}}
                    <div class="px-5 py-3 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-md bg-slate-900 text-white text-xs font-bold">
                                {{ $partNumber ?: '—' }}
                            </span>
                            <h2 class="text-sm font-semibold text-slate-900">Part {{ $partNumber ?: 'Unspecified' }}</h2>
                            <span class="text-[11px] text-slate-500">· {{ $partQuestions->count() }} questions</span>
                        </div>
                    </div>

                    {{-- Questions --}}
                    <div class="divide-y divide-slate-100 pointer-events-none select-none" style="opacity: 1;">
                        @foreach($partQuestions as $q)
                            @include('admin.test-sets.preview-question', ['q' => $q, 'displayNumber' => $loop->iteration])
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @push('styles')
    <style>
        .preview-question input[type=text],
        .preview-question input[type=radio],
        .preview-question input[type=checkbox],
        .preview-question select,
        .preview-question textarea {
            pointer-events: none;
        }
        .preview-question { padding: 18px 22px; }
        .preview-question .q-num {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 28px; height: 28px; padding: 0 8px;
            background: #C8102E; color: #fff;
            border-radius: 6px; font-weight: 700; font-size: 12px;
            margin-right: 10px;
        }
        .preview-question .q-type-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 999px;
            background: #eff6ff; color: #1d4ed8;
            font-size: 11px; font-weight: 600;
        }
        .preview-question .q-content { font-size: 14px; color: #1e293b; line-height: 1.6; margin: 8px 0; }
        .preview-question .q-options { display: flex; flex-direction: column; gap: 6px; margin-top: 10px; }
        .preview-question .q-option {
            display: flex; align-items: center; gap: 8px;
            padding: 6px 10px; border-radius: 6px;
            background: #f8fafc; border: 1px solid #e2e8f0;
            font-size: 13px; color: #334155;
        }
        .preview-question .q-option.correct {
            background: #f0fdf4; border-color: #86efac; color: #166534; font-weight: 600;
        }
        .preview-question .q-option .opt-marker {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50%;
            background: #e2e8f0; color: #475569; font-size: 11px; font-weight: 700;
        }
        .preview-question .q-option.correct .opt-marker {
            background: #22c55e; color: #fff;
        }
        .preview-question .q-input-placeholder {
            display: inline-block; min-width: 110px; padding: 4px 10px;
            background: #fff; border: 1px solid #cbd5e1; border-radius: 4px;
            font-size: 13px; color: #94a3b8;
        }
        .preview-question .q-meta {
            display: flex; gap: 12px; flex-wrap: wrap;
            font-size: 11px; color: #64748b; margin-top: 8px;
        }
        .preview-question img { max-width: 100%; height: auto; border-radius: 6px; margin: 8px 0; }
    </style>
    @endpush
</x-admin-layout>
