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
    <textarea name="explanation" rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
              placeholder="Why this answer is correct — the student sees this on their result page.">{{ old('explanation', isset($question) ? $question->explanation : '') }}</textarea>
</div>

<div class="w-full">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Answer location in passage <span class="text-gray-400 font-normal">(optional)</span>
    </label>
    <input type="text" name="passage_reference"
           value="{{ old('passage_reference', isset($question) ? $question->passage_reference : '') }}"
           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
           placeholder="e.g. Q5">
    <p class="text-xs text-gray-500 mt-1">
        Wrap the answer text in the passage with <code class="bg-gray-100 px-1 rounded">&#123;&#123;Q5&#125;&#125;…&#123;&#123;Q5&#125;&#125;</code>,
        then put <strong>Q5</strong> here. On the result page the student can jump straight to it.
    </p>
</div>
