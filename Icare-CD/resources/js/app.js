import './bootstrap';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

createInertiaApp({
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        return pages[`./Pages/${name}.vue`]
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
    },
    progress: {
        delay: 250,
        color: '#29d',
        includeCSS: true,
        showSpinner: false,
    },
});

// Global timer functionality
const timer = {
    init(minutes, elementId, autoSubmitFormId = null) {
        if (!document.getElementById(elementId)) return;

        const timerElement = document.getElementById(elementId);
        let totalSeconds = minutes * 60;

        const interval = setInterval(() => {
            const mins = Math.floor(totalSeconds / 60);
            const secs = totalSeconds % 60;

            timerElement.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            if (totalSeconds <= 0) {
                clearInterval(interval);

                // Auto-submit form if specified
                if (autoSubmitFormId && document.getElementById(autoSubmitFormId)) {
                    document.getElementById(autoSubmitFormId).submit();
                }
            }

            totalSeconds--;
        }, 1000);
    }
};

window.timer = timer;

// Listening test specific functionality
const listeningTest = {
    init(audioElement) {
        const audio = document.getElementById(audioElement);
        if (!audio) return;

        // Ensure audio can only be played once
        audio.addEventListener('ended', () => {
            audio.setAttribute('disabled', 'disabled');
            document.querySelectorAll('.questions-container').forEach(el => {
                el.classList.remove('opacity-50', 'pointer-events-none');
            });
        });

        // Start the test when audio starts playing
        audio.addEventListener('play', () => {
            // Disable questions until audio ends
            document.querySelectorAll('.questions-container').forEach(el => {
                el.classList.add('opacity-50', 'pointer-events-none');
            });
        });
    }
};

window.listeningTest = listeningTest;

// Writing test autosave functionality
const writingTest = {
    init(attemptId, questionId, autoSaveUrl) {
        const editor = document.getElementById(`writing-editor-${questionId}`);
        if (!editor) return;

        let typingTimer;
        const doneTypingInterval = 2000; // 2 seconds

        editor.addEventListener('input', () => {
            clearTimeout(typingTimer);

            typingTimer = setTimeout(() => {
                this.autoSave(attemptId, questionId, editor.value, autoSaveUrl);
            }, doneTypingInterval);
        });
    },

    autoSave(attemptId, questionId, content, url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                content: content
            })
        })
            .then(response => response.json())
            .then(data => {
                // Show autosave indicator
                const indicator = document.getElementById('autosave-indicator');
                if (indicator) {
                    indicator.textContent = 'Saved';
                    indicator.classList.remove('text-red-500');
                    indicator.classList.add('text-green-500');

                    setTimeout(() => {
                        indicator.textContent = '';
                    }, 2000);
                }
            })
            .catch(error => {
                // Show error indicator
                const indicator = document.getElementById('autosave-indicator');
                if (indicator) {
                    indicator.textContent = 'Error saving';
                    indicator.classList.remove('text-green-500');
                    indicator.classList.add('text-red-500');
                }
            });
    }
};

window.writingTest = writingTest;

// Speaking test recording functionality
const speakingTest = {
    init(questionId, uploadUrl) {
        const recordButton = document.getElementById(`record-button-${questionId}`);
        const stopButton = document.getElementById(`stop-button-${questionId}`);
        const audioPlayer = document.getElementById(`audio-player-${questionId}`);

        if (!recordButton || !stopButton || !audioPlayer) return;

        let mediaRecorder;
        let chunks = [];

        recordButton.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);

                mediaRecorder.ondataavailable = (e) => {
                    chunks.push(e.data);
                };

                mediaRecorder.onstop = () => {
                    const blob = new Blob(chunks, { type: 'audio/webm' });
                    chunks = [];

                    const audioURL = URL.createObjectURL(blob);
                    audioPlayer.src = audioURL;

                    // Upload the recorded audio
                    this.uploadRecording(blob, uploadUrl);
                };

                mediaRecorder.start();
                recordButton.classList.add('hidden');
                stopButton.classList.remove('hidden');
            } catch (error) {
                console.error('Error accessing microphone:', error);
                alert('Could not access your microphone. Please check your permissions.');
            }
        });

        stopButton.addEventListener('click', () => {
            if (mediaRecorder && mediaRecorder.state !== 'inactive') {
                mediaRecorder.stop();
                recordButton.classList.remove('hidden');
                stopButton.classList.add('hidden');
            }
        });
    },

    uploadRecording(blob, url) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const formData = new FormData();
        formData.append('recording', blob, 'recording.webm');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                // Show success indicator
                const indicator = document.getElementById('upload-indicator');
                if (indicator) {
                    indicator.textContent = 'Recording saved';
                    indicator.classList.remove('text-red-500');
                    indicator.classList.add('text-green-500');

                    setTimeout(() => {
                        indicator.textContent = '';
                    }, 2000);
                }
            })
            .catch(error => {
                // Show error indicator
                const indicator = document.getElementById('upload-indicator');
                if (indicator) {
                    indicator.textContent = 'Error saving recording';
                    indicator.classList.remove('text-green-500');
                    indicator.classList.add('text-red-500');
                }
            });
    }
};

window.speakingTest = speakingTest;