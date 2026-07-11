// Speaking section specific functionality
document.addEventListener('DOMContentLoaded', function () {
    const questionType = document.getElementById('question_type');

    if (questionType) {
        questionType.addEventListener('change', function () {
            const type = this.value;
            const followupQuestions = document.getElementById('followup-questions');
            const cueCardFormat = document.getElementById('cue-card-format');
            const timeLimitInput = document.querySelector('[name="time_limit"]');

            // Reset displays
            if (followupQuestions) followupQuestions.classList.add('hidden');
            if (cueCardFormat) cueCardFormat.classList.add('hidden');

            switch (type) {
                case 'part1_personal':
                    if (timeLimitInput) timeLimitInput.value = 1;
                    break;
                case 'part2_cue_card':
                    if (timeLimitInput) timeLimitInput.value = 2;
                    if (cueCardFormat) cueCardFormat.classList.remove('hidden');
                    break;
                case 'part3_discussion':
                    if (timeLimitInput) timeLimitInput.value = 5;
                    if (followupQuestions) followupQuestions.classList.remove('hidden');
                    break;
            }
        });
    }
});

// Section specific handlers
function handleSectionSpecificChange(type) {
    // Handled in the change event above
}