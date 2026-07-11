<script setup>
import { ref, computed, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    testSet: { type: Object, required: true },
    section: { type: String, required: true },
    config: { type: Object, required: true },
    user: { type: Object, required: true },
    candidateNumber: { type: String, required: true },
    testDate: { type: String, required: true },
    startRoute: { type: String, required: true },
    audioCheckUrl: { type: String, default: '' },
});

// Steps computed from config
const steps = computed(() => props.config.steps || []);
const currentStepIndex = ref(0);
const currentStep = computed(() => steps.value[currentStepIndex.value]);
const totalSteps = computed(() => steps.value.length);
const isLastStep = computed(() => currentStepIndex.value === totalSteps.value - 1);

// Equipment check state
const volume = ref(50);
const audioElement = ref(null);
const isPlaying = ref(false);

// Microphone check state — live visualizer
const micListening = ref(false);
const micVerified = ref(false);
const micStatus = ref('');
const micStatusType = ref(''); // 'success', 'error', 'listening'
const micLevel = ref(0); // 0-100
const micBars = ref(Array(20).fill(3));
let micStream = null;
let micAudioCtx = null;
let micAnalyser = null;
let micAnimFrame = null;
let voiceDetectCount = 0;

// Transition
const transitionDirection = ref('forward');
const transitioning = ref(false);

// Computed
const equipmentType = computed(() => props.config.equipment_check);

const canContinueEquipment = computed(() => {
    if (equipmentType.value === 'mic') return micVerified.value;
    return true;
});

const isStartingTest = ref(false);

// Methods
function nextStep() {
    if (isLastStep.value) { startTest(); return; }
    transitionDirection.value = 'forward';
    transitioning.value = true;
    setTimeout(() => { currentStepIndex.value++; transitioning.value = false; }, 200);
}

function prevStep() {
    if (currentStepIndex.value > 0) {
        transitionDirection.value = 'backward';
        transitioning.value = true;
        setTimeout(() => { currentStepIndex.value--; transitioning.value = false; }, 200);
    }
}

function startTest() {
    isStartingTest.value = true;
    window.location.href = props.startRoute;
}

// Sound check
function playSound() {
    if (!audioElement.value) {
        audioElement.value = new Audio(props.audioCheckUrl);
        audioElement.value.volume = volume.value / 100;
    }
    audioElement.value.play();
    isPlaying.value = true;
    audioElement.value.addEventListener('ended', () => { isPlaying.value = false; });
}

function updateVolume(newVal) {
    volume.value = newVal;
    if (audioElement.value) audioElement.value.volume = newVal / 100;
}

function adjustVolume(delta) {
    updateVolume(Math.min(100, Math.max(0, volume.value + delta)));
}

// Microphone — live level detection
async function startMicCheck() {
    if (micListening.value) { stopMicCheck(); return; }
    try {
        micStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        micAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
        micAnalyser = micAudioCtx.createAnalyser();
        micAnalyser.fftSize = 256;
        micAudioCtx.createMediaStreamSource(micStream).connect(micAnalyser);

        micListening.value = true;
        micStatus.value = 'Speak now — say "Hello" or count "1, 2, 3"';
        micStatusType.value = 'listening';
        voiceDetectCount = 0;

        const dataArray = new Uint8Array(micAnalyser.frequencyBinCount);
        const update = () => {
            if (!micListening.value) return;
            micAnalyser.getByteFrequencyData(dataArray);

            // Calculate RMS level
            let sum = 0;
            for (let i = 0; i < dataArray.length; i++) sum += dataArray[i] * dataArray[i];
            const rms = Math.sqrt(sum / dataArray.length);
            const level = Math.min(100, Math.round((rms / 128) * 100));
            micLevel.value = level;

            // Update bars
            for (let i = 0; i < 20; i++) {
                const idx = Math.floor(i * dataArray.length / 20);
                micBars.value[i] = Math.max(3, (dataArray[idx] / 255) * 32);
            }

            // Voice detection: if level > 15 for enough frames
            if (level > 15) {
                voiceDetectCount++;
                if (voiceDetectCount >= 8 && !micVerified.value) {
                    micVerified.value = true;
                    micStatus.value = 'Microphone is working! You can continue.';
                    micStatusType.value = 'success';
                    // Auto-stop after verified
                    setTimeout(() => stopMicCheck(), 1500);
                }
            } else {
                voiceDetectCount = Math.max(0, voiceDetectCount - 1);
            }

            micAnimFrame = requestAnimationFrame(update);
        };
        update();
    } catch (error) {
        let msg = 'Could not access microphone.';
        if (error.name === 'NotAllowedError') msg = 'Microphone permission denied. Please allow access.';
        else if (error.name === 'NotFoundError') msg = 'No microphone found on this device.';
        else if (error.name === 'NotReadableError') msg = 'Microphone is busy — close other apps using it.';
        micStatus.value = msg;
        micStatusType.value = 'error';
    }
}

function stopMicCheck() {
    micListening.value = false;
    if (micAnimFrame) cancelAnimationFrame(micAnimFrame);
    if (micStream) { micStream.getTracks().forEach(t => t.stop()); micStream = null; }
    if (micAudioCtx) { micAudioCtx.close(); micAudioCtx = null; }
    micBars.value = Array(20).fill(3);
    micLevel.value = 0;
}

onUnmounted(() => { stopMicCheck(); });
</script>

<template>
    <div class="onboarding-root">
        <!-- Dark navbar -->
        <div class="onboarding-navbar">
            <div class="navbar-inner">
                <div class="user-info">
                    <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span>{{ user.name }} - {{ candidateNumber }}</span>
                </div>

                <!-- Volume control for sound check step -->
                <div v-if="currentStep === 'sound-check'" class="volume-controls">
                    <button @click="adjustVolume(-10)" class="vol-btn" title="Volume Down">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM12.293 7.293a1 1 0 011.414 0L15 8.586l1.293-1.293a1 1 0 111.414 1.414L16.414 10l1.293 1.293a1 1 0 01-1.414 1.414L15 11.414l-1.293 1.293a1 1 0 01-1.414-1.414L13.586 10l-1.293-1.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                    <div class="volume-slider-wrap">
                        <div class="volume-track">
                            <div class="volume-fill" :style="{ width: volume + '%' }"></div>
                        </div>
                        <input type="range" min="0" max="100" :value="volume" @input="updateVolume(+$event.target.value)" class="volume-input" />
                    </div>
                    <button @click="adjustVolume(10)" class="vol-btn" title="Volume Up">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="vol-value">{{ volume }}%</span>
                </div>
            </div>
        </div>

        <!-- Progress dots -->
        <div class="progress-section">
            <div class="progress-dots">
                <div v-for="(step, idx) in steps" :key="step"
                     :class="['dot', { active: idx === currentStepIndex, completed: idx < currentStepIndex }]">
                </div>
            </div>
            <div class="step-label">Step {{ currentStepIndex + 1 }} of {{ totalSteps }}</div>
        </div>

        <!-- Main card -->
        <div class="onboarding-main">
            <div class="onboarding-card">
                <Transition :name="transitionDirection === 'forward' ? 'slide-left' : 'slide-right'" mode="out-in">

                    <!-- ============ CONFIRM DETAILS ============ -->
                    <div v-if="currentStep === 'confirm-details'" key="confirm" class="step-content">
                        <div class="card-header">
                            <div class="header-icons">
                                <div class="icon-box bg-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-800" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg></div>
                                <div class="icon-box bg-green-600"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg></div>
                            </div>
                            <h2>Confirm your details</h2>
                        </div>

                        <div class="card-body">
                            <div class="details-grid">
                                <div class="detail-row"><span class="label">Name:</span><span class="value">{{ user.name }}</span></div>
                                <div class="detail-row"><span class="label">Date of Test:</span><span class="value">{{ testDate }}</span></div>
                                <div class="detail-row"><span class="label">Candidate Number:</span><span class="value">{{ candidateNumber }}</span></div>
                            </div>

                            <div class="info-message">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                <p>If your details are not correct, please inform the invigilator.</p>
                            </div>

                            <div class="action-center">
                                <button @click="nextStep" class="btn-confirm">My details are correct</button>
                            </div>
                        </div>
                    </div>

                    <!-- ============ SOUND CHECK ============ -->
                    <div v-else-if="currentStep === 'sound-check'" key="sound" class="step-content">
                        <div class="card-header">
                            <span class="header-emoji">🎧</span>
                            <h2>Sound Check</h2>
                        </div>
                        <div class="card-body center-body">
                            <p class="instruction-text">Put on your headphones and click on the Play Sound button to play a sample sound.</p>

                            <button @click="playSound" class="btn-play" :disabled="isPlaying">
                                <template v-if="isPlaying">
                                    <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    Playing...
                                </template>
                                <template v-else>Play Sound</template>
                            </button>

                            <div class="warning-text">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                <p>If you cannot hear the sound clearly, please check your audio settings.</p>
                            </div>

                            <button @click="nextStep" class="btn-confirm">Continue</button>
                        </div>
                    </div>

                    <!-- ============ MICROPHONE CHECK ============ -->
                    <div v-else-if="currentStep === 'microphone-check'" key="mic" class="step-content">
                        <div class="card-header">
                            <span class="header-emoji">🎤</span>
                            <h2>Microphone Check</h2>
                        </div>
                        <div class="card-body center-body">
                            <p class="instruction-text">Click the button below and speak into your microphone.</p>

                            <button @click="startMicCheck" :class="['btn-mic', { listening: micListening }]">
                                <template v-if="micListening">
                                    <span class="rec-dot"></span> Listening...
                                </template>
                                <template v-else>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd" /></svg>
                                    Test Microphone
                                </template>
                            </button>

                            <!-- Live audio visualizer -->
                            <div v-if="micListening || micVerified" class="mic-visualizer">
                                <div class="mic-bars">
                                    <div v-for="(h, i) in micBars" :key="i" class="mic-bar" :style="{ height: h + 'px', background: micVerified ? '#16a34a' : (micLevel > 15 ? '#2563eb' : '#94a3b8') }"></div>
                                </div>
                                <div class="mic-level-bar">
                                    <div class="mic-level-fill" :style="{ width: micLevel + '%', background: micLevel > 15 ? '#16a34a' : '#94a3b8' }"></div>
                                </div>
                            </div>

                            <div v-if="micStatus" :class="['mic-status', micStatusType]">
                                <svg v-if="micStatusType === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                <svg v-else-if="micStatusType === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                <span>{{ micStatus }}</span>
                            </div>

                            <button @click="nextStep" :disabled="!canContinueEquipment" :class="['btn-confirm', { enabled: canContinueEquipment }]">
                                Continue
                            </button>
                        </div>
                    </div>

                    <!-- ============ INSTRUCTIONS ============ -->
                    <div v-else-if="currentStep === 'instructions'" key="instructions" class="step-content">
                        <div class="card-header">
                            <h2>Test Instructions</h2>
                        </div>
                        <div class="card-body instructions-body">
                            <h1 class="section-title">{{ config.title }}</h1>
                            <p class="time-info">Time: {{ testSet.section?.time_limit || '60' }} minutes</p>

                            <h2 class="sub-heading">INSTRUCTIONS TO CANDIDATES</h2>
                            <ul class="instruction-list">
                                <li v-for="(inst, i) in config.instructions.candidates" :key="'c'+i" v-html="inst"></li>
                            </ul>

                            <h2 class="sub-heading">INFORMATION FOR CANDIDATES</h2>
                            <ul class="instruction-list">
                                <li v-for="(info, i) in config.instructions.information" :key="'i'+i" v-html="info"></li>
                            </ul>

                            <div class="info-message center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                <p>When you are ready to begin, click 'Start test'.</p>
                            </div>

                            <div class="action-center">
                                <button @click="startTest" :disabled="isStartingTest" class="btn-start">
                                    <template v-if="isStartingTest">
                                        <svg class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        Starting...
                                    </template>
                                    <template v-else>Start test</template>
                                </button>
                            </div>
                        </div>
                    </div>

                </Transition>
            </div>
        </div>

        <!-- Back button (floating) -->
        <button v-if="currentStepIndex > 0 && currentStep !== 'instructions'" @click="prevStep" class="back-float">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            Back
        </button>
    </div>
</template>

<style scoped>
/* ---- Root ---- */
.onboarding-root {
    min-height: 100vh;
    background-color: #eff6ff;
    overflow: hidden;
    position: fixed;
    inset: 0;
    font-family: system-ui, -apple-system, sans-serif;
}

/* ---- Navbar ---- */
.onboarding-navbar {
    background-color: #374151;
    padding: 0.6rem 0;
}
.navbar-inner {
    max-width: 72rem;
    margin: 0 auto;
    padding: 0 1rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.user-info {
    color: #fff;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}
.nav-icon { width: 1.25rem; height: 1.25rem; }

/* Volume controls */
.volume-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.vol-btn {
    color: #fff;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
    transition: color 0.2s;
}
.vol-btn:hover { color: #d1d5db; }
.volume-slider-wrap { position: relative; width: 8rem; }
.volume-track { width: 100%; background: #4b5563; border-radius: 9999px; height: 0.5rem; }
.volume-fill { background: #fff; height: 0.5rem; border-radius: 9999px; transition: width 0.2s; }
.volume-input { position: absolute; top: 0; left: 0; width: 100%; height: 0.5rem; opacity: 0; cursor: pointer; }
.vol-value { color: #fff; font-size: 0.875rem; min-width: 2.5rem; }

/* ---- Progress ---- */
.progress-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 0 0.5rem;
}
.progress-dots {
    display: flex;
    gap: 0.5rem;
}
.dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #cbd5e1;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.dot.active {
    background: #1e40af;
    width: 24px;
    border-radius: 4px;
}
.dot.completed {
    background: #1e40af;
}
.step-label {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 0.5rem;
}

/* ---- Main Card ---- */
.onboarding-main {
    max-width: 48rem;
    margin: 1rem auto 0;
    padding: 0 1rem;
}
.onboarding-card {
    background: #fff;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.07), 0 2px 4px -2px rgba(0,0,0,0.05);
    overflow: hidden;
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}

/* ---- Card header ---- */
.card-header {
    background: #111827;
    padding: 0.75rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #fff;
}
.card-header h2 { font-size: 1rem; font-weight: 500; }
.header-icons { display: flex; gap: 0.5rem; }
.icon-box { padding: 0.25rem; border-radius: 0.375rem; display: flex; align-items: center; justify-content: center; }
.header-emoji { font-size: 1.5rem; }

/* ---- Card body ---- */
.card-body { padding: 1.5rem; background: #f3f4f6; }
.center-body { text-align: center; }
.instructions-body { padding: 2rem; }

/* ---- Details grid ---- */
.details-grid { margin-bottom: 1.5rem; }
.detail-row { display: flex; padding: 0.5rem 0; }
.detail-row .label { width: 45%; color: #374151; }
.detail-row .value { width: 55%; color: #374151; font-weight: 600; }

/* ---- Info message ---- */
.info-message { display: flex; align-items: center; gap: 0.5rem; color: #2563eb; margin-bottom: 1.5rem; }
.info-message.center { justify-content: center; }



/* ---- Buttons ---- */
.action-center { display: flex; justify-content: center; margin-top: 1rem; }
.btn-confirm {
    background: #e5e7eb; color: #374151; padding: 0.5rem 1.5rem;
    border: 1px solid #d1d5db; border-radius: 0.375rem;
    cursor: pointer; font-weight: 600; font-size: 0.9rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
}
.btn-confirm:hover { background: #d1d5db; }
.btn-confirm:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-confirm.enabled { background: #16a34a; color: #fff; border-color: #16a34a; }
.btn-confirm.enabled:hover { background: #15803d; }
.btn-confirm.pulse { animation: pulse-green 2s infinite; }

.btn-start {
    background: #e5e7eb; color: #374151; padding: 0.5rem 1.5rem;
    border: 1px solid #d1d5db; border-radius: 0.375rem;
    cursor: pointer; font-weight: 600; font-size: 0.9rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    transition: all 0.2s;
    display: inline-flex; align-items: center;
}
.btn-start:hover { background: #d1d5db; }
.btn-start:disabled { opacity: 0.6; cursor: wait; }

.btn-play {
    background: #2563eb; color: #fff; padding: 0.5rem 1.5rem;
    border: none; border-radius: 0.375rem;
    cursor: pointer; font-weight: 600; font-size: 0.9rem;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    transition: all 0.2s; margin-bottom: 1.5rem;
    display: inline-flex; align-items: center;
}
.btn-play:hover { background: #1d4ed8; }
.btn-play:disabled { opacity: 0.7; cursor: not-allowed; }

.btn-mic {
    background: #111827; color: #fff; padding: 0.75rem 2rem;
    border: none; border-radius: 0.5rem;
    cursor: pointer; font-weight: 600; font-size: 0.95rem;
    transition: all 0.3s; margin-bottom: 1.25rem;
    display: inline-flex; align-items: center; gap: 0.5rem;
}
.btn-mic:hover { background: #374151; }
.btn-mic.listening { background: #2563eb; animation: pulse-blue 1.5s infinite; }
.rec-dot { width: 8px; height: 8px; background: #fff; border-radius: 50%; animation: blink 0.8s infinite; }

/* ---- Mic visualizer ---- */
.mic-visualizer {
    margin: 0 auto 1.25rem;
    max-width: 320px;
}
.mic-bars {
    display: flex; align-items: center; justify-content: center;
    gap: 3px; height: 36px; margin-bottom: 0.75rem;
}
.mic-bar {
    width: 4px; border-radius: 2px;
    transition: height 0.06s ease, background 0.3s;
    min-height: 3px;
}
.mic-level-bar {
    width: 100%; height: 6px; background: #e5e7eb;
    border-radius: 9999px; overflow: hidden;
}
.mic-level-fill {
    height: 100%; border-radius: 9999px;
    transition: width 0.1s ease, background 0.3s;
}

/* ---- Mic status ---- */
.mic-status {
    display: flex; align-items: center; justify-content: center;
    gap: 0.5rem; font-size: 0.9rem; font-weight: 500;
    margin-bottom: 1.25rem; min-height: 24px;
}
.mic-status.success { color: #16a34a; }
.mic-status.error { color: #dc2626; }
.mic-status.listening { color: #2563eb; }

/* ---- Warning text ---- */
.warning-text { display: flex; align-items: center; justify-content: center; gap: 0.5rem; color: #dc2626; margin-bottom: 1.5rem; font-size: 0.9rem; }

/* ---- Instructions ---- */
.section-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.time-info { margin-bottom: 1.5rem; color: #374151; }
.sub-heading { font-size: 1.125rem; font-weight: 700; margin-bottom: 1rem; }
.instruction-list { list-style: disc; padding-left: 2rem; margin-bottom: 1.5rem; }
.instruction-list li { margin-bottom: 0.5rem; line-height: 1.5; }
.instruction-text { font-size: 1.05rem; margin-bottom: 1.5rem; line-height: 1.5; }

/* ---- Back button ---- */
.back-float {
    position: fixed; bottom: 2rem; left: 2rem;
    background: #fff; color: #374151;
    border: 1px solid #d1d5db; border-radius: 0.5rem;
    padding: 0.5rem 1rem; cursor: pointer;
    display: flex; align-items: center; gap: 0.25rem;
    font-weight: 500; font-size: 0.9rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.2s;
}
.back-float:hover { background: #f3f4f6; transform: translateY(-1px); }

/* ---- Transitions ---- */
.slide-left-enter-active, .slide-left-leave-active,
.slide-right-enter-active, .slide-right-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-left-enter-from { opacity: 0; transform: translateX(30px); }
.slide-left-leave-to { opacity: 0; transform: translateX(-30px); }
.slide-right-enter-from { opacity: 0; transform: translateX(-30px); }
.slide-right-leave-to { opacity: 0; transform: translateX(30px); }

/* ---- Animations ---- */
@keyframes pulse-green {
    0%, 100% { box-shadow: 0 0 0 0 rgba(22, 163, 74, 0.7); }
    50% { box-shadow: 0 0 0 10px rgba(22, 163, 74, 0); }
}
@keyframes pulse-blue {
    0%, 100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.6); }
    50% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
}
@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}

/* ---- Responsive ---- */
@media (max-width: 640px) {
    .onboarding-main { padding: 0 0.5rem; }
    .card-body { padding: 1rem; }
    .ip-actions { flex-direction: column; }
}
</style>
