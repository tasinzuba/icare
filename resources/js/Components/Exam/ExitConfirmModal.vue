<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    dashboardUrl: { type: String, default: '/student/dashboard' },
});

const show = ref(false);

function open() { show.value = true; }
function close() { show.value = false; }

function exitTest() {
    window.onbeforeunload = null;
    window.location.href = props.dashboardUrl;
}

// Listen for back-button-trapped from AntiCheat
function onBackTrapped() { open(); }

onMounted(() => {
    window.addEventListener('back-button-trapped', onBackTrapped);
});

onUnmounted(() => {
    window.removeEventListener('back-button-trapped', onBackTrapped);
});

defineExpose({ open, close });
</script>

<template>
    <Teleport to="body">
        <Transition name="modal-fade">
            <div v-if="show" class="exit-overlay" @click.self="close">
                <div class="exit-modal">
                    <div class="modal-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                    </div>
                    <h3>Leave this test?</h3>
                    <p>Your progress has been saved, but the timer will keep running. Are you sure you want to exit?</p>
                    <div class="modal-actions">
                        <button @click="close" class="btn-stay">Continue Test</button>
                        <button @click="exitTest" class="btn-exit">Exit to Dashboard</button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.exit-overlay {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.exit-modal {
    background: #fff;
    border-radius: 16px;
    padding: 2rem;
    max-width: 420px;
    width: 100%;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: modal-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.modal-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #fef3c7;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
}
.modal-icon svg {
    width: 28px;
    height: 28px;
    color: #d97706;
}
.exit-modal h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}
.exit-modal p {
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}
.modal-actions {
    display: flex;
    gap: 0.75rem;
}
.btn-stay {
    flex: 1;
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    border: none;
    background: #2563eb;
    color: #fff;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: background 0.2s;
}
.btn-stay:hover { background: #1d4ed8; }
.btn-exit {
    flex: 1;
    padding: 0.625rem 1rem;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #dc2626;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-exit:hover { background: #fef2f2; border-color: #fca5a5; }

/* Transitions */
.modal-fade-enter-active, .modal-fade-leave-active {
    transition: opacity 0.25s ease;
}
.modal-fade-enter-from, .modal-fade-leave-to {
    opacity: 0;
}
@keyframes modal-pop {
    0% { transform: scale(0.85); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}
</style>
