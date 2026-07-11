// Matching Headings Enhanced Fix
document.addEventListener('DOMContentLoaded', function() {
    console.log('Matching Headings Enhanced Fix loaded');
    
    // Watch for question type changes
    const questionType = document.getElementById('question_type');
    if (questionType) {
        questionType.addEventListener('change', function() {
            if (this.value === 'matching_headings') {
                console.log('Matching headings selected, fixing initialization...');
                
                // Force show the card
                const card = document.getElementById('matching-headings-card');
                if (card) {
                    card.style.display = 'block';
                }
                
                // Force initialize after a small delay
                setTimeout(() => {
                    if (window.MatchingHeadingsEnhanced) {
                        console.log('Force initializing MatchingHeadingsEnhanced...');
                        window.MatchingHeadingsEnhanced.init();
                    } else {
                        console.error('MatchingHeadingsEnhanced not found!');
                    }
                }, 200);
            }
        });
    }
});

// Debug helper
window.debugMatchingHeadings = function() {
    console.log('=== Matching Headings Debug ===');
    console.log('Card visible:', document.getElementById('matching-headings-card')?.style.display);
    console.log('Headings container:', document.getElementById('mh-headings-container'));
    console.log('Questions container:', document.getElementById('mh-questions-container'));
    console.log('Manager exists:', !!window.MatchingHeadingsEnhanced);
    if (window.MatchingHeadingsEnhanced) {
        console.log('Headings:', window.MatchingHeadingsEnhanced.headings);
        console.log('Questions:', window.MatchingHeadingsEnhanced.questions);
    }
};
