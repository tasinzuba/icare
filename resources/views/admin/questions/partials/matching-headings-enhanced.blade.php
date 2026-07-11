<!-- Enhanced Matching Headings Manager -->
<div id="matching-headings-card" class="bg-white rounded-lg shadow-sm overflow-hidden" style="display: none;">
    <div class="px-6 py-4 border-b border-gray-200 bg-blue-50">
        <h3 class="text-lg font-medium text-gray-900">
            <svg class="w-5 h-5 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            Create Matching Headings Group
        </h3>
        <p class="text-sm text-gray-600 mt-1">Create all questions in one place</p>
    </div>
    
    <div class="p-6 space-y-6">
        <!-- Settings -->
        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Starting Question Number
                    </label>
                    <input type="number"
                           id="mh_start_number"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                           value="{{ isset($question) ? $question->order_number : 1 }}"
                           min="1"
                           onchange="MatchingHeadingsEnhanced.updateStartNumber()">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Number of Questions
                    </label>
                    <input type="number"
                           id="mh_question_count"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                           value="{{ isset($question) && $question->section_specific_data ? count($question->section_specific_data['mappings'] ?? []) ?: 5 : 5 }}"
                           min="2"
                           max="10"
                           onchange="MatchingHeadingsEnhanced.updateQuestionCount()">
                </div>
            </div>
        </div>

        <!-- Step 1: Headings List -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-blue-600 text-white text-xs rounded-full mr-2">1</span>
                    Headings List <span class="text-sm font-normal text-gray-500">(Add 2-3 extra as distractors)</span>
                </h4>
                <span class="text-sm text-gray-500" id="mh-heading-count">0 headings</span>
            </div>
            <div id="mh-headings-container" class="space-y-2 mb-3 max-h-64 overflow-y-auto">
                <!-- Headings will be added here -->
            </div>
            <button type="button" id="mh-add-heading-btn" onclick="MatchingHeadingsEnhanced.addHeading()" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                + Add Heading
            </button>
        </div>
        
        <!-- Step 2: Questions & Mappings -->
        <div class="border-t pt-6">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">
                    <span class="inline-flex items-center justify-center w-6 h-6 bg-green-600 text-white text-xs rounded-full mr-2">2</span>
                    Questions & Paragraph Mapping
                </h4>
            </div>
            <div id="mh-questions-container" class="space-y-3">
                <!-- Questions will be generated here -->
            </div>
        </div>
        
        <!-- Preview -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h5 class="font-medium text-blue-900 mb-2">📋 Preview</h5>
            <div class="text-sm text-blue-800">
                <p>This will create:</p>
                <ul class="list-disc list-inside mt-1 space-y-1">
                    <li>1 Master Question (Questions <span id="mh-preview-range">1-5</span>)</li>
                    <li><span id="mh-preview-count">5</span> Individual Questions in the test</li>
                    <li><span id="mh-preview-headings">0</span> Heading options</li>
                    <li><span id="mh-preview-distractors">0</span> Distractor headings</li>
                </ul>
            </div>
        </div>
        
        <!-- Hidden inputs for data -->
        <input type="hidden" id="matching_headings_data" name="matching_headings_data" value="">
        
        <!-- Instructions -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <p class="text-sm text-yellow-800">
                <strong>Tips:</strong>
            </p>
            <ul class="list-disc list-inside mt-1 space-y-1 text-sm text-yellow-800">
                <li>Each heading should be concise and clear</li>
                <li>Add 2-3 extra headings that won't be used (distractors)</li>
                <li>Make sure headings are distinct from each other</li>
                <li>Paragraph titles are optional but helpful for organization</li>
            </ul>
        </div>
    </div>
</div>

<script>
// Enhanced Matching Headings Manager
const MatchingHeadingsEnhanced = {
    headings: [],
    questions: [],
    
    init() {
        console.log('MatchingHeadingsEnhanced.init() called');

        // In edit mode, skip defaults — loadExistingMatchingHeadingsData handles data loading
        if (window.isEditMode && window.questionData?.question_type === 'matching_headings') {
            console.log('Edit mode detected — skipping defaults, data will be loaded by loadExistingMatchingHeadingsData');
            return;
        }

        // Clear existing data
        this.headings = [];
        this.questions = [];

        const startNumInput = document.getElementById('mh_start_number');

        // In create mode, use auto-calculated next question number
        const nextQuestionNumber = window.nextQuestionNumber || 1;
        if (startNumInput) {
            startNumInput.value = nextQuestionNumber;
        }

        // Get initial values
        const startNum = parseInt(startNumInput?.value) || 1;
        const count = parseInt(document.getElementById('mh_question_count')?.value) || 5;

        console.log('Starting number:', startNum, 'Count:', count);

        // Clear containers
        const headingsContainer = document.getElementById('mh-headings-container');
        const questionsContainer = document.getElementById('mh-questions-container');

        if (headingsContainer) headingsContainer.innerHTML = '';
        if (questionsContainer) questionsContainer.innerHTML = '';

        // Add default headings (count + 3 distractors)
        for (let i = 0; i < count + 3; i++) {
            this.addHeading();
        }

        // Generate questions
        this.generateQuestions();

        // Update preview
        this.updatePreview();

        // Update main form order number and marks
        const orderInput = document.querySelector('#order-number-wrapper input[name="order_number"]');
        const marksInput = document.querySelector('input[name="marks"]');
        if (orderInput) {
            orderInput.value = startNum;
        }
        if (marksInput) {
            marksInput.value = count;
        }
    },
    
    addHeading(text = '') {
        const container = document.getElementById('mh-headings-container');
        if (!container) return;
        
        const index = this.headings.length;
        const letter = String.fromCharCode(65 + index);
        
        const headingDiv = document.createElement('div');
        headingDiv.className = 'flex items-center gap-2 p-3 bg-white rounded border border-gray-200';
        headingDiv.setAttribute('data-heading-index', index);
        
        headingDiv.innerHTML = `
            <input type="text" 
                   id="mh-heading-${index}"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                   placeholder="Enter heading text..." 
                   value="${text}"
                   onchange="MatchingHeadingsEnhanced.updateHeadingData(${index}, this.value)">
            <button type="button" onclick="MatchingHeadingsEnhanced.removeHeading(${index})" 
                    class="text-red-500 hover:text-red-700 p-1">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        `;
        
        container.appendChild(headingDiv);
        this.headings.push({ id: letter, text: text });
        
        // Update counter
        document.getElementById('mh-heading-count').textContent = `${this.headings.length} headings`;
        
        // Update dropdowns in questions
        this.updateAllDropdowns();
        this.updatePreview();
    },
    
    removeHeading(index) {
        if (this.headings.length <= 2) {
            alert('You must have at least 2 headings.');
            return;
        }
        
        const container = document.getElementById('mh-headings-container');
        const headingDiv = container.querySelector(`[data-heading-index="${index}"]`);
        if (headingDiv) {
            headingDiv.remove();
            this.reindexHeadings();
        }
    },
    
    reindexHeadings() {
        const container = document.getElementById('mh-headings-container');
        const headingDivs = container.querySelectorAll('div[data-heading-index]');
        
        this.headings = [];
        
        headingDivs.forEach((div, index) => {
            const letter = String.fromCharCode(65 + index);
            div.setAttribute('data-heading-index', index);
            // Remove letter display
            
            const input = div.querySelector('input');
            input.id = `mh-heading-${index}`;
            input.setAttribute('onchange', `MatchingHeadingsEnhanced.updateHeadingData(${index}, this.value)`);
            
            const btn = div.querySelector('button');
            btn.setAttribute('onclick', `MatchingHeadingsEnhanced.removeHeading(${index})`);
            
            this.headings.push({ id: letter, text: input.value });
        });
        
        // Update counter and dropdowns
        document.getElementById('mh-heading-count').textContent = `${this.headings.length} headings`;
        this.updateAllDropdowns();
        this.updatePreview();
    },
    
    generateQuestions() {
        const container = document.getElementById('mh-questions-container');
        if (!container) {
            console.error('Questions container not found!');
            return;
        }
        
        console.log('Generating questions...');
        container.innerHTML = '';
        this.questions = [];
        
        const startNum = parseInt(document.getElementById('mh_start_number').value) || 1;
        const count = parseInt(document.getElementById('mh_question_count').value) || 5;
        
        for (let i = 0; i < count; i++) {
            const questionNum = startNum + i;
            const paragraphLetter = String.fromCharCode(65 + i);
            
            const questionDiv = document.createElement('div');
            questionDiv.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
            
            questionDiv.innerHTML = `
                <div class="flex items-center justify-between mb-2">
                    <h5 class="font-medium text-gray-900">Question ${questionNum}</h5>
                    <span class="text-sm text-gray-500">Paragraph ${paragraphLetter}</span>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Paragraph Title (Optional)
                        </label>
                        <input type="text" 
                               id="mh-para-title-${i}"
                               class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                               placeholder="e.g., Introduction to urban planning"
                               onchange="MatchingHeadingsEnhanced.updateQuestionData(${i})">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Correct Heading
                        </label>
                        <select id="mh-correct-${i}"
                                class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                                onchange="MatchingHeadingsEnhanced.updateQuestionData(${i})">
                            <option value="">Select correct heading</option>
                        </select>
                    </div>
                </div>
            `;
            
            container.appendChild(questionDiv);
            
            this.questions.push({
                number: questionNum,
                paragraph: paragraphLetter,
                title: '',
                correct: ''
            });
        }
        
        console.log('Questions container after generation:', container.innerHTML);
        console.log('Questions generated:', this.questions.length, 'questions');
        
        // Populate dropdowns
        this.updateAllDropdowns();
        this.updateData();
    },
    
    updateStartNumber() {
        const startNum = parseInt(document.getElementById('mh_start_number').value) || 1;

        // Update main form order number
        const orderInput = document.querySelector('#order-number-wrapper input[name="order_number"]');
        if (orderInput) {
            orderInput.value = startNum;
        }
        
        // Regenerate questions with new numbers
        this.generateQuestions();
        this.updatePreview();
    },
    
    updateQuestionCount() {
        const count = parseInt(document.getElementById('mh_question_count').value) || 5;
        
        // Regenerate questions
        this.generateQuestions();
        
        // Add more headings if needed
        const neededHeadings = count + 3; // questions + 3 distractors
        while (this.headings.length < neededHeadings) {
            this.addHeading();
        }
        
        // Update marks in main form
        const marksInput = document.querySelector('input[name="marks"]');
        if (marksInput) {
            marksInput.value = count;
        }
        
        this.updatePreview();
    },
    
    updateHeadingData(index, value) {
        if (this.headings[index]) {
            this.headings[index].text = value;
            this.updateAllDropdowns();
            this.updateData();
        }
    },
    
    updateQuestionData(index) {
        console.log('updateQuestionData called for index:', index);
        
        const titleInput = document.getElementById(`mh-para-title-${index}`);
        const correctSelect = document.getElementById(`mh-correct-${index}`);
        
        console.log('Title input:', titleInput?.value);
        console.log('Correct select:', correctSelect?.value);
        
        if (this.questions[index]) {
            this.questions[index].title = titleInput?.value || '';
            this.questions[index].correct = correctSelect?.value || '';
            console.log('Updated question:', this.questions[index]);
            this.updateData();
        } else {
            console.error('Question not found at index:', index);
        }
    },
    
    updateAllDropdowns() {
        this.questions.forEach((_, index) => {
            const select = document.getElementById(`mh-correct-${index}`);
            if (select) {
                const currentValue = select.value;
                select.innerHTML = '<option value="">Select correct heading</option>';
                
                this.headings.forEach(heading => {
                    const option = document.createElement('option');
                    option.value = heading.id;
                    option.textContent = heading.text || 'Heading ' + heading.id;
                    select.appendChild(option);
                });
                
                select.value = currentValue;
            }
        });
    },
    
    updatePreview() {
        const startNum = parseInt(document.getElementById('mh_start_number').value) || 1;
        const count = parseInt(document.getElementById('mh_question_count').value) || 5;
        const endNum = startNum + count - 1;
        
        document.getElementById('mh-preview-range').textContent = `${startNum}-${endNum}`;
        document.getElementById('mh-preview-count').textContent = count;
        document.getElementById('mh-preview-headings').textContent = this.headings.length;
        
        const usedHeadings = this.questions.filter(q => q.correct).length;
        const distractors = Math.max(0, this.headings.length - usedHeadings);
        document.getElementById('mh-preview-distractors').textContent = distractors;
        
        this.updateData();
    },
    
    updateData() {
        // Prepare data for submission
        const validHeadings = this.headings.filter(h => h.text && h.text.trim());
        const validMappings = this.questions.filter(q => q.correct);
        
        const data = {
            headings: validHeadings,
            mappings: validMappings.map(q => ({
                question: q.number,
                paragraph: q.paragraph,
                title: q.title,
                correct: q.correct
            }))
        };
        
        console.log('Updating data:', data);
        
        // Update hidden input
        document.getElementById('matching_headings_data').value = JSON.stringify(data);
        
        // Update main content field with auto-generated content
        const startNum = parseInt(document.getElementById('mh_start_number').value) || 1;
        const count = parseInt(document.getElementById('mh_question_count').value) || 5;
        const endNum = startNum + count - 1;
        
        const content = `Questions ${startNum}-${endNum}\n\nChoose the correct heading for each paragraph from the list of headings below.`;
        
        if (window.contentEditor) {
            window.contentEditor.setContent(content);
        } else {
            const contentField = document.getElementById('content');
            if (contentField) {
                contentField.value = content;
            }
        }
        
        console.log('Updated matching headings data:', data);
    }
};

// Make it globally available first
window.MatchingHeadingsEnhanced = MatchingHeadingsEnhanced;

// Auto-initialize when question type changes
document.addEventListener('DOMContentLoaded', function() {
    console.log('Matching Headings Enhanced ready');
    
    const questionType = document.getElementById('question_type');
    if (questionType && questionType.value === 'matching_headings') {
        console.log('Matching headings already selected, initializing in 500ms...');
        setTimeout(() => {
            if (window.MatchingHeadingsEnhanced) {
                window.MatchingHeadingsEnhanced.init();
            }
        }, 500);
    }
});
</script>