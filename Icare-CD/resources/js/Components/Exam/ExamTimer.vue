<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true },
    attemptStartTime: { type: String, required: true },
    isReviewPhase: { type: Boolean, default: false },
    reviewTimeSeconds: { type: Number, default: 0 }
});

const emit = defineEmits(['timeUp']);

const attemptStart = new Date(props.attemptStartTime);
const serverCurrentTime = new Date(props.serverTime);
const clientTime = new Date();
const timeOffset = clientTime.getTime() - serverCurrentTime.getTime();

// Bonus time added when review starts and remaining < reviewTimeSeconds
const bonusMs = ref(0);

const calculateRemainingTime = () => {
    const currentTime = new Date();
    const adjustedCurrentTime = currentTime.getTime() - timeOffset;
    const elapsedMs = adjustedCurrentTime - attemptStart.getTime();
    const totalMs = (props.timeLimitSeconds * 1000) + bonusMs.value;
    const remainingMs = totalMs - elapsedMs;
    return Math.max(0, Math.floor(remainingMs / 1000));
};

// When review phase starts, reset timer to exactly reviewTimeSeconds
watch(() => props.isReviewPhase, (isReview) => {
    if (isReview && props.reviewTimeSeconds > 0) {
        // Calculate raw elapsed (not clamped) to set exact review time
        const now = new Date();
        const adjustedNow = now.getTime() - timeOffset;
        const elapsedMs = adjustedNow - attemptStart.getTime();
        // bonusMs = what we need to add so that (totalMs - elapsedMs) = reviewTimeSeconds * 1000
        bonusMs.value = (props.reviewTimeSeconds * 1000) + elapsedMs - (props.timeLimitSeconds * 1000);

        // Update timeLeft immediately
        timeLeft.value = calculateRemainingTime();

        // Restart timer interval if it was already cleared (main timer expired before audio ended)
        if (!timerInterval) {
            timerInterval = setInterval(() => {
                timeLeft.value = calculateRemainingTime();
                if (timeLeft.value <= 0) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    emit('timeUp');
                }
            }, 1000);
        }
    }
});

const timeLeft = ref(calculateRemainingTime());

const formattedTime = computed(() => {
    const mins = Math.floor(timeLeft.value / 60);
    const secs = timeLeft.value % 60;
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
});

const timeLeftMinutesText = computed(() => {
    const mins = Math.floor(timeLeft.value / 60);
    const secs = timeLeft.value % 60;
    const prefix = props.isReviewPhase ? '⏱ Review: ' : '';
    if (mins >= 1) {
        return `${prefix}${mins} Minute${mins > 1 ? 's' : ''} Left`;
    }
    return `${prefix}${secs} Second${secs !== 1 ? 's' : ''} Left`;
});

const isWarningTime = computed(() => timeLeft.value <= 300 && timeLeft.value > 60); // 5 mins Warning
const isDangerTime = computed(() => timeLeft.value <= 60); // 1 min Danger

let timerInterval = null;
onMounted(() => {
    timerInterval = setInterval(() => {
        timeLeft.value = calculateRemainingTime();
        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            timerInterval = null;
            emit('timeUp');
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
});
</script>

<template>
    <div class="timer-integrated" id="timer-integrated">
        <div class="timer-minimalist" id="universal-timer-display-integrated" :class="{ 'warning': isWarningTime && !isReviewPhase, 'danger': isDangerTime && !isReviewPhase, 'review': isReviewPhase }">
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
            <span class="timer-text-simple">{{ timeLeftMinutesText }}</span>
            <span class="timer-text-hover">{{ formattedTime }} Left</span>
        </div>
    </div>
</template>

<style scoped>
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
    cursor: default;
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

/* Review phase state */
.timer-minimalist.review {
    color: #60a5fa;
    animation: pulse-review 2s infinite;
}

.timer-minimalist.review .timer-icon {
    filter: drop-shadow(0 2px 6px rgba(96, 165, 250, 0.8));
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    75% { transform: translateX(2px); }
}

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

@keyframes pulse-review {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.75; }
}
</style>
