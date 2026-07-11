{{-- resources/views/components/test-timer.blade.php --}}
{{-- Updated version to integrate in user bar --}}
@props([
    'attempt',
    'autoSubmitFormId' => null,
    'warningTime' => 300,
    'dangerTime' => 60,
    'position' => 'integrated', // integrated, top-right, top-left, etc.
    'customDuration' => null, // Override section time_limit if provided (in minutes)
    'reviewTime' => 0 // Extra review time in minutes (shown separately after main time ends)
])

@php
    // Use custom duration if provided, otherwise fallback to section time_limit
    $baseDuration = $customDuration ?? $attempt->testSet->section->time_limit;
    // Total duration = base + review (for internal calculation)
    $testDuration = $baseDuration + $reviewTime;
    $attemptStartTime = $attempt->start_time;
@endphp

@if($position === 'integrated')
    {{-- This will be integrated directly in the user bar --}}
    <div class="timer-integrated" id="timer-integrated">
        <div class="timer-minimalist" id="universal-timer-display-integrated">
            <svg class="timer-icon" viewBox="0 0 48 48">
                <linearGradient id="ardn4qMWM6qJppYdTWAANa_wrIwUNhk1J4k_gr1" x1="9.858" x2="38.142" y1="9.858" y2="38.142" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#889097"></stop>
                    <stop offset="1" stop-color="#64717c"></stop>
                </linearGradient>
                <circle cx="24" cy="24" r="20" fill="url(#ardn4qMWM6qJppYdTWAANa_wrIwUNhk1J4k_gr1)"></circle>
                <radialGradient id="ardn4qMWM6qJppYdTWAANb_wrIwUNhk1J4k_gr2" cx="24" cy="24" r="18.5" gradientUnits="userSpaceOnUse">
                    <stop offset="0"></stop>
                    <stop offset="1" stop-opacity="0"></stop>
                </radialGradient>
                <circle cx="24" cy="24" r="18.5" fill="url(#ardn4qMWM6qJppYdTWAANb_wrIwUNhk1J4k_gr2)"></circle>
                <radialGradient id="ardn4qMWM6qJppYdTWAANc_wrIwUNhk1J4k_gr3" cx="23.89" cy="7.394" r="37.883" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#fafafb"></stop>
                    <stop offset="1" stop-color="#c8cdd1"></stop>
                </radialGradient>
                <circle cx="24" cy="24" r="17" fill="url(#ardn4qMWM6qJppYdTWAANc_wrIwUNhk1J4k_gr3)"></circle>
                <linearGradient id="ardn4qMWM6qJppYdTWAANd_wrIwUNhk1J4k_gr4" x1="22.479" x2="25.719" y1="9.361" y2="40.548" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#c8cdd1"></stop>
                    <stop offset="1" stop-color="#a6aaad"></stop>
                </linearGradient>
                <path fill="url(#ardn4qMWM6qJppYdTWAANd_wrIwUNhk1J4k_gr4)" d="M25,10c0,0.552-0.448,1-1,1s-1-0.448-1-1c0-0.552,0.448-1,1-1S25,9.448,25,10z M24,37 c-0.552,0-1,0.448-1,1c0,0.552,0.448,1,1,1s1-0.448,1-1C25,37.448,24.552,37,24,37z M38,23c-0.552,0-1,0.448-1,1 c0,0.552,0.448,1,1,1s1-0.448,1-1C39,23.448,38.552,23,38,23z M10,23c-0.552,0-1,0.448-1,1c0,0.552,0.448,1,1,1s1-0.448,1-1 C11,23.448,10.552,23,10,23z"></path>
                <path fill="#d83b01" d="M24,34.75c-0.414,0-0.75-0.336-0.75-0.75V24c0-0.414,0.336-0.75,0.75-0.75s0.75,0.336,0.75,0.75v10 C24.75,34.414,24.414,34.75,24,34.75z"></path>
                <path fill="#45494d" d="M24,24.75c-0.192,0-0.384-0.073-0.53-0.22c-0.293-0.293-0.293-0.768,0-1.061l8.485-8.485 c0.293-0.293,0.768-0.293,1.061,0s0.293,0.768,0,1.061L24.53,24.53C24.384,24.677,24.192,24.75,24,24.75z"></path>
                <path fill="#45494d" d="M23.999,25.25c-0.181,0-0.365-0.039-0.54-0.123l-7.787-3.735c-0.623-0.299-0.885-1.045-0.586-1.668 c0.298-0.622,1.045-0.887,1.667-0.586l7.787,3.735c0.623,0.299,0.885,1.045,0.586,1.668C24.912,24.988,24.465,25.25,23.999,25.25z"></path>
                <circle cx="24" cy="24" r="2" fill="#1e2021"></circle>
            </svg>
            <span class="timer-text-simple" id="universal-timer-text">{{ $baseDuration }} Minutes Left</span>
            <span class="timer-text-hover" id="universal-timer-text-hover">{{ $baseDuration }}:00 Left</span>
        </div>
    </div>
@else
    {{-- Original floating timer --}}
    <div class="universal-timer-container timer-{{ $position }}" id="universal-timer-container">
        <div class="universal-timer-display" id="universal-timer-display">
            <div class="timer-icon">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="timer-text" id="universal-timer-text">{{ $baseDuration }}:00</div>
        </div>
        
        <div class="timer-progress" id="universal-timer-progress">
            <div class="timer-progress-bar" id="universal-timer-progress-bar"></div>
        </div>
    </div>
@endif

<style>
/* Integrated Timer Styles - Minimalist */
.timer-integrated {
    display: flex;
    align-items: center;
}

.timer-minimalist {
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    padding: 4px 0;
    transition: all 0.3s ease;
}

.timer-icon {
    width: 28px;
    height: 28px;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
    transition: all 0.3s ease;
}

.timer-minimalist:hover .timer-icon {
    transform: scale(1.15) rotate(10deg);
    filter: drop-shadow(0 3px 6px rgba(0, 0, 0, 0.4));
}

.timer-text-simple {
    display: block;
    white-space: nowrap;
    text-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
}

.timer-text-hover {
    display: none;
    white-space: nowrap;
    text-shadow: 0 0 4px rgba(0, 0, 0, 0.5);
}

/* Hover effect */
.timer-minimalist:hover .timer-text-simple {
    display: none;
}

.timer-minimalist:hover .timer-text-hover {
    display: block;
}

/* Warning state */
.timer-minimalist.warning {
    color: #fbbf24;
    animation: pulse-warning 2s infinite;
}

.timer-minimalist.warning .timer-icon {
    filter: drop-shadow(0 2px 6px rgba(245, 158, 11, 0.8));
}

/* Danger state */
.timer-minimalist.danger {
    color: #ef4444;
    animation: pulse-danger 1s infinite;
}

.timer-minimalist.danger .timer-icon {
    filter: drop-shadow(0 2px 8px rgba(239, 68, 68, 1));
    animation: shake 0.5s infinite;
}

/* Review mode state - Blue color to indicate review time */
.timer-minimalist.review-mode {
    color: #60a5fa;
}

.timer-minimalist.review-mode .timer-icon {
    filter: drop-shadow(0 2px 6px rgba(96, 165, 250, 0.8));
}

.timer-minimalist.review-mode .timer-text-simple,
.timer-minimalist.review-mode .timer-text-hover {
    background: linear-gradient(135deg, #3b82f6, #60a5fa);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Shake animation for danger */
@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

/* Simple pulse animations */
@keyframes pulse-warning {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

@keyframes pulse-danger {
    0%, 100% { 
        opacity: 1;
        transform: scale(1);
    }
    50% { 
        opacity: 0.8;
        transform: scale(1.05);
    }
}

/* Original floating timer styles */
.universal-timer-container {
    position: fixed;
    z-index: 1000;
    user-select: none;
}

.timer-top-right {
    top: 20px;
    right: 20px;
}

.timer-top-left {
    top: 20px;
    left: 20px;
}

.timer-bottom-right {
    bottom: 20px;
    right: 20px;
}

.timer-bottom-left {
    bottom: 20px;
    left: 20px;
}

.universal-timer-display {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    padding: 12px 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    font-weight: 600;
    font-size: 16px;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
}

.universal-timer-display.warning {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
    animation: pulse-warning 2s infinite;
}

.universal-timer-display.danger {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    animation: pulse-danger 1s infinite;
}

.timer-progress {
    width: 100%;
    height: 4px;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 2px;
    margin-top: 8px;
    overflow: hidden;
}

.timer-progress-bar {
    height: 100%;
    background-color: rgba(255, 255, 255, 0.8);
    border-radius: 2px;
    transition: width 1s linear;
    width: 100%;
}

@keyframes pulse-warning {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes pulse-danger {
    0%, 100% { 
        transform: scale(1); 
        opacity: 1;
    }
    50% { 
        transform: scale(1.08); 
        opacity: 0.8;
    }
}

@media (max-width: 768px) {
    .timer-minimalist {
        font-size: 14px;
        gap: 8px;
    }
    
    .timer-icon {
        width: 24px;
        height: 24px;
    }
    
    /* On mobile, show time format directly */
    .timer-text-simple {
        display: none;
    }
    
    .timer-text-hover {
        display: block;
    }
    
    .universal-timer-container {
        top: 10px !important;
        right: 10px !important;
        left: auto !important;
        bottom: auto !important;
    }
    
    .universal-timer-display {
        padding: 10px 12px;
        font-size: 14px;
        min-width: 100px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.UniversalTimer) {
        console.log('Timer already initialized');
        return;
    }

    const config = {
        attemptStartTime: new Date('{{ $attemptStartTime->toIso8601String() }}'),
        serverCurrentTime: new Date('{{ now()->toIso8601String() }}'),
        testDurationMinutes: {{ $testDuration }},
        baseDurationMinutes: {{ $baseDuration }}, // Main test time (without review)
        reviewTimeMinutes: {{ $reviewTime }}, // Extra review time
        warningTime: {{ $warningTime }},
        dangerTime: {{ $dangerTime }},
        autoSubmitFormId: '{{ $autoSubmitFormId }}',
        attemptId: {{ $attempt->id }},
        position: '{{ $position }}',
        // Session management URLs
        keepAliveUrl: '{{ route("student.test.session.keep-alive") }}',
        checkAuthUrl: '{{ route("student.test.session.check-auth") }}',
        emergencySaveUrl: '{{ route("student.test.session.emergency-save") }}',
        csrfToken: '{{ csrf_token() }}'
    };
    
    // Calculate time offset between server and client
    const clientTime = new Date();
    const serverTime = config.serverCurrentTime;
    const timeOffset = clientTime.getTime() - serverTime.getTime();
    
    window.UniversalTimer = {
        config: config,
        testDurationMs: config.testDurationMinutes * 60 * 1000,
        baseDurationMs: config.baseDurationMinutes * 60 * 1000, // Main test time in ms
        reviewTimeMs: config.reviewTimeMinutes * 60 * 1000, // Review time in ms
        isInReviewMode: false, // Track if we're in review time
        timerInterval: null,
        keepAliveInterval: null, // Keep session alive
        isRunning: false,
        isOnline: navigator.onLine,
        timeOffset: timeOffset,
        lastUpdate: Date.now(),
        submitRetryCount: 0,
        maxSubmitRetries: 3,
        
        // DOM Elements - support both integrated and floating
        get timerDisplay() {
            return config.position === 'integrated' 
                ? document.getElementById('universal-timer-display-integrated')
                : document.getElementById('universal-timer-display');
        },
        
        get timerText() {
            return document.getElementById('universal-timer-text');
        },
        
        get progressBar() {
            return document.getElementById('universal-timer-progress-bar');
        },
        
        start: function() {
            if (this.isRunning) return;

            this.isRunning = true;
            this.lastUpdate = Date.now();
            this.updateTimer();

            // Use both setInterval and visibility change detection
            this.timerInterval = setInterval(() => this.updateTimer(), 1000);

            // Start keep-alive ping every 5 minutes to prevent session timeout
            this.startKeepAlive();

            // Setup network status monitoring
            this.setupNetworkMonitoring();

            // Handle page visibility changes for accurate timing
            document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));

            this.setupNavigationPrevention();

            console.log('Universal Timer Started for attempt:', this.config.attemptId);
            console.log('Time offset from server:', this.timeOffset, 'ms');
        },

        // Keep session alive during long tests (Full Tests can be 2.5+ hours)
        startKeepAlive: function() {
            // Ping server every 5 minutes
            this.keepAliveInterval = setInterval(() => {
                this.pingKeepAlive();
            }, 5 * 60 * 1000); // 5 minutes

            // Also ping immediately on start
            this.pingKeepAlive();
            console.log('Session keep-alive started');
        },

        pingKeepAlive: function() {
            if (!this.isOnline) {
                console.log('Offline - skipping keep-alive ping');
                return;
            }

            fetch(this.config.keepAliveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    if (response.status === 401) {
                        // Session expired - show warning
                        this.showSessionExpiredWarning();
                    }
                    throw new Error('Keep-alive failed');
                }
                return response.json();
            })
            .then(data => {
                console.log('Keep-alive success:', data.timestamp);
            })
            .catch(error => {
                console.error('Keep-alive error:', error);
            });
        },

        showSessionExpiredWarning: function() {
            // Show a non-blocking warning that session might have issues
            this.showToastNotification('Session issue detected. Your work is being saved.', 'warning');
            // Try emergency save
            this.emergencySave();
        },

        setupNetworkMonitoring: function() {
            window.addEventListener('online', () => {
                this.isOnline = true;
                console.log('Network: Online');
                this.showToastNotification('Connection restored', 'success');
                // Ping keep-alive immediately when back online
                this.pingKeepAlive();
            });

            window.addEventListener('offline', () => {
                this.isOnline = false;
                console.log('Network: Offline');
                this.showToastNotification('Connection lost. Your work is saved locally.', 'danger');
                // Save to localStorage when offline
                this.saveCurrentState();
            });
        },

        emergencySave: function() {
            const formData = this.collectFormData();
            if (!formData || Object.keys(formData).length === 0) return;

            // Save to localStorage first (immediate)
            this.saveCurrentState();

            // Then try server save
            if (this.isOnline) {
                fetch(this.config.emergencySaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.config.csrfToken,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        attempt_id: this.config.attemptId,
                        answers: formData,
                        section: this.detectSection()
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Emergency save to server successful');
                    }
                })
                .catch(error => {
                    console.error('Emergency server save failed:', error);
                });
            }
        },

        collectFormData: function() {
            const data = {};
            const forms = document.querySelectorAll('form');

            forms.forEach(form => {
                // Textareas
                form.querySelectorAll('textarea').forEach(textarea => {
                    if (textarea.name) {
                        data[textarea.name] = textarea.value;
                    }
                });

                // Inputs
                form.querySelectorAll('input').forEach(input => {
                    if (input.name && input.type !== 'hidden' && input.type !== 'submit') {
                        if (input.type === 'radio' || input.type === 'checkbox') {
                            if (input.checked) {
                                data[input.name] = input.value;
                            }
                        } else {
                            data[input.name] = input.value;
                        }
                    }
                });

                // Selects
                form.querySelectorAll('select').forEach(select => {
                    if (select.name) {
                        data[select.name] = select.value;
                    }
                });
            });

            return data;
        },

        detectSection: function() {
            const path = window.location.pathname;
            if (path.includes('listening')) return 'listening';
            if (path.includes('reading')) return 'reading';
            if (path.includes('writing')) return 'writing';
            if (path.includes('speaking')) return 'speaking';
            return 'unknown';
        },
        
        handleVisibilityChange: function() {
            if (document.hidden) {
                console.log('Tab became inactive');
            } else {
                console.log('Tab became active, updating timer');
                // Immediately update when tab becomes visible
                this.updateTimer();
            }
        },
        
        stop: function() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
            if (this.keepAliveInterval) {
                clearInterval(this.keepAliveInterval);
                this.keepAliveInterval = null;
            }
            this.isRunning = false;
            document.removeEventListener('visibilitychange', this.handleVisibilityChange);
            this.removeNavigationPrevention();
        },
        
        calculateRemainingTime: function() {
            const currentTime = new Date();
            // Adjust for time offset to sync with server
            const adjustedCurrentTime = currentTime.getTime() - this.timeOffset;
            const elapsedMs = adjustedCurrentTime - this.config.attemptStartTime.getTime();
            const remainingMs = this.testDurationMs - elapsedMs;
            
            // Handle tab switching delays
            const now = Date.now();
            if (now - this.lastUpdate > 2000) {
                // Tab was likely inactive, recalculate
                console.log('Timer recalibrating after tab switch');
            }
            this.lastUpdate = now;
            
            return Math.max(0, Math.floor(remainingMs / 1000));
        },
        
        updateTimer: function() {
            const remainingSeconds = this.calculateRemainingTime();
            
            if (remainingSeconds <= 0) {
                this.handleTimeUp();
                return;
            }
            
            this.updateDisplay(remainingSeconds);
            this.updateProgressBar(remainingSeconds);
            this.updateVisualState(remainingSeconds);
        },
        
        updateDisplay: function(remainingSeconds) {
            // Check if we should switch to review mode
            const baseTimeSeconds = this.baseDurationMs / 1000;
            const reviewTimeSeconds = this.reviewTimeMs / 1000;
            const totalSeconds = this.testDurationMs / 1000;

            // Calculate how much base time has passed
            const elapsedSeconds = totalSeconds - remainingSeconds;
            const inReviewPhase = elapsedSeconds >= baseTimeSeconds && reviewTimeSeconds > 0;

            // Trigger review mode notification once
            if (inReviewPhase && !this.isInReviewMode) {
                this.isInReviewMode = true;
                this.showReviewModeNotification();
            }

            let displayMinutes, displaySeconds, displayText, hoverText;

            if (inReviewPhase) {
                // In review mode - show review time remaining
                const reviewRemaining = remainingSeconds;
                displayMinutes = Math.floor(reviewRemaining / 60);
                displaySeconds = reviewRemaining % 60;

                if (displayMinutes >= 1) {
                    displayText = `Review: ${displayMinutes}m ${displaySeconds}s`;
                } else {
                    displayText = `Review: ${displaySeconds}s`;
                }
                hoverText = `Review Time: ${displayMinutes}:${displaySeconds.toString().padStart(2, '0')}`;
            } else {
                // Normal mode - show main test time
                // Calculate remaining base time (not including review)
                const baseRemaining = Math.max(0, baseTimeSeconds - elapsedSeconds);
                displayMinutes = Math.floor(baseRemaining / 60);
                displaySeconds = Math.floor(baseRemaining % 60);

                if (displayMinutes >= 1) {
                    displayText = `${displayMinutes} Minute${displayMinutes > 1 ? 's' : ''} Left`;
                } else {
                    displayText = `${displaySeconds} Second${displaySeconds !== 1 ? 's' : ''} Left`;
                }
                hoverText = `${displayMinutes}:${displaySeconds.toString().padStart(2, '0')} Left`;
            }

            if (this.timerText) {
                this.timerText.textContent = displayText;
            }

            // Update hover text
            const hoverEl = document.getElementById('universal-timer-text-hover');
            if (hoverEl) {
                hoverEl.textContent = hoverText;
            }

            // Add/remove review class for styling
            const timerWrapper = document.querySelector('.timer-minimalist');
            if (timerWrapper) {
                if (inReviewPhase) {
                    timerWrapper.classList.add('review-mode');
                } else {
                    timerWrapper.classList.remove('review-mode');
                }
            }
        },

        showReviewModeNotification: function() {
            // Show a notification that review time has started
            const notification = document.createElement('div');
            notification.id = 'review-mode-notification';
            notification.innerHTML = `
                <div style="
                    position: fixed;
                    top: 80px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                    color: white;
                    padding: 14px 28px;
                    border-radius: 10px;
                    font-size: 15px;
                    font-weight: 500;
                    z-index: 99999;
                    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
                    animation: slideDownReview 0.4s ease-out;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                ">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span>Review Time Started - Check your answers!</span>
                </div>
                <style>
                    @keyframes slideDownReview {
                        from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                        to { transform: translateX(-50%) translateY(0); opacity: 1; }
                    }
                </style>
            `;
            document.body.appendChild(notification);

            // Auto-hide after 4 seconds
            setTimeout(() => {
                const el = document.getElementById('review-mode-notification');
                if (el) {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 0.3s ease';
                    setTimeout(() => el.remove(), 300);
                }
            }, 4000);
        },
        
        updateProgressBar: function(remainingSeconds) {
            const totalSeconds = this.config.testDurationMinutes * 60;
            const percentage = (remainingSeconds / totalSeconds) * 100;
            if (this.progressBar) {
                this.progressBar.style.width = `${percentage}%`;
            }
        },
        
        // Track which warnings have been shown
        warningsShown: {
            fiveMinute: false,
            oneMinute: false,
            thirtySecond: false
        },

        updateVisualState: function(remainingSeconds) {
            if (!this.timerDisplay) return;

            // Show warning popups at specific times
            this.checkAndShowWarnings(remainingSeconds);

            // For integrated timer, update the minimalist timer
            const minimalistTimer = document.querySelector('.timer-minimalist');
            if (minimalistTimer) {
                minimalistTimer.classList.remove('warning', 'danger');

                if (remainingSeconds <= this.config.dangerTime) {
                    minimalistTimer.classList.add('danger');
                } else if (remainingSeconds <= this.config.warningTime) {
                    minimalistTimer.classList.add('warning');
                }
            }

            // For floating timer, update as before
            this.timerDisplay.classList.remove('warning', 'danger');

            if (remainingSeconds <= this.config.dangerTime) {
                this.timerDisplay.classList.add('danger');
            } else if (remainingSeconds <= this.config.warningTime) {
                this.timerDisplay.classList.add('warning');
            }
        },

        checkAndShowWarnings: function(remainingSeconds) {
            // Only show 1 minute warning as a subtle toast (reduced from 3 warnings to 1)
            // This prevents distraction during the test
            if (remainingSeconds <= 60 && remainingSeconds > 55 && !this.warningsShown.oneMinute) {
                this.warningsShown.oneMinute = true;
                this.showToastNotification('1 Minute Left', 'danger');
            }
        },

        // FIXED: Changed from intrusive modal popup to subtle toast notification
        showToastNotification: function(message, type) {
            // Remove any existing toast
            const existingToast = document.getElementById('timer-toast');
            if (existingToast) {
                existingToast.remove();
            }

            const colors = {
                success: { bg: '#10b981', text: '#ffffff' },
                warning: { bg: '#f59e0b', text: '#ffffff' },
                danger: { bg: '#ef4444', text: '#ffffff' },
                critical: { bg: '#dc2626', text: '#ffffff' }
            };

            const color = colors[type] || colors.warning;

            const toast = document.createElement('div');
            toast.id = 'timer-toast';
            toast.innerHTML = `
                <div style="
                    position: fixed;
                    top: 80px;
                    right: 20px;
                    background: ${color.bg};
                    color: ${color.text};
                    padding: 12px 20px;
                    border-radius: 8px;
                    font-size: 14px;
                    font-weight: 600;
                    z-index: 99999;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    animation: slideInRight 0.3s ease-out;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                ">
                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    ${message}
                </div>
                <style>
                    @keyframes slideInRight {
                        from { transform: translateX(100%); opacity: 0; }
                        to { transform: translateX(0); opacity: 1; }
                    }
                    @keyframes slideOutRight {
                        from { transform: translateX(0); opacity: 1; }
                        to { transform: translateX(100%); opacity: 0; }
                    }
                </style>
            `;

            document.body.appendChild(toast);

            // Auto-close after 3 seconds with slide out animation
            setTimeout(() => {
                const t = document.getElementById('timer-toast');
                if (t) {
                    t.firstElementChild.style.animation = 'slideOutRight 0.3s ease-out forwards';
                    setTimeout(() => t.remove(), 300);
                }
            }, 3000);
        },

        // Keep the old function name for backwards compatibility but redirect to toast
        showWarningPopup: function(title, message, type) {
            this.showToastNotification(title, type);
        },

        playWarningSound: function(type) {
            try {
                // Create a simple beep sound using Web Audio API
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                // Different frequencies for different warning types
                const frequencies = {
                    warning: 440,    // A4
                    danger: 523,     // C5
                    critical: 659    // E5
                };

                oscillator.frequency.value = frequencies[type] || 440;
                oscillator.type = 'sine';

                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.5);

                // For critical, play multiple beeps
                if (type === 'critical') {
                    setTimeout(() => {
                        const osc2 = audioContext.createOscillator();
                        const gain2 = audioContext.createGain();
                        osc2.connect(gain2);
                        gain2.connect(audioContext.destination);
                        osc2.frequency.value = 659;
                        osc2.type = 'sine';
                        gain2.gain.setValueAtTime(0.3, audioContext.currentTime);
                        gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                        osc2.start(audioContext.currentTime);
                        osc2.stop(audioContext.currentTime + 0.3);
                    }, 200);
                }
            } catch (e) {
                // Audio not supported, silently fail
                console.log('Warning sound not supported');
            }
        },
        
        handleTimeUp: function() {
            this.stop();
            console.log('Time is up! Auto-submitting...');

            // Save current state first
            this.saveCurrentState();

            // Show time up modal and auto-submit
            this.showTimeUpModal();
        },

        showTimeUpModal: function() {
            const self = this;
            let autoSubmitCountdown = 3;

            // Remove navigation prevention FIRST before showing modal
            this.removeNavigationPrevention();

            const modal = document.createElement('div');
            modal.id = 'time-up-modal';
            modal.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.7);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div style="
                        background: white;
                        padding: 24px 32px;
                        border-radius: 12px;
                        text-align: center;
                        max-width: 320px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                    ">
                        <div style="
                            width: 48px;
                            height: 48px;
                            margin: 0 auto 12px;
                            background: #fef2f2;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                            <svg width="24" height="24" fill="#dc2626" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 style="color: #1f2937; font-size: 18px; font-weight: 600; margin-bottom: 4px;">Time's Up!</h2>
                        <p id="auto-submit-text" style="color: #6b7280; font-size: 13px; margin-bottom: 16px;">
                            Submitting in <span id="countdown-number" style="font-weight: 600; color: #dc2626;">3</span>s...
                        </p>
                        <div style="height: 3px; background: #e5e7eb; border-radius: 2px; overflow: hidden;">
                            <div id="countdown-bar" style="height: 100%; background: #dc2626; width: 100%; transition: width 1s linear;"></div>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            // Get elements
            const countdownBar = document.getElementById('countdown-bar');
            const countdownNumber = document.getElementById('countdown-number');

            // Auto-submit countdown - reduced to 3 seconds
            const countdownInterval = setInterval(() => {
                autoSubmitCountdown--;
                if (countdownNumber) {
                    countdownNumber.textContent = autoSubmitCountdown;
                }
                if (countdownBar) {
                    countdownBar.style.width = (autoSubmitCountdown / 3 * 100) + '%';
                }

                if (autoSubmitCountdown <= 0) {
                    clearInterval(countdownInterval);
                    self.submitTestForm();
                }
            }, 1000);
        },

        submitTestForm: function() {
            const formId = this.config.autoSubmitFormId;
            const autoSubmitText = document.getElementById('auto-submit-text');
            const self = this;

            // Update status
            if (autoSubmitText) {
                autoSubmitText.innerHTML = '<span style="color: #059669;">Submitting...</span>';
            }

            // CRITICAL: Cleanup all background processes before submit
            if (window.cleanupReadingTest) {
                window.cleanupReadingTest();
            }
            if (window.AnswerManager && window.AnswerManager.cleanup) {
                window.AnswerManager.cleanup();
            }

            // Find the form
            let form = null;

            // Try specified form ID first
            if (formId) {
                form = document.getElementById(formId);
            }

            // Try common form IDs
            if (!form) {
                const commonFormIds = ['listening-form', 'reading-form', 'writing-form', 'speaking-form', 'test-form', 'submit-form'];
                for (const id of commonFormIds) {
                    form = document.getElementById(id);
                    if (form) break;
                }
            }

            // Find any POST form
            if (!form) {
                form = document.querySelector('form[method="post"], form[method="POST"]');
            }

            if (form) {
                // First, try AJAX submission for better reliability
                this.submitViaAjax(form, autoSubmitText);
            } else {
                this.handleSubmitFailure(autoSubmitText);
            }
        },

        // AJAX submission with retry capability
        submitViaAjax: function(form, statusElement) {
            const self = this;
            const formData = new FormData(form);

            // Add auto_submit marker
            formData.append('auto_submit', '1');

            // Get form action URL
            const submitUrl = form.action || window.location.href;

            if (statusElement) {
                statusElement.innerHTML = `<span style="color: #059669;">Submitting (attempt ${this.submitRetryCount + 1})...</span>`;
            }

            fetch(submitUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html, application/json'
                }
            })
            .then(response => {
                // Check if redirected (successful submission)
                if (response.redirected) {
                    console.log('Submit successful, redirecting to:', response.url);
                    window.location.href = response.url;
                    return;
                }

                // Check response status
                if (response.ok) {
                    // Try to get redirect URL from response
                    return response.text().then(html => {
                        // Check if response contains a redirect or success indicator
                        if (html.includes('results') || html.includes('completed') || html.includes('success')) {
                            // Try to extract redirect URL
                            const match = html.match(/window\.location\s*=\s*['"]([^'"]+)['"]/);
                            if (match) {
                                window.location.href = match[1];
                            } else {
                                // Reload to results page
                                window.location.reload();
                            }
                        } else {
                            // Response is OK but no redirect - fallback to form submit
                            console.log('AJAX OK but no redirect, using form submit');
                            self.fallbackToFormSubmit(form);
                        }
                    });
                }

                // If response is 419 (CSRF token mismatch), get new token and retry
                if (response.status === 419) {
                    console.warn('CSRF token expired, refreshing...');
                    throw new Error('CSRF_EXPIRED');
                }

                // If response is 401 (session expired), save data and show error
                if (response.status === 401) {
                    console.error('Session expired during submit');
                    self.emergencySave();
                    throw new Error('SESSION_EXPIRED');
                }

                throw new Error(`HTTP ${response.status}`);
            })
            .catch(error => {
                console.error('AJAX submit error:', error.message);

                if (error.message === 'SESSION_EXPIRED') {
                    if (statusElement) {
                        statusElement.innerHTML = '<span style="color: #dc2626;">Session expired. Data saved. Please login and retry.</span>';
                    }
                    // Don't retry on session expiry
                    return;
                }

                // Retry logic
                if (self.submitRetryCount < self.maxSubmitRetries) {
                    self.submitRetryCount++;
                    console.log(`Retrying submit (${self.submitRetryCount}/${self.maxSubmitRetries})...`);

                    if (statusElement) {
                        statusElement.innerHTML = `<span style="color: #f59e0b;">Retrying (${self.submitRetryCount}/${self.maxSubmitRetries})...</span>`;
                    }

                    // Wait 2 seconds before retry
                    setTimeout(() => {
                        self.submitViaAjax(form, statusElement);
                    }, 2000);
                } else {
                    // Max retries reached, fallback to form submit
                    console.log('Max retries reached, falling back to form submit');
                    self.fallbackToFormSubmit(form);
                }
            });
        },

        // Fallback to traditional form submit
        fallbackToFormSubmit: function(form) {
            console.log('Using fallback form submit');

            // Add hidden input to mark as auto-submit
            let hiddenInput = form.querySelector('input[name="auto_submit"]');
            if (!hiddenInput) {
                hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'auto_submit';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
            }

            // Set timeout for recovery
            const submitTimeout = setTimeout(() => {
                console.error('Form submit timeout - showing retry button');
                this.showSubmitRetryModal();
            }, 30000); // 30 seconds

            window.autoSubmitTimeoutId = submitTimeout;

            // Use native form.submit()
            HTMLFormElement.prototype.submit.call(form);
        },

        // Show modal with retry button if submit seems stuck
        showSubmitRetryModal: function() {
            const existingModal = document.getElementById('submit-retry-modal');
            if (existingModal) existingModal.remove();

            const modal = document.createElement('div');
            modal.id = 'submit-retry-modal';
            modal.innerHTML = `
                <div style="
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(0,0,0,0.8);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                ">
                    <div style="
                        background: white;
                        padding: 24px 32px;
                        border-radius: 12px;
                        text-align: center;
                        max-width: 400px;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
                    ">
                        <div style="
                            width: 48px;
                            height: 48px;
                            margin: 0 auto 12px;
                            background: #fef3c7;
                            border-radius: 50%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        ">
                            <svg width="24" height="24" fill="#f59e0b" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <h2 style="color: #1f2937; font-size: 18px; font-weight: 600; margin-bottom: 8px;">Submission Taking Long</h2>
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 16px;">
                            Your test is being submitted. If this takes too long, click retry.
                            <br><strong>Your answers have been saved.</strong>
                        </p>
                        <div style="display: flex; gap: 12px; justify-content: center;">
                            <button onclick="window.location.reload()" style="
                                background: #3b82f6;
                                color: white;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 6px;
                                font-weight: 500;
                                cursor: pointer;
                            ">Retry Submission</button>
                            <button onclick="document.getElementById('submit-retry-modal').remove()" style="
                                background: #e5e7eb;
                                color: #374151;
                                border: none;
                                padding: 10px 20px;
                                border-radius: 6px;
                                font-weight: 500;
                                cursor: pointer;
                            ">Wait More</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        },

        handleSubmitFailure: function(autoSubmitText) {
            if (autoSubmitText) {
                autoSubmitText.innerHTML = '<span style="color: #dc2626;">Submit failed. Reloading...</span>';
            }

            // Reload page after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        },

        saveCurrentState: function() {
            try {
                const forms = document.querySelectorAll('form');
                if (forms.length > 0) {
                    const formData = new FormData(forms[0]);
                    const data = {};
                    
                    for (let [key, value] of formData.entries()) {
                        data[key] = value;
                    }
                    
                    const textareas = document.querySelectorAll('textarea');
                    textareas.forEach((textarea) => {
                        if (textarea.name) {
                            data[textarea.name] = textarea.value;
                        }
                    });
                    
                    const radios = document.querySelectorAll('input[type="radio"]:checked');
                    radios.forEach((radio) => {
                        data[radio.name] = radio.value;
                    });
                    
                    localStorage.setItem(`testBackup_${this.config.attemptId}`, JSON.stringify({
                        data: data,
                        timestamp: Date.now(),
                        timeUp: true
                    }));
                }
            } catch (error) {
                console.error('Error saving test data:', error);
            }
        },
        
        setupNavigationPrevention: function() {
            // Note: beforeunload and popstate prevention are now handled
            // directly in each test page to show custom submit modal
            // This prevents the default browser alert

            // Keep beforeunload for external navigation (closing tab, typing new URL)
            this.beforeUnloadHandler = (e) => {
                if (this.isRunning) {
                    e.preventDefault();
                    e.returnValue = 'Your test is in progress. Are you sure you want to leave?';
                    return 'Your test is in progress. Are you sure you want to leave?';
                }
            };

            window.addEventListener('beforeunload', this.beforeUnloadHandler);

            // Removed popstate handler - now handled in individual test pages
            // to show submit modal instead of alert
        },
        
        removeNavigationPrevention: function() {
            if (this.beforeUnloadHandler) {
                window.removeEventListener('beforeunload', this.beforeUnloadHandler);
            }
            // popstateHandler removed - now handled in individual test pages
        },
        
        showTimeUpNotification: function() {
            const modal = document.createElement('div');
            modal.innerHTML = `
                <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.8); z-index: 9999; display: flex; align-items: center; justify-content: center;">
                    <div style="background: white; padding: 2rem; border-radius: 1rem; text-align: center; max-width: 400px;">
                        <h2 style="color: #dc2626; font-size: 1.5rem; font-weight: bold; margin-bottom: 1rem;">Time's Up!</h2>
                        <p style="margin-bottom: 1.5rem;">Your test time has expired. Please submit your test.</p>
                        <button onclick="this.parentElement.parentElement.remove()" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.5rem; cursor: pointer;">OK</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        },
        
        getRemainingTime: function() {
            return this.calculateRemainingTime();
        },
        
        getElapsedTime: function() {
            const currentTime = new Date();
            const elapsedMs = currentTime.getTime() - this.config.attemptStartTime.getTime();
            return Math.floor(elapsedMs / 1000);
        },
        
        pause: function() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },
        
        resume: function() {
            if (!this.timerInterval && this.isRunning) {
                this.timerInterval = setInterval(() => this.updateTimer(), 1000);
            }
        }
    };
    
    window.UniversalTimer.start();
});

window.getTimerStatus = function() {
    if (window.UniversalTimer) {
        return {
            remaining: window.UniversalTimer.getRemainingTime(),
            elapsed: window.UniversalTimer.getElapsedTime(),
            isRunning: window.UniversalTimer.isRunning
        };
    }
    return null;
};

window.pauseTimer = function() {
    if (window.UniversalTimer) {
        window.UniversalTimer.pause();
    }
};

window.resumeTimer = function() {
    if (window.UniversalTimer) {
        window.UniversalTimer.resume();
    }
};
</script>