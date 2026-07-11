// Listening section specific functionality
document.addEventListener('DOMContentLoaded', function () {
    // Initialize TinyMCE for listening questions
    initializeTinyMCE('.tinymce');

    // Check if audio is required
    const mediaInput = document.getElementById('media');
    if (mediaInput) {
        mediaInput.setAttribute('required', 'required');
    }
});

// Section specific handlers
function handleSectionSpecificChange(type) {
    // Listening-specific logic based on question type
    const transcriptField = document.querySelector('[name="audio_transcript"]');

    // Show/hide transcript based on type
    if (type === 'form_completion' || type === 'note_completion') {
        if (transcriptField) {
            transcriptField.closest('div').style.display = 'block';
        }
    }
}