{{-- Listening Question Render Partial --}}
@php
    $questionType = $question->question_type;
    $isMultipleChoice = false;
    
    // Check if this is a multiple_choice question
    if ($questionType === 'multiple_choice') {
        $isMultipleChoice = true;
    }
@endphp

@switch($questionType)
    @case('fill_blanks')
        {{-- Fill in the Blanks Question - Using Unified Component --}}
        <div class="question-item" id="question-{{ $question->id }}">
            <x-question-types.dropdown-selection
                :question="$question"
                :displayNumber="$displayNumber"
            />
        </div>
        @break
        
    @case('single_choice')
        {{-- Single Choice Question --}}
        <div class="question-item single-choice-question" id="question-{{ $question->id }}">
            <div class="question-content" style="text-align: left;">
                <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question {{ $displayNumber }}</div>
                <div class="question-text" style="text-align: left;">{!! $question->content !!}</div>
            </div>

            @if($question->options && $question->options->count() > 0)
                <x-question-types.single-choice
                    :question="$question"
                    :displayNumber="$displayNumber"
                />
            @else
                <x-question-types.text-input
                    :question="$question"
                    :displayNumber="$displayNumber"
                />
            @endif
        </div>
        @break

    @case('multiple_choice')
        {{-- Multiple Choice Question --}}
        <div class="question-item multiple-choice-question" id="question-{{ $question->id }}">
            <div class="question-content" style="text-align: left;">
                @php
                    $correctCount = $question->options->where('is_correct', true)->count();
                    $hasMultipleCorrect = $correctCount > 1;
                @endphp

                @if($hasMultipleCorrect)
                    <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Questions {{ $displayNumber }}-{{ $displayNumber + $correctCount - 1 }}</div>
                @else
                    <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question {{ $displayNumber }}</div>
                @endif
                <div class="question-text" style="text-align: left;">{!! $question->content !!}</div>
            </div>

            @if($question->options && $question->options->count() > 0)
                <x-question-types.multiple-choice
                    :question="$question"
                    :displayNumber="$displayNumber"
                />
            @else
                <x-question-types.text-input
                    :question="$question"
                    :displayNumber="$displayNumber"
                />
            @endif
        </div>
        @break
        
    @case('dropdown_selection')
        {{-- Dropdown Selection Question - Using Unified Component --}}
        <div class="question-item" id="question-{{ $question->id }}">
            <x-question-types.dropdown-selection
                :question="$question"
                :displayNumber="$displayNumber"
            />
        </div>
        @break
        
    @case('drag_drop')
        {{-- Drag and Drop Question - Options on Right Side --}}
        <div class="question-item drag-drop-question" id="question-{{ $question->id }}" style="background: none; border: none; box-shadow: none; padding: 0; margin-bottom: 20px;">
            @php
                $sectionData = $question->section_specific_data ?? [];

                // Use drop_zones from section_specific_data (same as result page)
                $dropZones = $sectionData['drop_zones'] ?? [];
                $options = $sectionData['draggable_options'] ?? [];
                $allowReuse = $sectionData['allow_reuse'] ?? true;
                $dropZoneCount = count($dropZones);

                // Calculate question number range
                $startNum = $displayNumber;
                $endNum = $displayNumber + $dropZoneCount - 1;

                // Process content to replace [DRAG_X] with drop boxes
                $content = $question->content;
                $processedContent = $content;

                foreach ($dropZones as $zoneIndex => $zone) {
                    $zoneNumber = $displayNumber + $zoneIndex;

                    // Find pattern [DRAG_X] where X is zoneIndex+1 or just sequential
                    $pattern = '/\[DRAG_' . ($zoneIndex + 1) . '\]/';

                    $dropBoxHtml = '<span class="drop-box"
                                         data-question-id="' . $question->id . '"
                                         data-zone-number="' . $zoneIndex . '"
                                         data-zone-index="' . $zoneIndex . '"
                                         data-question-number="' . $zoneNumber . '"
                                         data-allow-reuse="' . ($allowReuse ? '1' : '0') . '"
                                         style="display: inline-flex; min-width: 120px; width: auto; height: 28px; border: 1px dashed #000000; border-radius: 4px; line-height: 26px; align-items: center; justify-content: center; background: white; font-size: 14px; padding: 0 12px; cursor: pointer; margin: 0 4px; vertical-align: middle;">
                        <span class="placeholder-text" style="color: #000000; font-weight: 600; font-size: 14px;">' . $zoneNumber . '</span>
                    </span>';

                    $processedContent = preg_replace($pattern, $dropBoxHtml, $processedContent, 1);
                }
            @endphp

            {{-- Question Number Header --}}
            <div class="question-header" style="margin-bottom: 15px; text-align: left;">
                @if($dropZoneCount > 1)
                    <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Questions {{ $startNum }}-{{ $endNum }}</div>
                @else
                    <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question {{ $displayNumber }}</div>
                @endif
                <div style="font-weight: 600; color: #1f2937; text-align: left;">Drag and drop the correct answers</div>
            </div>

            {{-- Flex Layout: Question Left, Options Right (Horizontal) --}}
            <div class="drag-drop-layout" style="display: flex; gap: 40px; align-items: flex-start;">
                {{-- Question Text with inline drop boxes (Left Side) --}}
                <div class="question-text" style="flex-shrink: 0; font-size: 15px; line-height: 2.4; color: #1f2937;">{!! $processedContent !!}</div>

                {{-- Draggable Options (Right Side - Horizontal Row) --}}
                <div class="draggable-options-container" style="flex: 1;">
                    <div class="draggable-options-grid" style="display: flex; flex-direction: row; flex-wrap: wrap; gap: 12px; padding: 0; background: none; border: none;">
                        @foreach($options as $optionIndex => $optionText)
                            <div class="draggable-option"
                                 draggable="true"
                                 data-option-value="{{ $optionText }}"
                                 data-option-letter="{{ chr(65 + $optionIndex) }}"
                                 style="min-width: fit-content; padding: 4px 12px; background: white; border: 1px solid rgb(235, 236, 237); border-radius: 4px; cursor: move; font-size: 14px; color: #1f2937; user-select: none; text-align: center;">
                                {{ $optionText }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Hidden inputs for each drop zone - use zone index for backend matching --}}
            @foreach($dropZones as $zoneIndex => $zone)
                <input type="hidden"
                       name="answers[{{ $question->id }}][zone_{{ $zoneIndex }}]"
                       data-question-number="{{ $displayNumber + $zoneIndex }}"
                       data-zone-number="{{ $zoneIndex }}">
            @endforeach
        </div>
        @break
    
    @default
        {{-- Fallback for any other type - treat as text input --}}
        <div class="question-item" id="question-{{ $question->id }}">
            <div class="question-content" style="text-align: left;">
                <div class="question-number" style="font-weight: 700; font-size: 15px; margin-bottom: 4px;">Question {{ $displayNumber }}</div>
                <div class="question-text" style="text-align: left;">{!! $question->content !!}</div>
            </div>

            <div class="answer-input">
                <x-question-types.text-input
                    :question="$question"
                    :displayNumber="$displayNumber"
                />
            </div>
        </div>
        @break
@endswitch

<style>
/* TinyMCE Content Styles */
.question-text strong,
.question-text b {
    font-weight: 700 !important;
    color: #000000 !important;
}

.question-text em,
.question-text i {
    font-style: italic !important;
}

.question-text u {
    text-decoration: underline !important;
}

/* Tables from TinyMCE */
.question-text table {
    width: auto !important;
    max-width: 100% !important;
    border-collapse: collapse !important;
    margin: 10px 0 !important;
    font-size: 14px !important;
    background: white !important;
}

.question-text table th {
    background-color: #f3f4f6 !important;
    padding: 8px 12px !important;
    text-align: left !important;
    font-weight: 700 !important;
    border: 1px solid #000000 !important;
    color: #000000 !important;
}

.question-text table td {
    padding: 6px 12px !important;
    border: 1px solid #000000 !important;
    color: #1f2937 !important;
    background: white !important;
}

/* Lists from TinyMCE */
.question-text ul,
.question-text ol {
    margin: 10px 0 10px 20px !important;
    padding-left: 20px !important;
}

.question-text ul li,
.question-text ol li {
    margin-bottom: 5px !important;
    line-height: 1.6 !important;
}

/* Fill in Blanks & Dropdown - Screenshot Style */
.dropdown-selection-question {
    line-height: 2.2 !important;
    font-size: 16px !important;
    color: #374151 !important;
}

.inline-blank {
    width: 120px !important;
    display: inline-block !important;
    margin: 4px 6px !important;
    padding: 2px 8px !important;
    border: 1px solid rgb(235, 236, 237) !important;
    border-radius: 4px !important;
    background-color: rgb(247, 247, 248) !important;
    font-size: 14px !important;
    font-weight: normal !important;
    text-align: center !important;
    color: #374151 !important;
    outline: none !important;
    transition: all 0.2s ease !important;
    vertical-align: middle !important;
    height: 28px !important;
}

.inline-blank:focus {
    background-color: #eff6ff !important;
    border-color: #3b82f6 !important;
    outline: 2px solid #3b82f6 !important;
    outline-offset: -1px !important;
}

.inline-blank:focus::placeholder {
    color: transparent !important;
}

.inline-blank::placeholder {
    text-align: center !important;
    color: #374151 !important;
    font-weight: 600 !important;
    opacity: 1 !important;
}

/* Dropdown Question Layout - Title on top, dropdowns below */
.dropdown-question-item {
    margin-bottom: 20px !important;
}

.dropdown-question-header {
    display: flex !important;
    align-items: baseline !important;
    margin-bottom: 15px !important;
    font-size: 15px !important;
    color: #1f2937 !important;
}

.dropdown-question-header .question-title {
    flex: 1 !important;
}

.dropdown-options-container {
    margin-left: 25px !important;
}

/* Dropdown Answer Row */
.dropdown-answer-row {
    display: flex !important;
    align-items: center !important;
    margin-bottom: 10px !important;
}

.dropdown-question-number {
    font-weight: 700 !important;
    font-size: 15px !important;
    min-width: 30px !important;
    color: #1f2937 !important;
}

/* Dropdown Wrapper - Fixed Width for Consistency */
.dropdown-wrapper {
    position: relative !important;
    display: inline-block !important;
    width: 130px !important;
    vertical-align: middle !important;
}

/* Dropdown Select */
.dropdown {
    width: 100% !important;
    padding: 0 20px 0 8px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 4px !important;
    background-color: #fff !important;
    color: #374151 !important;
    font-size: 13px !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    outline: none !important;
    height: 36px !important;
    line-height: 34px !important;
    text-overflow: ellipsis !important;
    overflow: hidden !important;
    white-space: nowrap !important;
}

.dropdown:hover {
    border-color: #9ca3af !important;
}

.dropdown:focus {
    outline: none !important;
    border-color: #6b7280 !important;
    box-shadow: 0 0 0 2px rgba(156, 163, 175, 0.15) !important;
}

/* SVG Arrow */
.dropdown-arrow {
    position: absolute !important;
    right: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 12px !important;
    height: 12px !important;
    color: #6b7280 !important;
    pointer-events: none !important;
}

/* Answered State */
.dropdown[data-answered="true"] {
    background-color: #f0fdf4 !important;
    border-color: #86efac !important;
}

/* Remove instruction background styling */
.question-instruction {
    font-size: 14px !important;
    color: #1f2937 !important;
    margin-bottom: 16px !important;
    font-weight: 600 !important;
    line-height: 1.6 !important;
    /* Remove all background and border styling */
    background: none !important;
    padding: 0 !important;
    border: none !important;
}

.question-instruction p {
    margin: 0 0 8px 0 !important;
}

.question-instruction p:last-child {
    margin-bottom: 0 !important;
}

/* IELTS Options Styling - Same as Reading Test */
.ielts-options {
    margin-left: 24px;
    margin-top: 8px;
}

.ielts-option {
    margin-bottom: 6px !important;
    display: flex !important;
    align-items: center !important;
    padding: 0 !important;
    background: none !important;
}

.ielts-option input[type="radio"],
.ielts-option input[type="checkbox"] {
    -webkit-appearance: radio !important;
    -moz-appearance: radio !important;
    appearance: radio !important;
    margin: 0 !important;
    margin-right: 8px !important;
    width: 14px !important;
    height: 14px !important;
    cursor: pointer !important;
    padding: 0 !important;
}

.ielts-option input[type="checkbox"] {
    -webkit-appearance: checkbox !important;
    -moz-appearance: checkbox !important;
    appearance: checkbox !important;
}

.ielts-option label {
    cursor: pointer !important;
    font-size: 14px !important;
    color: #000000 !important;
    font-weight: normal !important;
    margin: 0 !important;
    padding: 0 !important;
    line-height: 1.4 !important;
}

/* Legacy Options Styling (Remove these in future) */
.options-list {
    margin: 20px 0;
    margin-left: 47px;
}

.option-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 12px;
    cursor: pointer;
    padding: 12px;
    border-radius: 6px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.option-item:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
}

.option-radio,
.option-checkbox {
    margin-top: 2px;
    margin-right: 12px;
    width: 18px;
    height: 18px;
    cursor: pointer;
    flex-shrink: 0;
    accent-color: #1f2937;
}

.option-label {
    flex: 1;
    font-size: 15px;
    line-height: 1.6;
    color: #1f2937;
    cursor: pointer;
    display: flex;
    align-items: baseline;
}

.option-label strong {
    font-weight: 700;
    margin-right: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    height: 28px;
    background: #f3f4f6;
    border-radius: 4px;
    font-size: 14px;
    color: #374151;
    transition: all 0.2s;
}

/* Selected state */
.option-item:has(input:checked) {
    background: #f3f4f6;
    border: 1px solid #374151;
    margin-left: -1px;
    padding: 11px;
}

.option-item:has(input:checked) .option-label {
    color: #111827;
    font-weight: 600;
}

.option-item:has(input:checked) .option-label strong {
    background: #1f2937;
    color: white;
}

/* Drag and Drop Styles */

/* Drag Drop Layout - Horizontal Options on Right */
.drag-drop-layout {
    display: flex !important;
    gap: 40px !important;
    align-items: flex-start !important;
}

.draggable-options-container {
    flex: 1 !important;
}

.draggable-options-grid {
    display: flex !important;
    flex-direction: row !important;
    flex-wrap: wrap !important;
    gap: 12px !important;
    padding: 0 !important;
    background: none !important;
    border: none !important;
}

/* Drop box styles */
.drop-box {
    display: inline-flex !important;
    min-width: 120px !important;
    width: auto !important;
    height: 28px !important;
    border: 1px dashed #000000 !important;
    border-radius: 4px !important;
    line-height: 26px !important;
    align-items: center !important;
    justify-content: center !important;
    transition: all 0.2s !important;
    background: white !important;
    font-size: 14px !important;
    padding: 0 12px !important;
    cursor: pointer !important;
    margin: 0 4px !important;
    vertical-align: middle !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    white-space: nowrap !important;
}

.drop-box.drag-over {
    background: #f0fdf4 !important;
    border: 1px dashed #22c55e !important;
}

.drop-box.has-answer {
    min-width: auto !important;
    width: auto !important;
    padding: 0 12px !important;
    border: 1px solid #d1d5db !important;
    border-style: solid !important;
    background: white !important;
    cursor: move !important;
    color: #1f2937 !important;
    font-weight: normal !important;
}

.drop-box .placeholder-text {
    color: #000000 !important;
    font-weight: 600 !important;
    font-size: 14px !important;
}

/* Draggable Options */
.draggable-option {
    min-width: fit-content !important;
    padding: 4px 12px !important;
    background: white !important;
    border: 1px solid rgb(235, 236, 237) !important;
    border-radius: 4px !important;
    cursor: move !important;
    transition: all 0.2s !important;
    font-size: 14px !important;
    font-weight: 400 !important;
    color: #1f2937 !important;
    text-align: center !important;
    user-select: none !important;
}

.draggable-option:hover:not(.placed) {
    background: #f9fafb !important;
    border-color: #9ca3af !important;
}

.draggable-option.dragging {
    opacity: 0.5 !important;
    cursor: grabbing !important;
}

.draggable-option.placed {
    display: none !important;
    visibility: hidden !important;
}
</style>
