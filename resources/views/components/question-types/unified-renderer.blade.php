{{--
    Unified Question Renderer Component
    Used by: Reading & Listening Tests

    This component renders any question type using the appropriate sub-component.
    It provides a single entry point for question rendering across all test interfaces.

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question

    Optional Props:
    - $questionNumbers: Array of sub-question numbers (for sentence_completion, matching_headings)
    - $showWordList: Boolean to show/hide word list for sentence_completion (default: true)
    - $testType: 'reading' or 'listening' (for type-specific handling)
--}}

@props([
    'question',
    'displayNumber',
    'questionNumbers' => [],
    'showWordList' => true,
    'testType' => 'reading'
])

@php
    $questionType = $question->question_type;
@endphp

@switch($questionType)
    @case('single_choice')
        <x-question-types.single-choice
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('multiple_choice')
        <x-question-types.multiple-choice
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('true_false')
    @case('yes_no')
        <x-question-types.true-false
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('matching')
    @case('matching_information')
    @case('matching_features')
        <x-question-types.matching-dropdown
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('fill_blanks')
    @case('dropdown_selection')
        <x-question-types.dropdown-selection
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('sentence_completion')
        <x-question-types.sentence-completion
            :question="$question"
            :displayNumber="$displayNumber"
            :questionNumbers="$questionNumbers"
            :showWordList="$showWordList"
        />
        @break

    @case('drag_drop')
        <x-question-types.drag-drop
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break

    @case('summary_completion')
    @case('short_answer')
    @default
        <x-question-types.text-input
            :question="$question"
            :displayNumber="$displayNumber"
        />
        @break
@endswitch
