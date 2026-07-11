{{--
    Unified Matching Dropdown Question Component
    Used by: Reading & Listening Tests
    For: matching, matching_information, matching_features

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question
--}}

@props(['question', 'displayNumber'])

<div class="ielts-matching-dropdown">
    <select name="answers[{{ $question->id }}]"
            class="ielts-select"
            data-question-number="{{ $displayNumber }}"
            style="padding: 6px 10px; border: 1px solid #666; border-radius: 4px; font-size: 14px; background: #fff; cursor: pointer; min-width: 200px;">
        <option value="" disabled selected>Select your answer</option>
        @foreach ($question->options as $optionIndex => $option)
            <option value="{{ $option->id }}">
                {{ $option->content }}
            </option>
        @endforeach
    </select>
</div>
