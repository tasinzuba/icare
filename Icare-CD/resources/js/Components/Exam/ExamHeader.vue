<script setup>
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import ExamTimer from './ExamTimer.vue';

const props = defineProps({
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true },
    attemptStartTime: { type: String, required: true },
    showTimer: { type: Boolean, default: true },
    isReviewPhase: { type: Boolean, default: false },
    reviewTimeSeconds: { type: Number, default: 0 },
});

const emit = defineEmits(['timeUp']);

const page = usePage();
const user = computed(() => page.props.auth.user);
const isHelpModalOpen = ref(false);

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
</style>
