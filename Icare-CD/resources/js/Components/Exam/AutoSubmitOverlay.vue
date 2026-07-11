<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    attemptId: { type: [Number, String], required: true },
    answers: { type: Object, required: true },
    storageKey: { type: String, required: true },
    section: { type: String, default: 'reading' }
});

const isSubmitting = ref(false);
const countdown = ref(5);
const error = ref(null);
const progress = ref(0);

const submitTest = () => {
    isSubmitting.value = true;
    error.value = null;

    // Animate progress bar
    let p = 0;
    const progressTimer = setInterval(() => {
        p += 2;
        progress.value = Math.min(p, 90);
        if (p >= 90) clearInterval(progressTimer);
    }, 60);

    const payload = {};
    if (Object.keys(props.answers).length > 0) {
        Object.assign(payload, props.answers);
    } else {
        payload["__empty"] = true;
    }

    localStorage.removeItem(props.storageKey);

    router.post(`/student/test/${props.section}/submit/${props.attemptId}`, {
        answers: payload,
        auto_submit: 1
    }, {
        onError: (err) => {
            isSubmitting.value = false;
            clearInterval(progressTimer);
            error.value = 'Auto-submission failed. Please try to submit manually.';
            console.error('Auto-submit error:', err);
        }
    });
};

onMounted(() => {
    const timer = setInterval(() => {
        if (countdown.value > 1) {
            countdown.value--;
        } else {
            clearInterval(timer);
            submitTest();
        }
    }, 1000);
});

const retrySubmit = () => {
    submitTest();
};
</script>

<template>
    <div class="auto-submit-overlay">
        <!-- Background -->
        <div class="overlay-bg"></div>

        <!-- Content -->
        <div class="overlay-content">

            <!-- Normal State -->
            <div v-if="!error" class="state-normal">
                <!-- Circular countdown -->
                <div class="countdown-ring">
                    <svg viewBox="0 0 100 100">
                        <circle class="ring-bg" cx="50" cy="50" r="42" />
                        <circle class="ring-progress" cx="50" cy="50" r="42"
                            :style="{ strokeDashoffset: isSubmitting ? 0 : (264 - (264 * (5 - countdown) / 5)) }" />
                    </svg>
                    <div class="countdown-number">
                        <template v-if="!isSubmitting">{{ countdown }}</template>
                        <svg v-else class="check-icon" width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>

                <!-- Text -->
                <div class="overlay-text">
                    <h2>{{ isSubmitting ? 'Submitting Your Answers' : "Time's Up!" }}</h2>
                    <p>{{ isSubmitting ? 'Please wait while we save your test...' : 'Your test will be submitted automatically' }}</p>
                </div>

                <!-- Status pill -->
                <div class="status-pill">
                    <span class="pulse-dot">
                        <span class="pulse-ring"></span>
                        <span class="pulse-core"></span>
                    </span>
                    {{ isSubmitting ? 'Saving Answers...' : 'Preparing Submission' }}
                </div>
            </div>

            <!-- Error State -->
            <div v-else class="state-error">
                <div class="error-icon-wrap">
                    <svg width="28" height="28" fill="none" stroke="#ef4444" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                </div>
                <h2>Submission Failed</h2>
                <p>{{ error }}</p>
                <button @click="retrySubmit" class="retry-btn">
                    Retry Submission
                </button>
            </div>
        </div>

        <!-- Bottom progress bar -->
        <div class="bottom-bar">
            <div class="bottom-bar-fill" :style="{ width: progress + '%' }"></div>
        </div>
    </div>
</template>

<style scoped>
.auto-submit-overlay {
    position: fixed;
    inset: 0;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.overlay-bg {
    position: absolute;
    inset: 0;
    background: #0f172a;
    animation: bgFadeIn 0.5s ease-out;
}

.overlay-content {
    position: relative;
    z-index: 1;
    text-align: center;
    animation: contentSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.state-normal {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 32px;
}

/* Countdown ring */
.countdown-ring {
    position: relative;
    width: 100px;
    height: 100px;
}

.countdown-ring > svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.ring-bg {
    fill: none;
    stroke: rgba(255, 255, 255, 0.08);
    stroke-width: 3;
}

.ring-progress {
    fill: none;
    stroke: #3b82f6;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-dasharray: 264;
    transition: stroke-dashoffset 1s linear;
}

.countdown-number {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 700;
    color: #fff;
    font-variant-numeric: tabular-nums;
}

.check-icon {
    color: #22c55e;
    animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

/* Text */
.overlay-text {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.overlay-text h2 {
    font-size: 28px;
    font-weight: 700;
    color: #fff;
    margin: 0;
    letter-spacing: -0.02em;
}

.overlay-text p {
    font-size: 15px;
    color: #94a3b8;
    margin: 0;
    font-weight: 500;
}

/* Status pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 20px;
    border-radius: 100px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    color: #94a3b8;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.pulse-dot {
    position: relative;
    width: 8px;
    height: 8px;
    display: flex;
}

.pulse-ring {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: #3b82f6;
    opacity: 0.4;
    animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
}

.pulse-core {
    position: relative;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #3b82f6;
}

/* Error state */
.state-error {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    animation: scaleIn 0.3s ease-out;
}

.error-icon-wrap {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
}

.state-error h2 {
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    margin: 0;
}

.state-error p {
    font-size: 14px;
    color: #94a3b8;
    margin: 0;
    max-width: 300px;
    line-height: 1.6;
}

.retry-btn {
    margin-top: 8px;
    padding: 12px 32px;
    background: #3b82f6;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.retry-btn:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.retry-btn:active {
    transform: scale(0.98);
}

/* Bottom progress bar */
.bottom-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: rgba(255, 255, 255, 0.05);
}

.bottom-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    border-radius: 0 2px 2px 0;
    transition: width 0.3s ease-out;
}

/* Animations */
@keyframes bgFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes contentSlideUp {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes scaleIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes ping {
    75%, 100% {
        transform: scale(2.5);
        opacity: 0;
    }
}
</style>
