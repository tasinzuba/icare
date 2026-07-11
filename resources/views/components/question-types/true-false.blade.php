{{--
    Unified True/False & Yes/No Question Component
    Used by: Reading & Listening Tests

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question
--}}

@props(['question', 'displayNumber'])

<div class="ielts-options" style="margin-left: 24px; margin-top: 8px;">
    @foreach ($question->options as $option)
        <div class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important; padding: 0 !important; background: none !important;">
            <input type="radio"
                   name="answers[{{ $question->id }}]"
                   id="option-{{ $question->id }}-{{ $option->id }}"
                   value="{{ $option->id }}"
                   style="-webkit-appearance: radio !important; -moz-appearance: radio !important; appearance: radio !important; margin: 0 !important; margin-right: 8px !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important;"
                   data-question-number="{{ $displayNumber }}">
            <label for="option-{{ $question->id }}-{{ $option->id }}" style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; margin: 0 !important; padding: 0 !important; line-height: 1.4 !important;">
                {{ strtoupper($option->content) }}
            </label>
        </div>
    @endforeach
</div>
