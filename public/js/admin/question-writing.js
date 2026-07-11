// Writing section specific functionality
document.addEventListener('DOMContentLoaded', function () {
    // Initialize TinyMCE for writing questions
    initializeTinyMCE('.tinymce');

    // Setup question type specific handlers
    const questionType = document.getElementById('question_type');
    if (questionType) {
        questionType.addEventListener('change', handleWritingQuestionTypeChange);
        // Trigger on load if value exists
        if (questionType.value) {
            handleWritingQuestionTypeChange.call(questionType);
        }
    }
});

// Handle Writing Question Type Changes
function handleWritingQuestionTypeChange() {
    const type = this.value;
    const mediaSection = document.getElementById('task1-media');
    const wordLimitInput = document.querySelector('[name="word_limit"]');
    const timeLimitInput = document.querySelector('[name="time_limit"]');

    const partNumberInput = document.getElementById('part_number');

    // Show/hide media upload for Task 1
    if (type && type.startsWith('task1_')) {
        // Task 1 settings
        if (partNumberInput) partNumberInput.value = 1;
        if (mediaSection) {
            mediaSection.classList.remove('hidden');
        }
        if (wordLimitInput) {
            wordLimitInput.value = 150;
        }
        if (timeLimitInput) {
            timeLimitInput.value = 20;
        }

        // Make media required for Task 1
        const mediaInput = document.getElementById('media');
        if (mediaInput) {
            mediaInput.setAttribute('required', 'required');
        }
    } else if (type && type.startsWith('task2_')) {
        // Task 2 settings
        if (partNumberInput) partNumberInput.value = 2;
        if (mediaSection) {
            mediaSection.classList.add('hidden');
        }
        if (wordLimitInput) {
            wordLimitInput.value = 250;
        }
        if (timeLimitInput) {
            timeLimitInput.value = 40;
        }

        // Remove media requirement for Task 2
        const mediaInput = document.getElementById('media');
        if (mediaInput) {
            mediaInput.removeAttribute('required');
        }
    }

    // Update instructions based on type
    updateWritingInstructions(type);
}

// Update instructions based on question type
function updateWritingInstructions(type) {
    const instructionsField = document.getElementById('content');
    if (!instructionsField || instructionsField.value) return; // Don't override if already has content

    const templates = {
        'task1_line_graph': 'The graph below shows [topic] between [time period].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.',
        'task1_bar_chart': 'The chart below shows [topic] in [location/time].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.',
        'task1_pie_chart': 'The pie chart below shows [topic] in [year/location].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.',
        'task1_table': 'The table below shows [topic] from [time period].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.',
        'task1_process': 'The diagram below shows [process/how something works].\n\nSummarise the information by selecting and reporting the main features.',
        'task1_map': 'The maps below show [location] in [two different time periods].\n\nSummarise the information by selecting and reporting the main features, and make comparisons where relevant.',
        'task2_opinion': 'Some people believe that [viewpoint]. To what extent do you agree or disagree?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.',
        'task2_discussion': '[Statement about two opposing views].\n\nDiscuss both these views and give your own opinion.\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.',
        'task2_problem_solution': '[Description of a problem].\n\nWhat are the causes of this problem? What solutions can you suggest?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.',
        'task2_advantage_disadvantage': '[Statement about a trend or development].\n\nDo the advantages of this outweigh the disadvantages?\n\nGive reasons for your answer and include any relevant examples from your own knowledge or experience.'
    };

    if (templates[type]) {
        instructionsField.value = templates[type];
    }
}

// Section specific handlers (called from question-common.js)
function handleSectionSpecificChange(type) {
    // This is called from common handler, just trigger our handler
    const questionTypeSelect = document.getElementById('question_type');
    if (questionTypeSelect) {
        handleWritingQuestionTypeChange.call(questionTypeSelect);
    }
}

// Sample answer structure helper
function insertSampleStructure() {
    const type = document.getElementById('question_type').value;
    const instructionsField = document.querySelector('[name="instructions"]');

    if (!instructionsField) return;

    const structures = {
        'task1_line_graph': '• Introduction: Paraphrase the question\n• Overview: 2-3 main trends\n• Body 1: First time period/highest values\n• Body 2: Second time period/comparisons',
        'task1_process': '• Introduction: Paraphrase the question\n• Overview: Number of stages and start/end points\n• Body 1: First half of the process\n• Body 2: Second half of the process',
        'task2_opinion': '• Introduction: Paraphrase question + thesis statement\n• Body 1: First reason with example\n• Body 2: Second reason with example\n• Body 3: Counter-argument (optional)\n• Conclusion: Restate opinion',
        'task2_discussion': '• Introduction: Paraphrase the question\n• Body 1: First viewpoint with reasons\n• Body 2: Second viewpoint with reasons\n• Body 3: Your opinion\n• Conclusion: Summary of both views + your opinion'
    };

    if (structures[type]) {
        instructionsField.value = structures[type];
    }
}

// Add sample structure button if needed
document.addEventListener('DOMContentLoaded', function () {
    const instructionsField = document.querySelector('[name="instructions"]');
    if (instructionsField) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'text-sm text-blue-600 hover:text-blue-700 mb-2';
        button.textContent = 'Add Sample Structure';
        button.onclick = insertSampleStructure;

        instructionsField.parentElement.insertBefore(button, instructionsField);
    }
});