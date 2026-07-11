<x-student-layout>
    <x-slot:title>AI Evaluation in Progress</x-slot>
    
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="text-center max-w-lg">
            {{-- AI Animation --}}
            <div class="relative mb-8">
                <div class="w-32 h-32 mx-auto">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-600 to-blue-600 rounded-full animate-pulse"></div>
                    <div class="absolute inset-2 bg-white rounded-full flex items-center justify-center">
                        <i class="fas fa-robot text-4xl text-purple-600 animate-bounce"></i>
                    </div>
                </div>
            </div>

            {{-- Status Text --}}
            <h2 class="text-2xl font-bold text-gray-900 mb-4">
                AI is Evaluating Your {{ ucfirst($type) }}...
            </h2>
            
            <p id="status-message" class="text-gray-600 mb-8">
                This may take 30-60 seconds. Please don't close this window.
            </p>

            {{-- Progress Bar --}}
            <div class="w-full bg-gray-200 rounded-full h-3 mb-8 overflow-hidden">
                <div id="progress-bar" 
                     class="bg-gradient-to-r from-purple-600 to-blue-600 h-full rounded-full transition-all duration-500 ease-out"
                     style="width: 0%">
                </div>
            </div>

            {{-- Progress Steps --}}
            <div class="max-w-md mx-auto space-y-4 text-left">
                <div class="flex items-center space-x-3" data-step="1" data-min-progress="0">
                    <div class="step-icon w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                        <i class="fas fa-check text-sm hidden"></i>
                        <div class="spinner hidden"></div>
                    </div>
                    <span class="text-gray-700">Analyzing your response...</span>
                </div>
                
                <div class="flex items-center space-x-3" data-step="2" data-min-progress="25">
                    <div class="step-icon w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                        <i class="fas fa-check text-sm hidden"></i>
                        <div class="spinner hidden"></div>
                    </div>
                    <span class="text-gray-700">Evaluating against IELTS criteria...</span>
                </div>
                
                <div class="flex items-center space-x-3" data-step="3" data-min-progress="50">
                    <div class="step-icon w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                        <i class="fas fa-check text-sm hidden"></i>
                        <div class="spinner hidden"></div>
                    </div>
                    <span class="text-gray-700">Generating detailed feedback...</span>
                </div>
                
                <div class="flex items-center space-x-3" data-step="4" data-min-progress="80">
                    <div class="step-icon w-8 h-8 rounded-full bg-gray-300 text-white flex items-center justify-center">
                        <i class="fas fa-check text-sm hidden"></i>
                        <div class="spinner hidden"></div>
                    </div>
                    <span class="text-gray-700">Calculating band score...</span>
                </div>
            </div>

            {{-- Error Message --}}
            <div id="error-message" class="mt-8 p-4 bg-red-50 rounded-lg hidden">
                <p class="text-red-800">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span id="error-text"></span>
                </p>
                <div class="mt-4 space-x-2">
                    <button onclick="retryEvaluation()" class="bg-red-600 text-white px-6 py-2 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-redo mr-2"></i>Try Again
                    </button>
                    <a href="{{ route('student.results.show', $attempt->id) }}" 
                       class="inline-block bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition">
                        Back to Results
                    </a>
                </div>
            </div>

            {{-- Success Message (initially hidden) --}}
            <div id="success-message" class="mt-8 p-6 bg-green-50 rounded-lg hidden">
                <i class="fas fa-check-circle text-green-500 text-5xl mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Evaluation Complete!</h3>
                <p class="text-gray-600 mb-4">Your AI evaluation has been completed successfully.</p>
                <div id="band-score-display" class="hidden mb-4">
                    <p class="text-lg">Your Band Score:</p>
                    <p class="text-3xl font-bold text-purple-600" id="band-score-value">-</p>
                </div>
                <p class="text-sm text-gray-500 mb-4">Redirecting to your detailed results...</p>
            </div>

            {{-- Manual Navigation --}}
            <div id="manual-navigation" class="mt-8 hidden">
                <p class="text-sm text-gray-500 mb-4">Taking longer than expected?</p>
                <a href="{{ route('ai.evaluation.get', $attempt->id) }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-lg inline-block hover:bg-blue-700 transition">
                    <i class="fas fa-arrow-right mr-2"></i>
                    View Results Now
                </a>
            </div>

            {{-- Tip --}}
            <div class="mt-12 p-4 bg-blue-50 rounded-lg max-w-md mx-auto">
                <p class="text-sm text-blue-800">
                    <i class="fas fa-lightbulb mr-2"></i>
                    <span class="font-semibold">Did you know?</span> 
                    Our AI evaluator analyzes your response using the same criteria as real IELTS examiners.
                </p>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
    .spinner {
        width: 16px;
        height: 16px;
        border: 2px solid #f3f4f6;
        border-top: 2px solid #9333ea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .step-active {
        animation: stepPulse 1.5s ease-in-out infinite;
    }

    @keyframes stepPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    </style>
    @endpush

    @push('scripts')
    <script>
    const attemptId = {{ $attempt->id }};
    const evaluationType = '{{ $type }}';
    let checkInterval;
    let timeoutTimer;
    let checkCount = 0;
    let currentProgress = 0;
    let retryCount = 0;
    const maxRetries = 3;

    function updateProgress(progress, message) {
        // Smooth progress animation
        currentProgress = Math.max(currentProgress, progress);
        document.getElementById('progress-bar').style.width = currentProgress + '%';
        
        // Update message
        if (message) {
            document.getElementById('status-message').textContent = message;
        }
        
        // Update steps
        document.querySelectorAll('[data-step]').forEach(step => {
            const minProgress = parseInt(step.dataset.minProgress);
            const stepNum = parseInt(step.dataset.step);
            const icon = step.querySelector('.step-icon');
            const checkIcon = icon.querySelector('.fa-check');
            const spinner = icon.querySelector('.spinner');
            
            if (currentProgress > minProgress) {
                // Remove previous active states
                document.querySelectorAll('.step-active').forEach(el => el.classList.remove('step-active'));
                
                icon.classList.remove('bg-gray-300');
                icon.classList.add('bg-purple-600');
                
                if (currentProgress >= minProgress + 25 || stepNum < 4 && currentProgress > parseInt(document.querySelector(`[data-step="${stepNum + 1}"]`)?.dataset.minProgress || 100)) {
                    // Step completed
                    checkIcon.classList.remove('hidden');
                    spinner.classList.add('hidden');
                    icon.classList.remove('step-active');
                } else {
                    // Step in progress
                    checkIcon.classList.add('hidden');
                    spinner.classList.remove('hidden');
                    icon.classList.add('step-active');
                }
            }
        });
    }

    function checkStatus() {
        checkCount++;
        console.log(`Status check #${checkCount} for attempt ${attemptId}`);
        
        fetch(`/ai/evaluation/status/${attemptId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Status update:', data);
                
                if (data.status === 'completed') {
                    handleSuccess(data);
                } else if (data.status === 'failed') {
                    handleError(data.error || 'Evaluation failed. Please try again.');
                } else if (data.status === 'processing') {
                    updateProgress(data.progress || currentProgress + 5, data.message);
                    
                    // Show manual navigation after 30 seconds
                    if (checkCount > 15) {
                        document.getElementById('manual-navigation').classList.remove('hidden');
                    }
                }
            })
            .catch(error => {
                console.error('Status check error:', error);
                retryCount++;
                
                if (retryCount >= maxRetries) {
                    handleError('Connection error. Please check your internet connection.');
                }
            });
    }

    function handleSuccess(data) {
        clearInterval(checkInterval);
        clearTimeout(timeoutTimer);
        
        // Update to 100% and show success
        updateProgress(100, 'Evaluation completed!');
        
        // Hide progress section
        document.getElementById('error-message').classList.add('hidden');
        
        // Show success message
        const successDiv = document.getElementById('success-message');
        successDiv.classList.remove('hidden');
        
        // Show band score if available
        if (data.band_score) {
            document.getElementById('band-score-display').classList.remove('hidden');
            document.getElementById('band-score-value').textContent = data.band_score;
        }
        
        // Redirect after showing success
        setTimeout(() => {
            console.log('Redirecting to:', data.redirect_url);
            if (data.redirect_url) {
                window.location.replace(data.redirect_url);
            } else {
                window.location.href = `/ai/evaluation/${attemptId}`;
            }
        }, 2000);
    }

    function handleError(message) {
        clearInterval(checkInterval);
        clearTimeout(timeoutTimer);
        
        document.getElementById('error-text').textContent = message;
        document.getElementById('error-message').classList.remove('hidden');
        document.getElementById('status-message').textContent = 'Evaluation encountered an error';
        
        // Update progress bar to red
        const progressBar = document.getElementById('progress-bar');
        progressBar.classList.remove('from-purple-600', 'to-blue-600');
        progressBar.classList.add('from-red-500', 'to-red-600');
    }

    function retryEvaluation() {
        // Reset state
        retryCount = 0;
        checkCount = 0;
        currentProgress = 0;
        
        // Hide error message
        document.getElementById('error-message').classList.add('hidden');
        
        // Reset progress bar
        const progressBar = document.getElementById('progress-bar');
        progressBar.classList.remove('from-red-500', 'to-red-600');
        progressBar.classList.add('from-purple-600', 'to-blue-600');
        progressBar.style.width = '0%';
        
        // Reset steps
        document.querySelectorAll('.step-icon').forEach(icon => {
            icon.classList.remove('bg-purple-600', 'step-active');
            icon.classList.add('bg-gray-300');
            icon.querySelector('.fa-check').classList.add('hidden');
            icon.querySelector('.spinner').classList.add('hidden');
        });
        
        // Start again
        updateProgress(10, 'Retrying evaluation...');
        startChecking();
        
        // Re-trigger evaluation
        fetch(`/ai/evaluate/${evaluationType}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                attempt_id: attemptId
            })
        }).catch(error => {
            console.error('Retry failed:', error);
            handleError('Failed to restart evaluation. Please go back and try again.');
        });
    }

    function startChecking() {
        // Initial progress
        updateProgress(10, 'Starting evaluation...');
        
        // Start regular checks
        checkInterval = setInterval(checkStatus, 2000);
        
        // Set timeout (1 minute)
        timeoutTimer = setTimeout(() => {
            clearInterval(checkInterval);
            console.log('Timeout reached');
            
            // Make one final check
            fetch(`/ai/evaluation/status/${attemptId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'completed') {
                        handleSuccess(data);
                    } else {
                        // Show manual navigation
                        document.getElementById('manual-navigation').classList.remove('hidden');
                        updateProgress(currentProgress, 'Taking longer than expected. You can check results manually.');
                    }
                })
                .catch(() => {
                    document.getElementById('manual-navigation').classList.remove('hidden');
                });
        }, 60000);
    }

    // Start when page loads
    window.addEventListener('DOMContentLoaded', function() {
        console.log('Status page loaded for attempt:', attemptId);
        
        // Check immediately
        checkStatus();
        
        // Start regular checking
        startChecking();
    });

    // Handle page visibility changes
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && checkInterval) {
            // Page became visible again, do immediate check
            checkStatus();
        }
    });

    // Prevent accidental navigation
    window.addEventListener('beforeunload', function(e) {
        if (currentProgress > 0 && currentProgress < 100) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
    </script>
    @endpush
</x-student-layout>