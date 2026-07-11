{{-- Fill in the Gap Question Component --}}
@props(['question', 'displayNumber', 'attemptId' => null])

@php
    // Process content to replace blanks with input fields
    $content = $question->content;
    $blanks = $question->blanks;
    $blankIndex = 0;
    
    // Replace [____N____] with input fields
    $processedContent = preg_replace_callback('/\[____(\d+)____\]/', function($matches) use ($question, &$blankIndex, $blanks, $displayNumber) {
        $blankIndex++;
        $blankNum = $matches[1];
        $blank = $blanks->where('blank_number', $blankNum)->first();
        
        $inputId = "blank_{$question->id}_{$blankNum}";
        $inputName = "answers[{$question->id}][blanks][{$blankNum}]";
        
        return sprintf(
            '<span class="inline-flex items-center gap-1">
                <input type="text"
                       id="%s"
                       name="%s"
                       class="gap-input"
                       placeholder="%d"
                       data-question-id="%d"
                       data-blank-number="%d"
                       data-display-number="%d"
                       autocomplete="off">
            </span>',
            $inputId,
            $inputName,
            $displayNumber + $blankIndex - 1,
            $question->id,
            $blankNum,
            $displayNumber + $blankIndex - 1
        );
    }, $content);
@endphp

<div class="question-box fill-in-gap-question" id="question-{{ $question->id }}">
    <div class="question-content">
        {!! $processedContent !!}
    </div>
    
    @if($question->media_path)
        <div class="mt-3">
            <img src="{{ Storage::url($question->media_path) }}" 
                 alt="Question Image" 
                 class="max-w-full h-auto rounded">
        </div>
    @endif
</div>

<style>
/* gap-input styles moved to reading-test.css for consistency */
.question-number-box {
    display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-width adjustment for inputs
    document.querySelectorAll('.gap-input').forEach(input => {
        function adjustWidth() {
            const length = input.value.length;
            input.style.width = Math.max(100, length * 10 + 30) + 'px';
        }
        
        input.addEventListener('input', adjustWidth);
        adjustWidth(); // Initial adjustment
        
        // Track answer for navigation
        input.addEventListener('change', function() {
            const displayNum = this.dataset.displayNumber;
            const navBtn = document.querySelector(`.number-btn[data-display-number="${displayNum}"]`);
            
            if (navBtn) {
                if (this.value.trim()) {
                    navBtn.classList.add('answered');
                } else {
                    navBtn.classList.remove('answered');
                }
            }
        });
    });
});
</script>