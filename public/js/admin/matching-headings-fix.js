// Matching Headings Fix for Admin Create Page
// This file ensures Matching Headings functionality works properly

(function() {
    'use strict';
    
    console.log('Matching Headings Fix loaded');
    
    // Function to check and initialize matching headings
    function checkAndInitMatchingHeadings() {
        const questionType = document.getElementById('question_type');
        const matchingHeadingsCard = document.getElementById('matching-headings-card');
        
        if (!questionType || !matchingHeadingsCard) {
            console.log('Required elements not found yet');
            return false;
        }
        
        if (questionType.value === 'matching_headings') {
            console.log('Matching headings is selected, forcing initialization...');
            
            // Show the card
            matchingHeadingsCard.style.display = 'block';
            
            // Force initialize if manager exists
            if (window.MatchingHeadingsManager) {
                // Reset to prevent duplicates
                window.MatchingHeadingsManager.headingCount = 0;
                window.MatchingHeadingsManager.questionCount = 0;
                window.MatchingHeadingsManager.headings = [];
                window.MatchingHeadingsManager.mappings = [];
                
                // Clear containers
                const headingsContainer = document.getElementById('matching-headings-container');
                const mappingsContainer = document.getElementById('question-mappings-container');
                if (headingsContainer) headingsContainer.innerHTML = '';
                if (mappingsContainer) mappingsContainer.innerHTML = '';
                
                // Initialize
                window.MatchingHeadingsManager.init();
                console.log('MatchingHeadingsManager force initialized');
                return true;
            } else {
                console.error('MatchingHeadingsManager not available yet');
                return false;
            }
        }
        
        return true;
    }
    
    // Try multiple times with delays
    let attempts = 0;
    const maxAttempts = 10;
    
    function tryInit() {
        attempts++;
        console.log(`Initialization attempt ${attempts}/${maxAttempts}`);
        
        if (checkAndInitMatchingHeadings() || attempts >= maxAttempts) {
            if (attempts >= maxAttempts) {
                console.error('Failed to initialize after maximum attempts');
            }
            return;
        }
        
        // Try again after delay
        setTimeout(tryInit, 500);
    }
    
    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryInit);
    } else {
        // DOM already loaded
        setTimeout(tryInit, 100);
    }
    
    // Also add a manual trigger for debugging
    window.forceInitMatchingHeadings = function() {
        console.log('Manual initialization triggered');
        checkAndInitMatchingHeadings();
    };
    
})();
