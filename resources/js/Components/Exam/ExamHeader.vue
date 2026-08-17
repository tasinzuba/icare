<script setup>
import { ref, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ExamTimer from './ExamTimer.vue';

const props = defineProps({
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true },
    attemptStartTime: { type: String, required: true },
    showTimer: { type: Boolean, default: true },
    isReviewPhase: { type: Boolean, default: false },
    reviewTimeSeconds: { type: Number, default: 0 },
    // Offered only where the page has wired --exam-zoom to its content, so the control is never
    // shown somewhere pressing it would do nothing.
    showTextSize: { type: Boolean, default: false },
});

const emit = defineEmits(['timeUp']);

const page = usePage();
const user = computed(() => page.props.auth.user);
const isHelpModalOpen = ref(false);

/* ------------------------------------------------------------------ text size */

/**
 * Reading text size.
 *
 * Applied as a zoom factor on a CSS custom property rather than a font-size, because the exam
 * stylesheets pin dozens of sizes in px — several with !important — and inputs and dropdowns carry
 * their own. A container font-size would leave most of the page unchanged; zoom scales the whole
 * block, answer boxes included.
 *
 * Kept in localStorage so a student sets it once rather than on every part and every test.
 *
 * Each step is its own button showing an A drawn at that step's size, so the choice reads at a
 * glance without a percentage to interpret — the small A is the small text.
 */
const ZOOM_STEPS = [
    { zoom: 0.9, label: 12 },
    { zoom: 1, label: 13 },
    { zoom: 1.15, label: 15 },
    { zoom: 1.3, label: 17 },
];
const STORAGE_KEY = 'exam-text-zoom';

const zoomIndex = ref(1);

const applyZoom = () => {
    document.documentElement.style.setProperty('--exam-zoom', String(ZOOM_STEPS[zoomIndex.value].zoom));
};

const setZoom = (index) => {
    zoomIndex.value = Math.min(ZOOM_STEPS.length - 1, Math.max(0, index));
    applyZoom();
    try {
        localStorage.setItem(STORAGE_KEY, String(zoomIndex.value));
    } catch (e) {
        // Private browsing can refuse storage; the size still applies for this sitting.
    }
};

onMounted(() => {
    let saved = null;
    try {
        saved = localStorage.getItem(STORAGE_KEY);
    } catch (e) {
        saved = null;
    }
    const parsed = parseInt(saved, 10);
    zoomIndex.value = Number.isFinite(parsed) && ZOOM_STEPS[parsed] !== undefined ? parsed : 1;
    applyZoom();
});

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => {
            console.error(`Error attempting to enable fullscreen: ${err.message}`);
        });
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
    }
};
</script>

<template>
    <div class="user-bar">
        <div class="user-info">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" style="width: 20px; height: 20px; margin-right: 8px;">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
            </svg>
            <span>{{ user?.name || 'Student' }} - BI {{ String(user?.id || 1).padStart(6, '0') }}</span>
        </div>
        
        <div class="timer-center-wrapper">
            <ExamTimer v-if="showTimer"
                :timeLimitSeconds="timeLimitSeconds"
                :serverTime="serverTime"
                :attemptStartTime="attemptStartTime"
                :isReviewPhase="isReviewPhase"
                :reviewTimeSeconds="reviewTimeSeconds"
                @timeUp="emit('timeUp')"
            />
        </div>
        
        <div class="user-controls">
            <slot name="extra-controls"></slot>

            <div v-if="showTextSize" class="text-size-control" title="Text size">
                <button v-for="(step, i) in ZOOM_STEPS" :key="step.zoom" type="button"
                        class="text-size-btn" :class="{ active: zoomIndex === i }"
                        :style="{ fontSize: step.label + 'px' }"
                        :aria-label="'Text size ' + Math.round(step.zoom * 100) + '%'"
                        :aria-pressed="zoomIndex === i"
                        @click="setZoom(i)">A</button>
            </div>

            <button class="help-button text-sm" @click="isHelpModalOpen = true">Help ?</button>
            <button class="no-nav text-sm" @click="toggleFullscreen">Full Screen</button>
        </div>

        <!-- Help Modal -->
        <div v-if="isHelpModalOpen" class="fixed inset-0 z-[99999] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="isHelpModalOpen = false"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-8 text-center animate-modal-in">
                <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                
                <h3 class="text-xl font-bold text-slate-900 mb-2">Need Help?</h3>
                <p class="text-slate-600 text-sm leading-relaxed mb-6">
                    If you encounter any issues during the test, please contact our support team at:<br>
                    Email: <span class="font-bold text-blue-600">support@cdielts.org</span>
                </p>

                <button 
                    @click="isHelpModalOpen = false"
                    class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-black transition-all active:scale-[0.98]"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.user-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 24px;
    background-color: #1a1a1a;
    color: white;
    border-bottom: 1px solid #333;
    position: relative;
    height: 50px;
    flex-shrink: 0;
}

.user-info {
    display: flex;
    align-items: center;
    font-size: 14px;
    flex: 1;
}

.timer-center-wrapper {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    z-index: 10;
}

.user-controls {
    flex: 1;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.help-button, .no-nav {
    background: #e5e7eb;
    color: #1f2937;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 14px;
    border: none;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.help-button:hover, .no-nav:hover {
    background: #d1d5db;
}

@keyframes modal-in {
    from { opacity: 0; transform: scale(0.95) translateY(10px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.animate-modal-in {
    animation: modal-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

/* Text size control. Sits on the dark bar beside Help and Full Screen.
   Each button draws its A at its own step's size, so the row itself is the scale. */
.text-size-control {
    display: flex;
    align-items: baseline;
    gap: 1px;
    padding: 2px 3px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 6px;
}

.text-size-btn {
    min-width: 24px;
    padding: 2px 5px;
    border-radius: 4px;
    color: rgba(255, 255, 255, 0.7);
    font-weight: 700;
    line-height: 1.1;
    background: transparent;
    transition: background-color 0.15s ease, color 0.15s ease;
}

.text-size-btn:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.15);
}

.text-size-btn.active {
    color: #1a1a1a;
    background: #fff;
}
</style>
