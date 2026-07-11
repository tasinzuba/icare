{{--
    Unified Dropdown Selection / Fill Blanks Question Component
    Used by: Reading & Listening Tests
    For: fill_blanks, dropdown_selection

    Required Props:
    - $question: Question model
    - $displayNumber: Display number for the question
--}}

@props(['question', 'displayNumber'])

@php
    $dropdownContent = $question->content;
    $dropdownData = $question->section_specific_data;

    // Clean Word HTML: Remove problematic inline styles from spans containing dropdowns
    // This fixes the issue where Word HTML styles override dropdown display
    $dropdownContent = preg_replace_callback(
        '/<span([^>]*style="[^"]*")([^>]*)>([^<]*\[DROPDOWN_\d+\][^<]*)<\/span>/i',
        function($matches) {
            // Extract the text content and dropdown placeholder
            $text = $matches[3];
            // Return the text without the problematic span wrapper
            return $text;
        },
        $dropdownContent
    );

    // Also strip color and letter-spacing from ALL spans to prevent inheritance
    $dropdownContent = preg_replace(
        '/(<span[^>]*style="[^"]*)(color:[^;"]+;?)/i',
        '$1',
        $dropdownContent
    );
    $dropdownContent = preg_replace(
        '/(<span[^>]*style="[^"]*)(letter-spacing:[^;"]+;?)/i',
        '$1',
        $dropdownContent
    );

    // Counter for dropdown question numbers
    $dropdownCounter = $displayNumber;

    // Process inline dropdowns [DROPDOWN_X] pattern
    if (isset($dropdownData['dropdown_options'])) {
        $dropdownContent = preg_replace_callback('/\[DROPDOWN_(\d+)\]/', function($matches) use ($question, $dropdownData, &$dropdownCounter) {
            $dropdownNum = $matches[1];
            $currentNum = $dropdownCounter;
            $dropdownCounter++;
            $options = isset($dropdownData['dropdown_options'][$dropdownNum]) ? explode(',', $dropdownData['dropdown_options'][$dropdownNum]) : [];

            // Inline question number + dropdown with inline styles to override Word HTML
            $selectHtml = '<span style="font-weight: 700; font-size: 15px; margin-right: 8px; color: #374151 !important;">' . $currentNum . '.</span>';
            $selectHtml .= '<span class="dropdown-wrapper" style="display: inline-block; position: relative; vertical-align: middle;">';
            $selectHtml .= '<select name="answers[' . $question->id . '][dropdown_' . $dropdownNum . ']" '
                       . 'class="dropdown" '
                       . 'data-question-number="' . $currentNum . '" '
                       . 'style="width: 70px; min-width: 70px; padding: 4px 8px; border: 2px solid #3b82f6; border-radius: 4px; background-color: #fff; color: #000000 !important; font-size: 14px; font-weight: 600; font-family: Arial, sans-serif; -webkit-appearance: menulist; -moz-appearance: menulist; appearance: menulist; cursor: pointer; outline: none; height: 32px; letter-spacing: normal; text-indent: 0; vertical-align: middle;">';
            $selectHtml .= '<option value="" style="color: #6b7280;">Select</option>';

            foreach ($options as $option) {
                $selectHtml .= '<option value="' . trim($option) . '" style="color: #000000; background-color: #ffffff;">' . trim($option) . '</option>';
            }

            $selectHtml .= '</select>';
            $selectHtml .= '<svg class="dropdown-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
            $selectHtml .= '</span>';
            return $selectHtml;
        }, $dropdownContent);
    }

    // Process [____X____] pattern (Listening style blanks) - with bold placeholder number
    $blankCounter = $displayNumber;
    $dropdownContent = preg_replace_callback('/\[____(\d+)____\]/', function($matches) use ($question, &$blankCounter) {
        $blankNum = $matches[1];
        $currentNum = $blankCounter;
        $blankCounter++;

        $inputHtml = '<input type="text" '
                   . 'name="answers[' . $question->id . '][blank_' . $blankNum . ']" '
                   . 'class="inline-blank" '
                   . 'placeholder="' . $currentNum . '" '
                   . 'data-question-number="' . $currentNum . '" '
                   . 'style="width: 120px; display: inline-block; margin: 4px 6px; padding: 2px 8px; height: 28px; border: 1px solid rgb(235, 236, 237); border-radius: 4px; background-color: rgb(247, 247, 248); font-size: 14px; font-weight: 600; text-align: center; color: #374151; outline: none; vertical-align: middle;" '
                   . 'autocomplete="off" '
                   . 'autocorrect="off" '
                   . 'autocapitalize="off" '
                   . 'spellcheck="false">';
        return $inputHtml;
    }, $dropdownContent);
@endphp

<div class="dropdown-selection-question" style="font-size: 16px; line-height: 2.2; color: #374151;">
    {!! $dropdownContent !!}
</div>

<style>
/* Dropdown Wrapper - Fixed Width for Consistency */
.dropdown-wrapper,
span .dropdown-wrapper {
    all: revert !important;
    position: relative !important;
    display: inline-block !important;
    width: 70px !important;
    min-width: 70px !important;
    vertical-align: middle !important;
    letter-spacing: normal !important;
    font-size: 14px !important;
    color: #000000 !important;
}

/* Dropdown Select - Use NATIVE appearance to ensure text visibility */
.dropdown,
span .dropdown,
span span .dropdown,
p .dropdown,
div .dropdown {
    all: revert !important;
    width: 70px !important;
    min-width: 70px !important;
    padding: 4px 8px !important;
    border: 2px solid #3b82f6 !important;
    border-radius: 4px !important;
    background-color: #fff !important;
    color: #000000 !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    font-family: Arial, sans-serif !important;
    -webkit-appearance: menulist !important;
    -moz-appearance: menulist !important;
    appearance: menulist !important;
    cursor: pointer !important;
    outline: none !important;
    height: 32px !important;
    letter-spacing: normal !important;
    text-indent: 0 !important;
    vertical-align: middle !important;
}

.dropdown:hover {
    border-color: #9ca3af !important;
    background-color: #f9fafb !important;
}

.dropdown:focus {
    outline: none !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.25) !important;
    background-color: #fff !important;
    color: #000000 !important;
}

/* SVG Arrow */
.dropdown-arrow {
    position: absolute;
    right: 6px;
    top: 50%;
    transform: translateY(-50%);
    width: 14px;
    height: 14px;
    color: #374151;
    pointer-events: none;
    z-index: 1;
}

/* Answered State */
.dropdown[data-answered="true"] {
    background-color: #f0fdf4;
    border-color: #86efac;
}

/* Dropdown Options */
.dropdown option {
    color: #000000 !important;
    background-color: #ffffff !important;
    padding: 8px !important;
    font-size: 14px !important;
}

.dropdown option:first-child {
    color: #6b7280 !important;
}

/* Ensure selected value is visible */
.dropdown:not(:focus) {
    color: #000000 !important;
}

/* Inline Blank Input */
.inline-blank {
    width: 120px !important;
    margin: 4px 6px !important;
    vertical-align: middle !important;
    transition: all 0.2s ease;
}

.inline-blank:focus {
    background-color: #eff6ff !important;
    border-color: #3b82f6 !important;
    outline: 2px solid #3b82f6 !important;
    outline-offset: -1px !important;
}
</style>

<script>
// Force dropdown text visibility - handles Word HTML parent styles
document.addEventListener('DOMContentLoaded', function() {
    const dropdowns = document.querySelectorAll('.dropdown-selection-question .dropdown');

    dropdowns.forEach(function(dropdown) {
        // Force initial style
        dropdown.style.setProperty('color', '#000000', 'important');
        dropdown.style.setProperty('background-color', '#ffffff', 'important');
        dropdown.style.setProperty('-webkit-text-fill-color', '#000000', 'important');

        // On change, ensure color stays visible
        dropdown.addEventListener('change', function() {
            this.style.setProperty('color', '#000000', 'important');
            this.style.setProperty('-webkit-text-fill-color', '#000000', 'important');
            if (this.value) {
                this.style.setProperty('background-color', '#f0fdf4', 'important');
                this.style.setProperty('border-color', '#86efac', 'important');
            } else {
                this.style.setProperty('background-color', '#ffffff', 'important');
                this.style.setProperty('border-color', '#3b82f6', 'important');
            }
        });

        // Trigger on load in case value is pre-selected
        if (dropdown.value) {
            dropdown.dispatchEvent(new Event('change'));
        }
    });
});
</script>
