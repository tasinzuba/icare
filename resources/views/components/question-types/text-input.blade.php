{{--
    Unified Text Input Question Component
    Used by: Reading & Listening Tests
    For: summary_completion, short_answer, and default fallback

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question
--}}

@props(['question', 'displayNumber'])

<input type="text"
       name="answers[{{ $question->id }}]"
       style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;"
       placeholder="Enter your answer"
       maxlength="100"
       data-question-number="{{ $displayNumber }}"
       autocomplete="off"
       autocorrect="off"
       autocapitalize="off"
       spellcheck="false">
