<script setup>
import { ref, computed, onMounted, onUnmounted, reactive, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ExamHeader from '@/Components/Exam/ExamHeader.vue';
import AntiCheat from '@/Components/Exam/AntiCheat.vue';
import ExitConfirmModal from '@/Components/Exam/ExitConfirmModal.vue';
import AutoSubmitOverlay from '@/Components/Exam/AutoSubmitOverlay.vue';

const props = defineProps({
    testSet: { type: Object, required: true },
    attempt: { type: Object, required: true },
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true }
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Removed local overall timer vars

// Questions Data mapping
const questions = computed(() => {
    return (props.testSet.questions || []).map((q, idx) => ({
        ...q,
        index: idx,
        read_time: q.read_time ?? (q.part_number == 2 ? 60 : 5),
        max_response_time: q.max_response_time ?? (q.part_number == 2 ? 120 : 45),
        pause_before_record: q.pause_before_record ?? 3,
        has_avatar: q.avatar_video_url ? true : false,
    }));
});

const totalQuestions = computed(() => questions.value.length);
const activeQuestionIndex = ref(0);
const activeQuestion = computed(() => questions.value[activeQuestionIndex.value]);

// Recording and Progress State
const recordingsCompleted = ref(0);
const isGlobalRecording = ref(false);
const globalRecordingProgress = ref(1.0);
const globalRecordingTimeStr = ref('00:00');
const isSubmitting = ref(false);
const isTimeUpSubmit = ref(false);

const cueCardPhase = ref({}); // 'prep' or 'recording'
const cueCardPrepTimeStr = ref({});

// Timers
let prepInterval;
let readingInterval;
let recordingInterval;
let maxRecordTimeout;
let partCountdownInterval;

// Recording refs
let mediaRecorder = null;
let audioChunks = [];
let recordingStartTime = null;
let currentStream = null;
const recordingsParams = ref({}); // Track processed status

// Visualizer
let audioContext = null;
let analyser = null;
let animationFrameId = null;
const visualizerHeights = ref(Array(30).fill(3));

// Modals
const showConfirmNext = ref(false);
const showPartComplete = ref(false);
const showReview = ref(false);

const completedPartNum = ref(1);
const nextPartNum = ref(2);
const partCountdown = ref(5);

const partNames = {
    1: 'Question & Answers',
    2: 'Cue Card',
    3: 'Discussion'
};

// Start logic
onMounted(() => {
    
    // Attempt to restore progress from localStorage
    const storageKey = `speaking_progress_${props.attempt.id}`;
    try {
        const saved = localStorage.getItem(storageKey);
        if (saved) {
            const data = JSON.parse(saved);
            activeQuestionIndex.value = data.currentIndex || 0;
            recordingsCompleted.value = data.recordingsCompleted || 0;
            recordingsParams.value = data.recordingDurations || {};
        }
    } catch(e) {}
    
    initializeQuestion(activeQuestionIndex.value);
});

onUnmounted(() => {
    clearAllTimers();
    if (audioContext) audioContext.close();
});

const saveProgress = () => {
    const storageKey = `speaking_progress_${props.attempt.id}`;
    localStorage.setItem(storageKey, JSON.stringify({
        currentIndex: activeQuestionIndex.value,
        recordingsCompleted: recordingsCompleted.value,
        recordingDurations: recordingsParams.value
    }));
};

const clearAllTimers = () => {
    clearInterval(prepInterval);
    clearInterval(readingInterval);
    clearInterval(recordingInterval);
    clearTimeout(maxRecordTimeout);
    clearInterval(partCountdownInterval);
    if (animationFrameId) cancelAnimationFrame(animationFrameId);
};

const formatSeconds = (sec) => {
    const mins = Math.floor(sec / 60);
    const secs = Math.floor(sec % 60);
    return `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
};

// Initialize Question Phase
const initializeQuestion = (index) => {
    clearAllTimers();
    
    if (index >= questions.value.length) {
        showReview.value = true;
        return;
    }
    
    const q = questions.value[index];
    isGlobalRecording.value = false;
    
    if (q.part_number === 2) {
        cueCardPhase.value[q.id] = 'prep';
        cueCardPrepTimeStr.value[q.id] = formatSeconds(q.read_time);
        
        // Wait till pip video ends or just start prep timer
        startPrepTimer(q);
    } else {
        startReadingTimer(q);
    }
};

const startPrepTimer = (q) => {
    let t = q.read_time;
    prepInterval = setInterval(() => {
        t--;
        cueCardPrepTimeStr.value[q.id] = formatSeconds(t);
        if (t <= 0) {
            clearInterval(prepInterval);
            startRecordingProcess(q);
        }
    }, 1000);
};

const startReadingTimer = (q) => {
    let t = q.read_time;
    readingInterval = setInterval(() => {
        t--;
        if (t <= 0) {
            clearInterval(readingInterval);
            startRecordingProcess(q);
        }
    }, 1000);
};

// Recording Flow
const startRecordingProcess = async (q) => {
    clearAllTimers();
    
    if (q.part_number === 2) {
        cueCardPhase.value[q.id] = 'recording';
        cueCardPrepTimeStr.value[q.id] = formatSeconds(q.max_response_time);
    }
    
    isGlobalRecording.value = true;
    
    try {
        currentStream = await navigator.mediaDevices.getUserMedia({ audio: true });
        mediaRecorder = new MediaRecorder(currentStream);
        audioChunks = [];
        
        mediaRecorder.ondataavailable = (e) => audioChunks.push(e.data);
        mediaRecorder.onstop = async () => {
            const blob = new Blob(audioChunks, { type: 'audio/webm' });
            await uploadRecording(q.id, blob);
            if (currentStream) currentStream.getTracks().forEach(track => track.stop());
        };
        
        mediaRecorder.start();
        recordingStartTime = Date.now();
        
        updateRecordingTimer(q);
        startVisualizer(currentStream);
        
        maxRecordTimeout = setTimeout(() => {
            proceedToNext();
        }, q.max_response_time * 1000);
        
    } catch(e) {
        console.error('Recording fail', e);
        alert('Could not access microphone.');
        isGlobalRecording.value = false;
    }
};

const updateRecordingTimer = (q) => {
    const maxTime = q.max_response_time;
    recordingInterval = setInterval(() => {
        if(!recordingStartTime) return;
        const elapsed = Math.floor((Date.now() - recordingStartTime) / 1000);
        const remaining = Math.max(0, maxTime - elapsed);
        
        globalRecordingProgress.value = remaining / maxTime;
        globalRecordingTimeStr.value = formatSeconds(elapsed);
        
        if (q.part_number === 2) {
            cueCardPrepTimeStr.value[q.id] = formatSeconds(remaining);
        }
    }, 1000);
};

const startVisualizer = (stream) => {
    audioContext = new (window.AudioContext || window.webkitAudioContext)();
    analyser = audioContext.createAnalyser();
    const source = audioContext.createMediaStreamSource(stream);
    source.connect(analyser);
    analyser.fftSize = 128;
    const dataArray = new Uint8Array(analyser.frequencyBinCount);
    
    const update = () => {
        if (!analyser) return;
        analyser.getByteFrequencyData(dataArray);
        for(let i=0; i<30; i++) {
            const dataIndex = Math.floor(i * dataArray.length / 30);
            const val = dataArray[dataIndex] || 0;
            visualizerHeights.value[i] = Math.max(3, (val / 255) * 18);
        }
        animationFrameId = requestAnimationFrame(update);
    };
    update();
};

const uploadRecording = async (questionId, audioBlob) => {
    const formData = new FormData();
    formData.append('recording', audioBlob, 'recording.webm');
    try {
        await axios.post(`/student/test/speaking/record/${props.attempt.id}/${questionId}`, formData);
        recordingsParams.value[questionId] = true;
    } catch (e) { console.error('Upload failed'); }
};

// Next / Navigation
const requestNext = () => {
    if (isGlobalRecording.value) {
        showConfirmNext.value = true;
    } else {
        proceedToNext();
    }
};

const proceedToNext = async () => {
    showConfirmNext.value = false;
    
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
    
    clearAllTimers();
    isGlobalRecording.value = false;
    recordingsCompleted.value++;
    globalRecordingTimeStr.value = '00:00';
    
    saveProgress();
    
    const currPart = activeQuestion.value.part_number;
    const nextIdx = activeQuestionIndex.value + 1;
    
    await new Promise(r => setTimeout(r, 500)); // allow upload start
    
    if (nextIdx < questions.value.length && questions.value[nextIdx].part_number !== currPart) {
        completedPartNum.value = currPart;
        nextPartNum.value = questions.value[nextIdx].part_number;
        partCountdown.value = 5;
        showPartComplete.value = true;
        
        partCountdownInterval = setInterval(() => {
            partCountdown.value--;
            if (partCountdown.value <= 0) {
                clearInterval(partCountdownInterval);
                continueToPart();
            }
        }, 1000);
    } else {
        transitionToNext();
    }
};

const continueToPart = () => {
    clearInterval(partCountdownInterval);
    showPartComplete.value = false;
    transitionToNext();
};

const transitionToNext = () => {
    activeQuestionIndex.value++;
    saveProgress();
    initializeQuestion(activeQuestionIndex.value);
};

const handleTimeUp = () => {
    // Stop any active recording
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.stop();
    }
    clearAllTimers();
    isGlobalRecording.value = false;
    if (currentStream) currentStream.getTracks().forEach(track => track.stop());
    isTimeUpSubmit.value = true;
};

const finalSubmit = () => {
    const storageKey = `speaking_progress_${props.attempt.id}`;
    localStorage.removeItem(storageKey);
    isSubmitting.value = true;
    router.post(`/student/test/speaking/submit/${props.attempt.id}`);
};

</script>

<template>
    <AntiCheat />
    <ExitConfirmModal />
    <AutoSubmitOverlay 
        v-if="isTimeUpSubmit"
        :attemptId="attempt.id"
        :answers="{}"
        :storageKey="`speaking_progress_${attempt.id}`"
        section="speaking"
    />
    <div class="main-container">

        <!-- Recording Progress Bar -->
        <div class="recording-progress-bar" :class="{ 'hidden': !isGlobalRecording }">
            <div class="recording-progress-fill" :style="{ width: (globalRecordingProgress * 100) + '%', background: globalRecordingProgress > 0.33 ? '#22c55e' : (globalRecordingProgress > 0.15 ? '#eab308' : '#ef4444') }"></div>
        </div>

        <!-- Top Bar using ExamHeader from global components -->
        <ExamHeader 
            moduleName="Speaking"
            :timeLimitSeconds="timeLimitSeconds"
            :serverTime="serverTime"
            :attemptStartTime="attempt.start_time"
            @timeUp="handleTimeUp"
        >
            <template #extra-controls>
                <div class="top-bar-recording" :class="{ 'active': isGlobalRecording }" :style="{ background: globalRecordingProgress > 0.33 ? '#22c55e' : (globalRecordingProgress > 0.15 ? '#eab308' : '#ef4444') }">
                    <div class="top-bar-recording-dot"></div>
                    <span class="top-bar-recording-text">REC</span>
                    <span class="top-bar-recording-time">{{ globalRecordingTimeStr }}</span>
                </div>
            </template>
        </ExamHeader>

        <!-- Main Content Layout -->
        <div class="main-content">
            
            <div v-if="activeQuestion" class="question-card active">
                <!-- PART 2 -->
                <div v-if="activeQuestion.part_number == 2" class="main-layout">
                    <div class="left-sidebar">
                        <div class="part-title">Part 2: Cue Card</div>
                    </div>

                    <div class="center-content">
                        <div class="cue-card-container" :class="cueCardPhase[activeQuestion.id] === 'recording' ? 'recording-phase' : 'prep-phase'">
                            <div class="phase-indicator">
                                {{ cueCardPhase[activeQuestion.id] === 'recording' ? 'SPEAKING NOW' : 'PREPARATION TIME' }}
                            </div>

                            <span class="prep-timer">{{ cueCardPrepTimeStr[activeQuestion.id] }}</span>

                            <!-- If complex cue card with form structure -->
                            <template v-if="activeQuestion.form_structure && activeQuestion.form_structure.fields">
                                <div class="cue-card-topic" v-html="activeQuestion.content"></div>
                                <ul class="cue-card-points">
                                    <li v-for="point in activeQuestion.form_structure.fields" :key="point.label">{{ point.label }}</li>
                                </ul>
                            </template>
                            <!-- Simple cue card -->
                            <template v-else>
                                <div class="cue-card-label">Cue Card</div>
                                <div class="cue-card-topic" v-html="activeQuestion.content"></div>
                            </template>

                            <button v-if="cueCardPhase[activeQuestion.id] === 'prep'" type="button" class="start-speaking-btn" @click="startRecordingProcess(activeQuestion)">
                                Start Speaking Now
                            </button>
                        </div>
                    </div>

                    <div class="right-sidebar">
                        <!-- PIP video could go here -->
                    </div>
                </div>

                <!-- PART 1 or 3 -->
                <div v-else class="main-layout">
                    <div class="left-sidebar">
                        <div class="part-title">Part {{ activeQuestion.part_number }}: {{ activeQuestion.part_number == 1 ? 'Question & Answers' : 'Discussion' }}</div>
                        <div class="question-number">Question {{ activeQuestionIndex + 1 }}</div>
                    </div>

                    <div class="center-content">
                        <div class="text-question-box">
                            <div class="text-question-content" v-html="activeQuestion.content"></div>
                            <div v-if="!isGlobalRecording" class="read-timer text-slate-500 font-bold text-2xl mt-4">
                                Waiting to record...
                            </div>
                        </div>
                    </div>

                    <div class="right-sidebar"></div>
                </div>

            </div>
            
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <div class="bottom-nav-inner flex justify-between w-full mx-auto max-w-4xl">
                <!-- Recording Box -->
                <div class="recording-box" :class="{ 'active': isGlobalRecording }">
                    <div class="rec-indicator"></div>
                    <span class="recording-label">Recording</span>
                    <div class="wave-visualizer">
                        <div v-for="(h, i) in visualizerHeights" :key="i" class="wave-bar" :style="{ height: h + 'px' }"></div>
                    </div>
                    <span class="recording-time">{{ globalRecordingTimeStr }}</span>
                </div>

                <!-- Next Button -->
                <button type="button" class="next-btn" :disabled="!isGlobalRecording" @click="requestNext">
                    Next Question
                </button>

                <!-- Submit Button -->
                <button type="button" @click="showReview = true" class="submit-btn" style="margin-left:auto;">Submit Test</button>
            </div>
        </div>

        <!-- Modals -->

        <!-- Confirm Next Question Modal -->
        <div class="confirm-next-modal" :class="{ 'active': showConfirmNext }">
            <div class="confirm-next-content">
                <svg class="confirm-next-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="confirm-next-title">Move to Next Question?</div>
                <div class="confirm-next-message">
                    Your recording will be saved and you <strong>cannot re-record</strong> this answer.<br><br>
                    Are you sure you want to continue?
                </div>
                <div class="confirm-next-buttons">
                    <button class="confirm-next-btn secondary" @click="showConfirmNext = false">Keep Recording</button>
                    <button class="confirm-next-btn primary" @click="proceedToNext">Save & Continue</button>
                </div>
            </div>
        </div>

        <!-- Part Complete Modal -->
        <div class="modal-overlay" v-if="showPartComplete">
            <div class="part-modal-content">
                <div class="part-modal-title">Part {{ completedPartNum }} Complete!</div>
                <div class="part-modal-subtitle">Great job! Take a moment to prepare.</div>
                <div class="part-modal-next-info">
                    <strong>Next:</strong> Part {{ nextPartNum }} - {{ partNames[nextPartNum] }}
                </div>
                <div class="part-modal-countdown">
                    Continue available in <span>{{ partCountdown }}</span> seconds
                </div>
                <button class="part-modal-btn" :disabled="partCountdown > 0" @click="continueToPart">
                    {{ partCountdown > 0 ? 'Please wait...' : 'Continue to Part ' + nextPartNum }}
                </button>
            </div>
        </div>

        <!-- Review Modal -->
        <div class="modal-overlay" v-if="showReview">
            <div class="review-modal-content">
                <div class="review-title">Review Your Answers</div>
                <div class="review-subtitle">Please review before submitting</div>

                <div class="review-summary">
                    <div class="review-stat">
                        <div class="review-stat-number recorded">{{ recordingsCompleted }}</div>
                        <div class="review-stat-label">Recorded</div>
                    </div>
                    <div class="review-stat">
                        <div class="review-stat-number not-recorded">{{ totalQuestions - recordingsCompleted }}</div>
                        <div class="review-stat-label">Not Recorded</div>
                    </div>
                </div>

                <div class="review-buttons flex gap-3">
                    <button class="review-btn cancel" @click="showReview = false">Cancel</button>
                    <button class="review-btn submit w-full" @click="finalSubmit" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Submitting...' : 'Submit Test' }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
        /* Modern CSS Variables & Reset (Minimalist) */
        :root {
            --primary: #0f172a;
            --primary-hover: #334155;
            --success: #166534;
            --danger: #dc2626;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #cbd5e1;
            --radius-md: 4px;
            --radius-lg: 8px;
        }

        * { box-sizing: border-box; }
        
        .main-container {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-body);
            height: 100vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            color: var(--text-main);
        }

        /* ==================== RECORDING STATE STYLES ==================== */
        .recording-progress-bar {
            position: fixed;
            bottom: 65px;
            left: 0;
            height: 3px;
            width: 100%;
            background: var(--border-color);
            z-index: 9999;
            pointer-events: none;
        }

        .recording-progress-fill {
            height: 100%;
            width: 100%;
            background: var(--success);
            transition: width 0.5s linear;
            transform-origin: left;
        }

        .hidden { display: none !important; }

        .top-bar-recording {
            display: none;
            align-items: center;
            gap: 8px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            padding: 4px 12px;
            border-radius: var(--radius-md);
        }

        .top-bar-recording.active { display: flex; }
        .top-bar-recording-dot { width: 8px; height: 8px; background: var(--danger); border-radius: 50%; }
        .top-bar-recording-text { color: var(--text-main); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
        .top-bar-recording-time { color: var(--text-main); font-size: 13px; font-weight: 600; font-feature-settings: 'tnum'; }

        /* ==================== CUE CARD PHASE STYLES ==================== */
        .cue-card-container {
            background: var(--bg-card);
            border-radius: var(--radius-lg);
            padding: 40px;
            max-width: 650px;
            width: 100%;
            min-height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .cue-card-container.prep-phase { border-top: 4px solid var(--text-main); }
        .cue-card-container.recording-phase { border-top: 4px solid var(--danger); }

        .phase-indicator {
            position: absolute;
            top: 24px;
            left: 40px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-muted);
        }
        
        .cue-card-container.recording-phase .phase-indicator { color: var(--danger); }

        .prep-timer {
            font-size: 16px;
            font-weight: 700;
            position: absolute;
            top: 24px;
            right: 40px;
            font-feature-settings: 'tnum';
            color: var(--text-main);
        }

        .cue-card-label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .cue-card-topic { font-size: 20px; font-weight: 600; color: var(--text-main); margin-bottom: 24px; line-height: 1.5; }
        
        .cue-card-points { list-style: none; padding: 0; margin: 0; background: #fafafa; border: 1px solid #e5e5e5; border-radius: var(--radius-md); padding: 20px 24px; }
        .cue-card-points li { 
            padding: 8px 0 8px 24px; position: relative; font-size: 16px; color: #111; line-height: 1.5; 
        }
        .cue-card-points li::before { 
            content: '•'; position: absolute; left: 8px; top: 8px; color: var(--text-muted); font-size: 18px;
        }
        
        .start-speaking-btn { 
            background: var(--primary); color: white; border: none; 
            padding: 14px 32px; border-radius: var(--radius-md); font-size: 15px; font-weight: 600; cursor: pointer; 
            margin-top: 32px; align-self: flex-start; transition: background 0.2s; 
        }
        .start-speaking-btn:hover { background: var(--primary-hover); }

        /* Text Box Fallback for Part 1 & 3 */
        .text-question-box { 
            background: var(--bg-card); border-radius: var(--radius-lg); padding: 48px; max-width: 600px; width: 100%; 
            border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .text-question-content { font-size: 20px; font-weight: 500; line-height: 1.6; color: var(--text-main); margin-bottom: 24px; }
        .read-timer { 
            display: inline-flex; align-items: center; justify-content: flex-start;
            font-size: 14px; font-weight: 500; color: var(--text-muted);
        }

        /* ==================== MODALS ==================== */
        .confirm-next-modal { position: fixed; inset: 0; background: rgba(255, 255, 255, 0.95); display: none; align-items: center; justify-content: center; z-index: 10001; }
        .confirm-next-modal.active { display: flex; }
        .confirm-next-content { background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); max-width: 420px; width: 90%; text-align: center; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .confirm-next-title { font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 12px; }
        .confirm-next-message { font-size: 15px; color: var(--text-muted); margin-bottom: 24px; line-height: 1.5; }
        .confirm-next-buttons { display: flex; gap: 12px; justify-content: center; }
        
        .btn-base { padding: 12px 24px; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s; border: 1px solid transparent; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: white; color: var(--text-main); border-color: var(--border-color); }
        .btn-secondary:hover { background: #f8fafc; }

        .part-modal-content, .review-modal-content { background: var(--bg-card); padding: 40px; border-radius: var(--radius-lg); max-width: 480px; width: 90%; text-align: center; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .part-modal-title { font-size: 20px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        .part-modal-subtitle { font-size: 15px; color: var(--text-muted); margin-bottom: 24px; }
        .part-modal-next-info { background: #f8fafc; padding: 16px; border-radius: var(--radius-md); margin: 20px 0; font-size: 15px; color: var(--text-main); border: 1px solid var(--border-color); }
        .part-modal-countdown { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
        .part-modal-countdown span { font-weight: 700; color: var(--text-main); }
        
        .part-modal-btn { width: 100%; }
        .part-modal-btn:disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; border-color: transparent; }

        .review-title { font-size: 20px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; }
        .review-subtitle { font-size: 14px; color: var(--text-muted); margin-bottom: 24px; }
        .review-summary { display: flex; justify-content: center; gap: 40px; margin-bottom: 32px; padding: 24px; background: #fff; border-radius: var(--radius-md); border: 1px solid #e2e8f0; }
        .review-stat { text-align: center; }
        .review-stat-number { font-size: 28px; font-weight: 700; line-height: 1; margin-bottom: 8px; }
        .review-stat-number.recorded { color: var(--success); }
        .review-stat-number.not-recorded { color: var(--danger); }
        .review-stat-label { font-size: 11px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        .review-btn { flex: 1; }

        .modal-overlay { position: fixed; inset: 0; background: rgba(255, 255, 255, 0.95); display: flex; align-items: center; justify-content: center; z-index: 1000; }

        /* ==================== MAIN CONTENT ==================== */
        .main-content { flex: 1; overflow: auto; position: relative; display: flex; justify-content: center; padding: 40px 20px; }
        .main-layout { width: 100%; max-width: 1100px; display: flex; gap: 40px; margin: 0 auto; height: fit-content; align-items: flex-start; }
        
        .left-sidebar { width: 200px; flex-shrink: 0; border-right: 1px solid var(--border-color); min-height: 200px; }
        .part-title { font-size: 18px; font-weight: 700; color: var(--text-main); margin-bottom: 8px; line-height: 1.3; }
        .question-number { font-size: 14px; font-weight: 500; color: var(--text-muted); }
        
        .center-content { flex: 1; display: flex; justify-content: flex-start; }
        .right-sidebar { width: 200px; flex-shrink: 0; }

        .question-card { width: 100%; display: none; }
        .question-card.active { display: block; }

        /* ==================== BOTTOM NAV & RECORDING VISUALIZER ==================== */
        .bottom-nav { 
            position: fixed; bottom: 0; left: 0; right: 0; background: #ffffff; 
            border-top: 1px solid var(--border-color); padding: 12px 32px; z-index: 100; height: 65px; box-sizing: border-box; 
        }
        .bottom-nav-inner { display: flex; align-items: center; justify-content: space-between; gap: 24px; width: 100%; max-width: 1200px; margin: 0 auto; height: 100%; }

        /* Flat Recording Box */
        .recording-box { 
            flex: 1; background: #f8fafc; border-radius: var(--radius-md); padding: 8px 16px; display: flex; align-items: center; gap: 16px; 
            opacity: 0.5; transition: opacity 0.2s; max-width: 400px; margin: 0 auto;
            border: 1px solid #e2e8f0;
        }
        .recording-box.active { 
            opacity: 1; background: #fff; border-color: #cbd5e1;
        }
        
        .rec-indicator { width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; flex-shrink: 0; }
        .recording-box.active .rec-indicator { background: var(--danger); box-shadow: 0 0 0 2px #fecaca; }
        
        .recording-label { font-size: 12px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .recording-box.active .recording-label { color: var(--danger); }
        
        .wave-visualizer { display: flex; align-items: center; gap: 2px; height: 20px; flex: 1; justify-content: center; }
        .wave-bar { width: 3px; background: #cbd5e1; border-radius: 2px; transition: height 0.05s ease; min-height: 3px; }
        .recording-box.active .wave-bar { background: #94a3b8; }
        
        .recording-time { font-size: 14px; font-weight: 600; color: #475569; font-feature-settings: 'tnum'; min-width: 45px; text-align: right; }
        .recording-box.active .recording-time { color: var(--text-main); }

        .next-btn { 
            background: white; border: 1px solid var(--border-color); padding: 8px 20px; border-radius: var(--radius-md); font-size: 14px; font-weight: 600; 
            color: var(--text-main); cursor: pointer; transition: background 0.2s; white-space: nowrap;
        }
        .next-btn:not(:disabled):hover { background: #f1f5f9; }
        .next-btn:disabled { opacity: 0.4; cursor: not-allowed; }

        .submit-btn { 
            background: transparent; color: var(--text-muted); border: none; padding: 8px 20px; 
            font-size: 13px; font-weight: 600; cursor: pointer; transition: color 0.2s;
        }
        .submit-btn:hover { color: var(--text-main); text-decoration: underline; }

        /* Media Queries */
        @media (max-width: 900px) {
            .right-sidebar { display: none; }
            .left-sidebar { border-right: none; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; min-height: auto; width: 100%; display: flex; gap: 12px; align-items: baseline; }
            .main-layout { flex-direction: column; max-width: 700px; gap: 24px; }
        }
        
        @media (max-width: 768px) {
            .bottom-nav { height: auto; padding: 12px 16px; }
            .bottom-nav-inner { flex-wrap: wrap; justify-content: space-between; gap: 12px; }
            .recording-box { width: 100%; max-width: none; order: 1; }
            .next-btn { order: 2; flex: 1; }
            .submit-btn { order: 3; padding-right: 0; }
        }
</style>
