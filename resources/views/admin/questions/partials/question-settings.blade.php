<!-- Question Type -->
<div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-2">Type <span class="text-red-500">*</span></label>
    <select id="question_type" name="question_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" required>
        <option value="">Select type...</option>
        @foreach($questionTypes as $key => $type)
            <option value="{{ $key }}" {{ old('question_type', isset($question) ? $question->question_type : '') == $key ? 'selected' : '' }}>{{ $type }}</option>
        @endforeach
    </select>
</div>

<!-- Question Number -->
<div class="w-full" id="order-number-wrapper">
    <label class="block text-sm font-medium text-gray-700 mb-2">Number <span class="text-red-500">*</span></label>
    <input type="number" name="order_number" value="{{ old('order_number', isset($question) ? $question->order_number : ($nextQuestionNumber ?? 1)) }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" min="0" required>
</div>

<!-- Part/Task Selection -->
@if(in_array($testSet->section->name, ['listening', 'reading', 'speaking', 'writing']))
<div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ $testSet->section->name === 'writing' ? 'Task' : 'Part' }} <span class="text-red-500">*</span>
    </label>
    @php
        $currentPartNumber = old('part_number', isset($question) ? $question->part_number : 1);
    @endphp
    <select name="part_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm" required>
        @if($testSet->section->name === 'listening')
            <option value="1" {{ $currentPartNumber == 1 ? 'selected' : '' }}>Part 1 (Social)</option>
            <option value="2" {{ $currentPartNumber == 2 ? 'selected' : '' }}>Part 2 (Monologue)</option>
            <option value="3" {{ $currentPartNumber == 3 ? 'selected' : '' }}>Part 3 (Discussion)</option>
            <option value="4" {{ $currentPartNumber == 4 ? 'selected' : '' }}>Part 4 (Lecture)</option>
        @elseif($testSet->section->name === 'reading')
            <option value="1" {{ $currentPartNumber == 1 ? 'selected' : '' }}>Passage 1</option>
            <option value="2" {{ $currentPartNumber == 2 ? 'selected' : '' }}>Passage 2</option>
            <option value="3" {{ $currentPartNumber == 3 ? 'selected' : '' }}>Passage 3</option>
        @elseif($testSet->section->name === 'speaking')
            <option value="1" {{ $currentPartNumber == 1 ? 'selected' : '' }}>Part 1 (Introduction)</option>
            <option value="2" {{ $currentPartNumber == 2 ? 'selected' : '' }}>Part 2 (Cue Card)</option>
            <option value="3" {{ $currentPartNumber == 3 ? 'selected' : '' }}>Part 3 (Discussion)</option>
        @elseif($testSet->section->name === 'writing')
            <option value="1" {{ $currentPartNumber == 1 ? 'selected' : '' }}>Task 1</option>
            <option value="2" {{ $currentPartNumber == 2 ? 'selected' : '' }}>Task 2</option>
        @endif
    </select>
</div>
@endif

<!-- Marks -->
<div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-2">Marks</label>
    <input type="number" name="marks" value="{{ old('marks', isset($question) ? $question->marks : 1) }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
           min="0" max="40">
</div>

{{-- Answer explanation + passage location. Both are shown to the student ONLY on the result
     page after they submit, never during the test. --}}
<div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Explanation <span class="text-gray-400 font-normal">(optional)</span>
    </label>
    {{-- Single box, used when the question is one question. Hidden automatically when the content
         contains several blanks/dropdowns, and the per-question boxes below take over. --}}
    <div id="explanation-single">
        <textarea name="explanation" rows="3"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  placeholder="Why this answer is correct — the student sees this on their result page.">{{ old('explanation', isset($question) ? $question->explanation : '') }}</textarea>
    </div>

    {{-- One box per numbered question, built from the blanks/dropdowns in the content. --}}
    <div id="explanation-multi" class="hidden space-y-2"></div>

    <p class="text-xs text-gray-500 mt-1" id="explanation-hint">
        Shown on the student's result page after they submit.
    </p>
</div>

@once
@push('scripts')
<script>
/**
 * Per-question explanation boxes.
 *
 * A question row with several blanks/dropdowns becomes several student questions, so the author
 * gets one box each, labelled with the number the student will actually see (order number + index).
 * They post as explanations[15], explanations[16], … and the controller folds them into the single
 * "[Q15] … [Q16] …" string the result page reads.
 */
(function () {
    const single = document.getElementById('explanation-single');
    const multi = document.getElementById('explanation-multi');
    const hint = document.getElementById('explanation-hint');
    if (!single || !multi) return;

    const singleBox = single.querySelector('textarea');
    const existing = singleBox ? singleBox.value : '';

    // Pull "[Q15] text" blocks out of whatever is already stored.
    function storedFor(number) {
        const re = new RegExp('\\[Q' + number + '\\]\\s*([\\s\\S]*?)(?=\\[Q\\d+\\]|$)');
        const m = existing.match(re);
        return m ? m[1].trim() : '';
    }

    function contentText() {
        if (window.tinymce && tinymce.get('content')) {
            return tinymce.get('content').getContent({ format: 'text' }) || '';
        }
        const el = document.getElementById('content');
        return el ? el.value : '';
    }

    function partCount() {
        const text = contentText();
        const blanks = (text.match(/\[____\d+____\]|\[BLANK_\d+\]/g) || []).length;
        const drops = (text.match(/\[DROPDOWN_\d+\]/g) || []).length;
        const drags = (text.match(/\[DRAG_\d+\]/g) || []).length;
        return blanks + drops + drags;
    }

    function firstNumber() {
        const el = document.querySelector('input[name="order_number"]');
        const n = el ? parseInt(el.value, 10) : 1;
        return Number.isFinite(n) && n > 0 ? n : 1;
    }

    function render() {
        const count = partCount();

        if (count < 2) {
            single.classList.remove('hidden');
            multi.classList.add('hidden');
            multi.innerHTML = '';
            if (hint) hint.textContent = 'Shown on the student’s result page after they submit.';
            return;
        }

        const start = firstNumber();
        const kept = {};
        multi.querySelectorAll('textarea[data-qnum]').forEach(function (t) {
            kept[t.getAttribute('data-qnum')] = t.value;
        });

        single.classList.add('hidden');
        multi.classList.remove('hidden');
        multi.innerHTML = Array.from({ length: count }, function (_, i) {
            const num = start + i;
            const val = kept[num] !== undefined ? kept[num] : storedFor(num);
            return '<div class="flex items-start gap-2">'
                + '<span class="text-xs font-bold text-blue-700 mt-2 w-9 shrink-0">Q' + num + '</span>'
                + '<textarea name="explanations[' + num + ']" data-qnum="' + num + '" rows="2"'
                + ' class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"'
                + ' placeholder="Why question ' + num + '’s answer is correct"></textarea>'
                + '</div>';
        }).join('');

        multi.querySelectorAll('textarea[data-qnum]').forEach(function (t) {
            t.value = kept[t.getAttribute('data-qnum')] !== undefined
                ? kept[t.getAttribute('data-qnum')]
                : storedFor(t.getAttribute('data-qnum'));
        });

        if (hint) hint.textContent = 'This question has ' + count + ' parts, so each numbered question gets its own note.';
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        setTimeout(render, 800); // after TinyMCE finishes initialising
        const order = document.querySelector('input[name="order_number"]');
        if (order) order.addEventListener('change', render);
        const type = document.getElementById('question_type');
        if (type) type.addEventListener('change', function () { setTimeout(render, 300); });
        setInterval(render, 2500); // content is edited inside TinyMCE, so poll for new blanks
    });
})();
</script>
@endpush
@endonce

{{-- No location field: the marker in the passage already names its question. Wrapping the answer
     text as {{Q5}}…{{Q5}} is enough for question 5 to offer a Location button. --}}
<div class="w-full">
    <p class="text-xs text-gray-500 bg-blue-50 border border-blue-100 rounded-md px-3 py-2">
        <i class="fas fa-lightbulb text-blue-400 mr-1"></i>
        <strong>Answer location:</strong> in the passage, wrap the answer text as
        <code class="bg-white px-1 rounded">&#123;&#123;Q5&#125;&#125;…&#123;&#123;Q5&#125;&#125;</code>
        for question 5. A Location button then appears on the student's result page and jumps there.
    </p>
</div>
