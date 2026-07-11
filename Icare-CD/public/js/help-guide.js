// Help Guide JavaScript
const HelpGuide = {
    config: {
        testType: 'reading',
        language: 'en'
    },

    init: function (config) {
        console.log('HelpGuide: Initializing with config:', config);
        this.config = Object.assign(this.config, config);

        // Check if modal exists
        const modal = document.getElementById('help-modal');
        if (!modal) {
            console.error('HelpGuide: Modal element not found!');
            return;
        }

        // Setup event listeners
        this.setupEventListeners();

        // Load default content
        this.loadContent('overview');

        console.log('HelpGuide: Initialization complete');
    },

    setupEventListeners: function () {
        // Tab switching
        const tabs = document.querySelectorAll('.help-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                const section = tab.dataset.section;
                this.switchTab(section);
            });
        });

        // Close button
        const closeBtn = document.querySelector('.help-close-btn');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.close());
        }

        // Click outside to close
        const modal = document.getElementById('help-modal');
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                this.close();
            }
        });

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display !== 'none') {
                this.close();
            }
        });
    },

    open: function () {
        console.log('HelpGuide: Opening modal');
        const modal = document.getElementById('help-modal');
        if (modal) {
            modal.style.display = 'flex';
            // Load overview by default
            this.loadContent('overview');
        } else {
            console.error('HelpGuide: Cannot open - modal not found');
        }
    },

    close: function () {
        console.log('HelpGuide: Closing modal');
        const modal = document.getElementById('help-modal');
        if (modal) {
            modal.style.display = 'none';
        }
    },

    switchTab: function (section) {
        // Update active tab
        document.querySelectorAll('.help-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.section === section) {
                tab.classList.add('active');
            }
        });

        // Load content
        this.loadContent(section);
    },

    loadContent: function (section) {
        const contentArea = document.getElementById('help-content');
        if (!contentArea) return;

        const content = this.getContent(section);
        contentArea.innerHTML = content;
    },

    getContent: function (section) {
        const contents = {
            overview: `
                <div class="help-section">
                    <h3>IELTS Reading Test Overview</h3>
                    <p>Welcome to the IELTS Computer-Delivered Reading Test. This test consists of:</p>
                    <ul>
                        <li><strong>Duration:</strong> 60 minutes</li>
                        <li><strong>Questions:</strong> 40 questions in total</li>
                        <li><strong>Sections:</strong> 3 reading passages</li>
                        <li><strong>Difficulty:</strong> Passages increase in difficulty</li>
                    </ul>
                    
                    <h4>Test Interface</h4>
                    <p>The screen is divided into two sections:</p>
                    <ul>
                        <li><strong>Left side:</strong> Reading passage</li>
                        <li><strong>Right side:</strong> Questions</li>
                    </ul>
                    
                    <h4>Important Features</h4>
                    <ul>
                        <li>You can highlight text in the passage using different colors</li>
                        <li>Timer shows remaining time at the top of the screen</li>
                        <li>Navigation buttons at the bottom let you jump between questions</li>
                        <li>Review checkbox allows you to flag questions for later review</li>
                    </ul>
                </div>
            `,

            questions: `
                <div class="help-section">
                    <h3>Question Types</h3>
                    
                    <h4>1. Multiple Choice</h4>
                    <p>Select the correct answer from options A, B, C, or D.</p>
                    
                    <h4>2. True/False/Not Given</h4>
                    <p>Decide if statements agree with the information in the passage.</p>
                    <ul>
                        <li><strong>TRUE:</strong> Statement agrees with the passage</li>
                        <li><strong>FALSE:</strong> Statement contradicts the passage</li>
                        <li><strong>NOT GIVEN:</strong> No information about this in the passage</li>
                    </ul>
                    
                    <h4>3. Fill in the Blanks</h4>
                    <p>Type words from the passage to complete sentences. Pay attention to:</p>
                    <ul>
                        <li>Word limits (e.g., NO MORE THAN TWO WORDS)</li>
                        <li>Spelling must be correct</li>
                        <li>Use exact words from the passage</li>
                    </ul>
                    
                    <h4>4. Matching Headings</h4>
                    <p>Match paragraph headings to the correct paragraphs in the passage.</p>
                    
                    <h4>5. Short Answer Questions</h4>
                    <p>Answer questions using words from the passage. Check word limits!</p>
                </div>
            `,

            navigation: `
                <div class="help-section">
                    <h3>Navigation Guide</h3>
                    
                    <h4>Moving Between Questions</h4>
                    <ul>
                        <li>Click question numbers at the bottom to jump to any question</li>
                        <li>Use Part buttons to switch between different sections</li>
                        <li>Questions you've answered appear in green</li>
                        <li>Current question is highlighted in blue</li>
                        <li>Flagged questions have a yellow indicator</li>
                    </ul>
                    
                    <h4>Text Highlighting</h4>
                    <p>To highlight important text in the passage:</p>
                    <ol>
                        <li>Select text with your mouse</li>
                        <li>Choose a highlight color (yellow, green, or blue)</li>
                        <li>Click on highlighted text to remove the highlight</li>
                    </ol>
                    
                    <h4>Review Feature</h4>
                    <p>Use the Review checkbox to:</p>
                    <ul>
                        <li>Flag questions you want to revisit</li>
                        <li>Flagged questions show a yellow dot</li>
                        <li>Easily identify questions needing review</li>
                    </ul>
                    
                    <h4>Timer</h4>
                    <ul>
                        <li>Shows remaining time in the top bar</li>
                        <li>Changes to yellow when 10 minutes remain</li>
                        <li>Changes to red when 5 minutes remain</li>
                        <li>Test auto-submits when time expires</li>
                    </ul>
                </div>
            `,

            tips: `
                <div class="help-section">
                    <h3>Tips & Strategies</h3>
                    
                    <h4>Time Management</h4>
                    <ul>
                        <li>Spend about 20 minutes per passage</li>
                        <li>Don't spend too long on difficult questions</li>
                        <li>Flag difficult questions and return later</li>
                        <li>Leave 5 minutes to check your answers</li>
                    </ul>
                    
                    <h4>Reading Strategies</h4>
                    <ul>
                        <li><strong>Skim first:</strong> Read the passage quickly to understand the main idea</li>
                        <li><strong>Read questions:</strong> Know what you're looking for before detailed reading</li>
                        <li><strong>Scan for keywords:</strong> Look for specific information mentioned in questions</li>
                        <li><strong>Use highlighting:</strong> Mark important information for easy reference</li>
                    </ul>
                    
                    <h4>Answering Tips</h4>
                    <ul>
                        <li>Read instructions carefully - note word limits</li>
                        <li>Use exact words from the passage when required</li>
                        <li>Check spelling - incorrect spelling means wrong answer</li>
                        <li>Answer all questions - there's no penalty for guessing</li>
                        <li>For True/False/Not Given: be careful about assumptions</li>
                    </ul>
                    
                    <h4>Common Mistakes to Avoid</h4>
                    <ul>
                        <li>Don't use your own knowledge - base answers only on the passage</li>
                        <li>Don't exceed word limits in short answer questions</li>
                        <li>Don't leave any questions blank</li>
                        <li>Don't panic if a passage seems difficult - stay calm</li>
                    </ul>
                </div>
            `
        };

        return contents[section] || '<p>Content not available</p>';
    },

    showVideo: function () {
        alert('Video tutorial coming soon!');
    }
};

// Make HelpGuide globally available
window.HelpGuide = HelpGuide;

console.log('HelpGuide loaded and available globally');