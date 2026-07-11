{{--
    Unified Multiple Choice Question Component
    Used by: Reading & Listening Tests

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question (start number)

    Optional Props:
    - $correctCount: Number of correct answers (for "Choose TWO" type questions)
--}}

@props(['question', 'displayNumber', 'correctCount' => null])

@php
    // Calculate correct count if not provided
    $correctAnswerCount = $correctCount ?? $question->options->filter(fn($opt) => $opt->is_correct)->count();
    // If only 1 correct answer, treat as single choice behavior
    $isMultiAnswer = $correctAnswerCount > 1;
@endphp

<div class="ielts-options"
     style="margin-left: 24px; margin-top: 8px;"
     data-question-id="{{ $question->id }}"
     data-start-number="{{ $displayNumber }}"
     data-correct-count="{{ $correctAnswerCount }}">
    @foreach ($question->options as $optionIndex => $option)
        <div class="ielts-option" style="margin-bottom: 6px !important; display: flex !important; align-items: center !important; padding: 0 !important; background: none !important;">
            <input type="checkbox"
                   name="answers[{{ $question->id }}][]"
                   id="option-{{ $question->id }}-{{ $option->id }}"
                   value="{{ $option->id }}"
                   style="-webkit-appearance: checkbox !important; -moz-appearance: checkbox !important; appearance: checkbox !important; margin: 0 !important; margin-right: 8px !important; width: 14px !important; height: 14px !important; cursor: pointer !important; padding: 0 !important;"
                   data-question-id="{{ $question->id }}"
                   data-question-number="{{ $displayNumber }}"
                   data-correct-count="{{ $correctAnswerCount }}"
                   class="multiple-choice-checkbox">
            <label for="option-{{ $question->id }}-{{ $option->id }}" style="cursor: pointer !important; font-size: 14px !important; color: #000000 !important; font-weight: normal !important; margin: 0 !important; padding: 0 !important; line-height: 1.4 !important;">
                {{ $option->content }}
            </label>
        </div>
    @endforeach
</div>

{{-- Enforce selection limit: cannot select more than correctCount --}}
@if($isMultiAnswer)
<script>
(function() {
    const qId = '{{ $question->id }}';
    const maxSelect = {{ $correctAnswerCount }};

    function enforceLimit() {
        const checkboxes = document.querySelectorAll('input[name="answers[' + qId + '][]"]');
        const checked = document.querySelectorAll('input[name="answers[' + qId + '][]"]:checked');

        if (checked.length >= maxSelect) {
            // Disable unchecked checkboxes
            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    cb.disabled = true;
                    cb.parentElement.style.opacity = '0.4';
                    cb.parentElement.style.cursor = 'not-allowed';
                }
            });
        } else {
            // Re-enable all checkboxes
            checkboxes.forEach(cb => {
                cb.disabled = false;
                cb.parentElement.style.opacity = '1';
                cb.parentElement.style.cursor = 'pointer';
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('input[name="answers[' + qId + '][]"]');
        checkboxes.forEach(cb => {
            cb.addEventListener('change', enforceLimit);
        });
        // Run on load for restored answers (multiple delays to cover different restore timings)
        setTimeout(enforceLimit, 500);
        setTimeout(enforceLimit, 1500);
    });
})();
</script>
@endif
