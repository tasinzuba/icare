{{--
    Unified Sentence Completion Question Component
    Used by: Reading & Listening Tests

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question (start number)
    - $questionNumbers: Array of question numbers for each sentence (optional)
    - $showWordList: Boolean to show/hide word list (optional, default true)
--}}

@props(['question', 'displayNumber', 'questionNumbers' => [], 'showWordList' => true])

@php
    $sectionData = $question->section_specific_data;
    $hasSentenceCompletionData = isset($sectionData['sentence_completion']);
@endphp

@if($hasSentenceCompletionData)
    @php
        $scData = $sectionData['sentence_completion'];

        // Get question range for title
        $startNum = $displayNumber;
        $sentenceCount = isset($scData['sentences']) ? count($scData['sentences']) : 0;
        $endNum = $startNum + $sentenceCount - 1;
    @endphp

    {{-- Question Title with Range --}}
    <div style="margin-bottom: 16px; font-weight: 700; font-size: 15px; color: #111827;">
        Questions {{ $startNum }}-{{ $endNum }}
    </div>

    @if($showWordList)
        {{-- Word List Box --}}
        @if(isset($scData['options']) && count($scData['options']) > 0)
            <div class="word-list-box" style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 16px; border: 1px solid #e5e7eb;">
                <div style="font-weight: 600; margin-bottom: 12px; font-size: 15px; color: #000000; display: flex; align-items: center;">
                    <svg style="width: 18px; height: 18px; margin-right: 8px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    Word List
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 8px;">
                    @foreach($scData['options'] as $option)
                        <div class="word-list-item" style="padding: 4px 8px; background: #fff; border-radius: 4px;">
                            <strong style="color: #000000; font-size: 15px;">{{ $option['id'] }}</strong>
                            <span style="color: #333333; margin-left: 6px;">{{ $option['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif

    {{-- Sentences --}}
    @if(isset($scData['sentences']))
        <div style="margin-top: 15px;">
            @foreach($scData['sentences'] as $sentenceIndex => $sentence)
                @php
                    // Get the display number from questionNumbers array or calculate
                    $displayNum = isset($questionNumbers[$sentenceIndex]) ? $questionNumbers[$sentenceIndex] : ($displayNumber + $sentenceIndex);
                    $sentenceText = $sentence['text'];

                    // Replace [GAP] with dropdown
                    $dropdownHtml = '<select name="answers[' . $question->id . '_q' . $displayNum . ']" '
                                  . 'class="sc-dropdown visible-dropdown" '
                                  . 'data-question-number="' . $displayNum . '" '
                                  . 'data-question-id="' . $question->id . '" '
                                  . 'style="display: inline-block; margin: 0 4px; padding: 2px 6px; border: 1px solid #666666; border-radius: 2px; font-size: 12px; font-weight: 500; min-width: 40px; max-width: 50px; background: #ffffff; color: #000000; cursor: pointer; vertical-align: baseline; line-height: normal;">';
                    $dropdownHtml .= '<option value="" style="color: #666666;">Select</option>';

                    foreach($scData['options'] as $option) {
                        $dropdownHtml .= '<option value="' . $option['id'] . '">' . $option['id'] . '</option>';
                    }

                    $dropdownHtml .= '</select>';

                    $processedText = str_replace('[GAP]', $dropdownHtml, $sentenceText);

                    // SMART FALLBACK: Handle cases where [GAP] might be missing
                    if (strpos($processedText, '<select') === false) {
                        if (trim($sentenceText)) {
                            if (preg_match('/[.!?]\s*$/', $sentenceText)) {
                                $processedText = preg_replace('/([.!?]\s*)$/', ' ' . $dropdownHtml . '$1', $sentenceText);
                            } else {
                                $processedText = trim($sentenceText) . ' ' . $dropdownHtml;
                            }
                        } else {
                            $processedText = $dropdownHtml;
                        }
                    }
                @endphp

                <div style="margin-bottom: 10px; display: flex; align-items: baseline;">
                    <span style="font-weight: 700; min-width: 35px; margin-right: 8px; font-size: 14px; color: #1f2937;">{{ $displayNum }}.</span>
                    <div style="flex: 1; font-size: 14px; line-height: 1.6; color: #374151;">{!! $processedText !!}</div>
                </div>
            @endforeach
        </div>
    @endif
@else
    {{-- Fallback to text input --}}
    <input type="text"
           name="answers[{{ $question->id }}]"
           style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 4px; font-size: 14px;"
           placeholder="Enter your answer"
           maxlength="100"
           data-question-number="{{ $displayNumber }}">
@endif
