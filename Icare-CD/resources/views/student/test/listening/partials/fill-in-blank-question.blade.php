{{-- Fill in the Blank Question Display for Listening --}}
@if(in_array($question->question_type, ['sentence_completion', 'note_completion', 'summary_completion']))
    <div class="question-item" id="question-{{ $question->id }}">
        <div class="question-content">
            <span class="question-number">
                @if($question->countBlanks() > 1)
                    {{ $displayNumber }}-{{ $displayNumber + $question->countBlanks() - 1 }}
                @else
                    {{ $displayNumber }}
                @endif
            </span>
            <div class="question-text">
                @php
                    $content = $question->content;
                    $blankCount = 0;
                    $startNumber = $displayNumber;
                    
                    // Process content to replace blanks with input fields
                    $processedContent = preg_replace_callback('/\[____(\d+)____\]/', function($matches) use (&$blankCount, $startNumber, $question) {
                        $blankId = $matches[1];
                        $currentNumber = $startNumber + $blankCount;
                        $blankCount++;
                        
                        return '<span class="inline-blank-wrapper">
                            <label class="blank-number">' . $currentNumber . '</label>
                            <input type="text" 
                                   name="answers[' . $question->id . '_blank_' . $blankId . ']"
                                   class="inline-blank-input"
                                   data-question-number="' . $currentNumber . '"
                                   placeholder="_______________"
                                   autocomplete="off">
                        </span>';
                    }, $content);
                @endphp
                {!! $processedContent !!}
            </div>
        </div>
        
        @if($question->instructions)
            <div class="question-instructions">
                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                {{ $question->instructions }}
            </div>
        @endif
    </div>
    
    @php 
        $currentQuestionNumber += $question->countBlanks() ?: 1; 
    @endphp
@endif

<style>
/* Fill in the Blank Styles */
.inline-blank-wrapper {
    display: inline-flex;
    align-items: center;
    margin: 0 4px;
    vertical-align: middle;
}

.blank-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    background: #333;
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
    margin-right: 6px;
    flex-shrink: 0;
}

.inline-blank-input {
    display: inline-block;
    width: 150px;
    padding: 4px 8px;
    border: none;
    border-bottom: 2px solid #333;
    font-size: 14px;
    font-weight: 500;
    background: transparent;
    outline: none;
    transition: all 0.2s;
}

.inline-blank-input:focus {
    border-bottom-color: #4A90E2;
    background: #f0f7ff;
}

.inline-blank-input::placeholder {
    color: #999;
    font-weight: normal;
}

/* Question Instructions */
.question-instructions {
    margin-top: 12px;
    padding: 8px 12px;
    background: #f0f7ff;
    border-left: 3px solid #4A90E2;
    font-size: 13px;
    color: #333;
    border-radius: 0 4px 4px 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .inline-blank-input {
        width: 120px;
        font-size: 13px;
    }
    
    .blank-number {
        width: 20px;
        height: 20px;
        font-size: 11px;
    }
    
    .question-instructions {
        font-size: 12px;
    }
}

@media (max-width: 480px) {
    .inline-blank-wrapper {
        display: block;
        margin: 8px 0;
    }
    
    .inline-blank-input {
        width: 100%;
        max-width: 200px;
        margin-top: 4px;
    }
}
</style>