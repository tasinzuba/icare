{{--
    Unified Drag & Drop Question Component
    Used by: Reading & Listening Tests

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question (start number)
--}}

@props(['question', 'displayNumber'])

@php
    $sectionData = $question->section_specific_data ?? [];

    // Parse drag zones from content
    $content = $question->content;
    preg_match_all('/\[DRAG_(\d+)\]/', $content, $matches);
    $dragZoneNumbers = $matches[1] ?? [];

    // Get drag zone answers
    $dragZones = [];
    foreach ($dragZoneNumbers as $num) {
        if (isset($sectionData['drag_zones'][$num])) {
            $dragZones[] = [
                'number' => $num,
                'answer' => $sectionData['drag_zones'][$num]['answer'] ?? ''
            ];
        } else {
            $dragZones[] = [
                'number' => $num,
                'answer' => ''
            ];
        }
    }

    $options = $sectionData['draggable_options'] ?? [];
    $allowReuse = $sectionData['allow_reuse'] ?? true;

    // Process content to replace [DRAG_X] with drop boxes
    $processedContent = $content;

    foreach ($dragZones as $index => $zone) {
        $num = $zone['number'];
        $zoneNumber = $displayNumber + $index;

        $dropBoxHtml = '<span class="drop-box"
                             data-question-id="' . $question->id . '"
                             data-zone-number="' . $num . '"
                             data-zone-index="' . $index . '"
                             data-question-number="' . $zoneNumber . '"
                             data-allow-reuse="' . ($allowReuse ? '1' : '0') . '"
                             style="display: inline-flex; min-width: 120px; width: auto; height: 40px; border: 1px dashed #000000; border-radius: 4px; line-height: 38px; align-items: center; justify-content: center; background: white; font-size: 14px; padding: 0 20px; cursor: pointer; margin: 0 4px; vertical-align: middle;">
            <span class="placeholder-text" style="color: #000000; font-weight: 600; font-size: 14px;">' . $zoneNumber . '</span>
        </span>';

        $processedContent = preg_replace('/\[DRAG_' . $num . '\]/', $dropBoxHtml, $processedContent, 1);
    }
@endphp

<div class="question-item drag-drop-question" id="question-{{ $question->id }}" style="background: none; border: none; box-shadow: none; padding: 0; margin-bottom: 20px;">
    {{-- Flex container: Question Left, Options Right (Horizontal) --}}
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
                         style="min-width: 120px; padding: 10px 20px; background: white; border: 1px solid #d1d5db; border-radius: 4px; cursor: move; font-size: 14px; color: #1f2937; user-select: none; text-align: center;">
                        {{ $optionText }}
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Hidden inputs for each drag zone --}}
    @foreach($dragZones as $index => $zone)
        <input type="hidden"
               name="answers[{{ $question->id }}][zone_{{ $zone['number'] }}]"
               data-question-number="{{ $displayNumber + $index }}"
               data-zone-number="{{ $zone['number'] }}">
    @endforeach
</div>

<style>
/* Drag Drop Layout - Horizontal Options on Right */
.drag-drop-layout {
    display: flex;
    gap: 40px;
    align-items: flex-start;
}

.draggable-options-container {
    flex: 1;
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
    height: 40px;
    border: 1px dashed #000000;
    border-radius: 4px;
    line-height: 38px;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: white;
    font-size: 14px;
    padding: 0 20px !important;
    cursor: pointer;
    margin: 0 4px;
    vertical-align: middle;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.drop-box.drag-over {
    background: #f0fdf4 !important;
    border: 1px dashed #22c55e !important;
}

.drop-box.has-answer {
    min-width: auto;
    width: auto;
    padding: 0 12px;
    border: 1px solid #d1d5db;
    border-style: solid;
    background: white;
    cursor: move;
    color: #1f2937;
    font-weight: normal;
}

.drop-box .placeholder-text {
    color: #000000;
    font-weight: 600;
    font-size: 14px;
}

/* Draggable Options */
.draggable-option {
    min-width: 120px;
    padding: 10px 20px;
    background: white;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    cursor: move;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 400;
    color: #1f2937;
    text-align: center;
    user-select: none;
}

.draggable-option:hover:not(.placed) {
    background: #f9fafb;
    border-color: #9ca3af;
}

.draggable-option.dragging {
    opacity: 0.5;
    cursor: grabbing;
}

.draggable-option.placed {
    display: none !important;
    visibility: hidden !important;
}

/* Responsive: Stack on smaller screens */
@media (max-width: 768px) {
    .drag-drop-layout {
        flex-direction: column;
    }
    .draggable-options-container {
        width: 100%;
    }
}
</style>
