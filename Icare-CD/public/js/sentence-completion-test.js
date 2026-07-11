// Enhanced Sentence Completion Display Handler
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeSentenceCompletion();
    });
    
    function initializeSentenceCompletion() {
        console.log('Initializing sentence completion display...');
        
        // Find all sentence completion questions
        const sentenceQuestions = document.querySelectorAll('[data-question-type="sentence_completion"]');
        
        sentenceQuestions.forEach(question => {
            enhanceSentenceCompletionDisplay(question);
        });
    }
    
    function enhanceSentenceCompletionDisplay(questionElement) {
        const questionId = questionElement.getAttribute('data-question-id');
        const sectionData = getSectionSpecificData(questionId);
        
        if (!sectionData || !sectionData.sentence_completion) {
            console.log('No sentence completion data found for question', questionId);
            return;
        }
        
        const scData = sectionData.sentence_completion;
        
        // Create enhanced display
        const enhancedDisplay = createEnhancedDisplay(scData, questionId);
        
        // Replace the existing content
        const contentArea = questionElement.querySelector('.question-content');
        if (contentArea) {
            contentArea.innerHTML = '';
            contentArea.appendChild(enhancedDisplay);
        }
    }
    
    function createEnhancedDisplay(scData, questionId) {
        const container = document.createElement('div');
        container.className = 'sentence-completion-enhanced';
        
        // Add instructions
        const instructions = document.createElement('div');
        instructions.className = 'sc-instructions';
        instructions.innerHTML = `
            <p><strong>Complete the sentences below.</strong></p>
            <p>Choose <strong>NO MORE THAN ONE WORD</strong> from the list for each answer.</p>
        `;
        container.appendChild(instructions);
        
        // Add options box (like matching headings)
        if (scData.options && scData.options.length > 0) {
            const optionsBox = document.createElement('div');
            optionsBox.className = 'sc-options-box';
            optionsBox.innerHTML = '<div class="sc-options-title">Word List:</div>';
            
            const optionsList = document.createElement('div');
            optionsList.className = 'sc-options-list';
            
            scData.options.forEach(option => {
                const optionItem = document.createElement('div');
                optionItem.className = 'sc-option-item';
                optionItem.innerHTML = `<strong>${option.id}.</strong> ${option.text}`;
                optionsList.appendChild(optionItem);
            });
            
            optionsBox.appendChild(optionsList);
            container.appendChild(optionsBox);
        }
        
        // Add sentences
        const sentencesContainer = document.createElement('div');
        sentencesContainer.className = 'sc-sentences';
        
        scData.sentences.forEach((sentence, index) => {
            const sentenceElement = createSentenceElement(sentence, questionId, scData.options);
            sentencesContainer.appendChild(sentenceElement);
        });
        
        container.appendChild(sentencesContainer);
        
        return container;
    }
    
    function createSentenceElement(sentence, questionId, options) {
        const sentenceDiv = document.createElement('div');
        sentenceDiv.className = 'sc-sentence-item';
        
        // Process the sentence text to replace [GAP] with dropdown
        let processedText = sentence.text;
        const dropdownHtml = createDropdownHtml(sentence.questionNumber, questionId, options);
        
        processedText = processedText.replace(/\[GAP\]/g, dropdownHtml);
        
        sentenceDiv.innerHTML = `
            <span class="sc-question-number">${sentence.questionNumber}.</span>
            <span class="sc-sentence-text">${processedText}</span>
        `;
        
        return sentenceDiv;
    }
    
    function createDropdownHtml(questionNumber, questionId, options) {
        let html = `<select name="answers[${questionId}][q${questionNumber}]" 
                           class="sc-dropdown" 
                           data-question-number="${questionNumber}">
                        <option value="">Choose</option>`;
        
        if (options && options.length > 0) {
            options.forEach(option => {
                html += `<option value="${option.id}">${option.id}</option>`;
            });
        }
        
        html += '</select>';
        
        return html;
    }
    
    function getSectionSpecificData(questionId) {
        // This would be populated from the server
        // For now, we'll look for it in a data attribute
        const questionElement = document.querySelector(`[data-question-id="${questionId}"]`);
        if (questionElement) {
            const dataAttr = questionElement.getAttribute('data-section-specific');
            if (dataAttr) {
                try {
                    return JSON.parse(dataAttr);
                } catch (e) {
                    console.error('Error parsing section specific data:', e);
                }
            }
        }
        return null;
    }
    
    // Add CSS styles
    const style = document.createElement('style');
    style.textContent = `
        .sentence-completion-enhanced {
            margin: 20px 0;
        }
        
        .sc-instructions {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #2563eb;
            border-radius: 4px;
        }
        
        .sc-instructions p {
            margin: 5px 0;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .sc-options-box {
            margin-bottom: 25px;
            padding: 20px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .sc-options-title {
            font-weight: 700;
            margin-bottom: 15px;
            font-size: 16px;
            color: #1f2937;
        }
        
        .sc-options-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
        }
        
        .sc-option-item {
            padding: 8px 12px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .sc-option-item strong {
            color: #2563eb;
            margin-right: 5px;
        }
        
        .sc-sentences {
            margin-top: 20px;
        }
        
        .sc-sentence-item {
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            line-height: 1.8;
        }
        
        .sc-question-number {
            font-weight: 700;
            min-width: 30px;
            color: #1f2937;
            font-size: 15px;
        }
        
        .sc-sentence-text {
            flex: 1;
            font-size: 15px;
            color: #374151;
        }
        
        .sc-dropdown {
            display: inline-block;
            margin: 0 6px;
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            min-width: 80px;
            background-color: #fef3c7;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .sc-dropdown:hover {
            border-color: #f59e0b;
            background-color: #fde68a;
        }
        
        .sc-dropdown:focus {
            outline: none;
            border-color: #f59e0b;
            box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        }
        
        .sc-dropdown[data-answered="true"] {
            background-color: #d1fae5;
            border-color: #10b981;
        }
    `;
    document.head.appendChild(style);
    
    // Update question status when dropdown is changed
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('sc-dropdown')) {
            const questionNumber = e.target.getAttribute('data-question-number');
            if (e.target.value) {
                e.target.setAttribute('data-answered', 'true');
                updateQuestionButton(questionNumber, 'answered');
            } else {
                e.target.removeAttribute('data-answered');
                updateQuestionButton(questionNumber, 'unanswered');
            }
        }
    });
    
    function updateQuestionButton(questionNumber, status) {
        const button = document.querySelector(`.number-btn[data-display-number="${questionNumber}"]`);
        if (button) {
            if (status === 'answered') {
                button.classList.add('answered');
            } else {
                button.classList.remove('answered');
            }
        }
    }
    
    window.SentenceCompletionTest = {
        initialize: initializeSentenceCompletion
    };
})();
