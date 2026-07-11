<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import debounce from 'lodash/debounce';
import ExamHeader from '@/Components/Exam/ExamHeader.vue';
import AntiCheat from '@/Components/Exam/AntiCheat.vue';
import ExitConfirmModal from '@/Components/Exam/ExitConfirmModal.vue';
import AutoSubmitOverlay from '@/Components/Exam/AutoSubmitOverlay.vue';

const props = defineProps({
    testSet: { type: Object, required: true },
    attempt: { type: Object, required: true },
    timeLimitSeconds: { type: Number, required: true },
    serverTime: { type: String, required: true },
    initialAnswers: { type: Object, default: () => ({}) },
    isSingleTask: { type: Boolean, default: false },
    taskNumber: { type: Number, default: null },
});

const page = usePage();
const user = computed(() => page.props.auth.user);

// Questions
const questions = computed(() => props.testSet.questions || []);

// For single-task mode, the single question is the only one
const task1Question = computed(() => {
    if (props.isSingleTask) return props.taskNumber === 1 ? questions.value[0] : null;
    return questions.value[0];
});
const task2Question = computed(() => {
    if (props.isSingleTask) return props.taskNumber === 2 ? questions.value[0] : null;
    return questions.value[1];
});

// The active question in single-task mode
const singleQuestion = computed(() => props.isSingleTask ? questions.value[0] : null);

// Answers — initialize dynamically from whatever questions exist
const initAnswers = {};
(props.testSet.questions || []).forEach(q => {
    initAnswers[q.id] = props.initialAnswers[q.id]?.answer || '';
});
const answers = ref(initAnswers);

// UI State
const activeTask = ref(props.isSingleTask ? (props.taskNumber || 1) : 1);
const isSubmitting = ref(false);
const isTimeUpSubmit = ref(false);
const saveStatus = ref('');
const showSubmitModal = ref(false);

const wordCountTask1 = computed(() => {
    if (!task1Question.value) return 0;
    const text = answers.value[task1Question.value?.id] || '';
    return text.trim() ? text.trim().split(/\s+/).length : 0;
});

const wordCountTask2 = computed(() => {
    if (!task2Question.value) return 0;
    const text = answers.value[task2Question.value?.id] || '';
    return text.trim() ? text.trim().split(/\s+/).length : 0;
});

const currentWordCount = computed(() => {
    if (props.isSingleTask) {
        return singleQuestion.value ? (answers.value[singleQuestion.value.id] || '').trim().split(/\s+/).filter(w => w).length : 0;
    }
    return activeTask.value === 1 ? wordCountTask1.value : wordCountTask2.value;
});

// Auto-save logic
const autoSave = debounce(async () => {
    saveStatus.value = 'Saving...';
    try {
        await axios.post(`/student/test/writing/autosave/${props.attempt.id}`, {
            answers: answers.value
        });
        saveStatus.value = 'Saved';
        setTimeout(() => { if (saveStatus.value === 'Saved') saveStatus.value = ''; }, 2000);
    } catch (e) {
        saveStatus.value = 'Error saving';
    }
}, 5000);

watch(answers, () => autoSave(), { deep: true });

onUnmounted(() => {
    autoSave.cancel();
});

// The fullscreen logic is moved to ExamHeader

const handleTimeUp = () => {
    isTimeUpSubmit.value = true;
};

const confirmSubmit = () => {
    isSubmitting.value = true;
    router.post(`/student/test/writing/submit/${props.attempt.id}`, {
        answers: answers.value
    });
};

</script>

<template>
    <AntiCheat />
    <ExitConfirmModal />
    <AutoSubmitOverlay 
        v-if="isTimeUpSubmit"
        :attemptId="attempt.id"
        :answers="answers"
        :storageKey="`writing_test_answers_${attempt.id}`"
        section="writing"
    />
    <div class="main-container">
        <!-- Reusable Exam Header -->
        <ExamHeader 
            :timeLimitSeconds="timeLimitSeconds"
            :serverTime="serverTime"
            :attemptStartTime="attempt.start_time"
            @timeUp="handleTimeUp"
        />

        <!-- Global Part Header -->
        <div class="global-part-header">
            <div class="part-header-inner" style="background: white; padding: 16px 24px; border-radius: 8px; border: 1px solid #e0e0e0; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">
                <div class="part-title">Writing Task {{ isSingleTask ? taskNumber : activeTask }}</div>
                <div class="part-instruction">
                    You should spend about {{ Math.floor(timeLimitSeconds / 60) }} minutes on this task.
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="content-wrapper">
            <!-- Left Panel - Questions -->
            <div class="left-panel">
                <!-- Single Task Mode -->
                <template v-if="isSingleTask">
                    <div class="question-content">
                        <div class="question-prompt" v-if="singleQuestion">
                            <h4>{{ taskNumber === 1 ? 'Task' : 'Essay Task' }}</h4>
                            <div class="prompt-text" v-html="singleQuestion.content"></div>
                            <img v-if="singleQuestion.media_url" :src="singleQuestion.media_url" alt="Task Visual" class="task-image">
                        </div>
                        <div class="question-prompt" v-if="singleQuestion?.instructions">
                            <h4>Instructions</h4>
                            <div class="prompt-text" v-html="singleQuestion.instructions"></div>
                        </div>
                    </div>
                </template>

                <!-- Dual Task Mode (Legacy) -->
                <template v-else>
                    <!-- Task 1 Content -->
                    <div v-show="activeTask === 1" class="question-content">
                        <div class="question-prompt" v-if="task1Question">
                            <h4>Task</h4>
                            <div class="prompt-text" v-html="task1Question.content"></div>
                            <img v-if="task1Question.media_url" :src="task1Question.media_url" alt="Task 1 Visual" class="task-image">
                        </div>
                        <div class="question-prompt" v-if="task1Question?.instructions">
                            <h4>Instructions</h4>
                            <div class="prompt-text" v-html="task1Question.instructions"></div>
                        </div>
                    </div>

                    <!-- Task 2 Content -->
                    <div v-show="activeTask === 2" class="question-content">
                        <div class="question-prompt" v-if="task2Question">
                            <h4>Essay Task</h4>
                            <div class="prompt-text" v-html="task2Question.content"></div>
                        </div>
                        <div class="question-prompt" v-if="task2Question?.instructions">
                            <h4>Instructions</h4>
                            <div class="prompt-text" v-html="task2Question.instructions"></div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Right Panel - Writing Area -->
            <div class="right-panel">
                <div style="height: 100%; display: flex; flex-direction: column;">
                    <div class="editor-header">
                        <div class="word-count-info">
                            <div class="word-count">
                                Word count: <span class="word-count-number">{{ currentWordCount }}</span>
                            </div>
                        </div>
                        <div class="autosave-status" :class="{ 'text-green-500': saveStatus === 'Saved', 'text-red-500': saveStatus === 'Error saving', 'text-gray-500': saveStatus === 'Saving...' }">
                            <span>{{ saveStatus }}</span>
                        </div>
                    </div>
                    
                    <div class="editor-area">
                        <!-- Single Task Editor -->
                        <template v-if="isSingleTask">
                            <textarea
                                v-model="answers[singleQuestion?.id]"
                                class="editor-textarea"
                                :placeholder="taskNumber === 1 ? 'Start writing your Task 1 response here...' : 'Start writing your Task 2 essay here...'"
                                spellcheck="false"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                            ></textarea>
                        </template>

                        <!-- Dual Task Editors (Legacy) -->
                        <template v-else>
                            <textarea v-show="activeTask === 1"
                                v-model="answers[task1Question?.id]"
                                class="editor-textarea"
                                placeholder="Start writing your Task 1 response here..."
                                spellcheck="false"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                            ></textarea>

                            <textarea v-show="activeTask === 2"
                                v-model="answers[task2Question?.id]"
                                class="editor-textarea"
                                placeholder="Start writing your Task 2 essay here..."
                                spellcheck="false"
                                autocomplete="off"
                                autocorrect="off"
                                autocapitalize="off"
                            ></textarea>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <div class="nav-left">
                <!-- Task tabs only shown for dual-task mode -->
                <div v-if="!isSingleTask" class="task-nav">
                    <button type="button" class="task-btn" :class="{ 'active': activeTask === 1 }" @click="activeTask = 1">Task 1</button>
                    <button type="button" class="task-btn" :class="{ 'active': activeTask === 2 }" @click="activeTask = 2">Task 2</button>
                </div>
                <div v-else class="task-nav">
                    <span style="font-weight: 600; color: #374151; font-size: 14px;">Writing Task {{ taskNumber }}</span>
                </div>
            </div>
            <div class="nav-right">
                <button type="button" @click="showSubmitModal = true" class="submit-btn" :disabled="isSubmitting">
                    Submit Test
                </button>
            </div>
        </div>
    </div>

    <!-- Submit Modal -->
    <div class="modal-overlay" v-if="showSubmitModal">
        <div class="modal-content">
            <div class="modal-title">Ready to Submit?</div>
            <div class="modal-message">
                Please review your word count before submitting:
            </div>
            <div class="word-summary">
                <template v-if="isSingleTask">
                    <div class="word-summary-item">
                        <span><strong>Task {{ taskNumber }}:</strong></span>
                        <span>{{ currentWordCount }} words</span>
                    </div>
                </template>
                <template v-else>
                    <div class="word-summary-item">
                        <span><strong>Task 1:</strong></span>
                        <span>{{ wordCountTask1 }} words</span>
                    </div>
                    <div class="word-summary-item">
                        <span><strong>Task 2:</strong></span>
                        <span>{{ wordCountTask2 }} words</span>
                    </div>
                </template>
            </div>
            <div class="modal-message">
                Once submitted, you cannot change your answers.
            </div>
            <div class="modal-buttons">
                <button class="modal-button primary" @click="confirmSubmit" :disabled="isSubmitting">
                    {{ isSubmitting ? 'Submitting...' : 'Submit Test' }}
                </button>
                <button class="modal-button secondary" @click="showSubmitModal = false" :disabled="isSubmitting">Continue Writing</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            overflow: hidden;
        }
        
        .main-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        
        .user-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #1a1a1a;
            color: white;
            height: 50px;
            flex-shrink: 0;
            position: relative;
        }
        
        .timer-center-wrapper {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10;
        }
        
        .timer-display {
            font-size: 1.25rem;
            font-weight: 600;
            color: white;
        }
        
        .text-yellow-400 { color: #facc15; }
        .text-red-500 { color: #ef4444; }
        
        .user-info {
            display: flex;
            align-items: center;
            font-size: 14px;
        }
        
        .user-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .content-wrapper {
            flex: 1;
            display: flex;
            overflow: hidden;
            margin-bottom: 60px; /* Space for bottom nav */
        }
        
        .left-panel {
            width: 45%;
            background-color: #f8f9fa;
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .question-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        /* Global Part Header */
        .global-part-header {
            background: white;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 5%;
            z-index: 200;
            flex-shrink: 0;
        }
        
        .part-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .part-instruction {
            font-size: 14px;
            color: #4b5563;
            line-height: 1.5;
        }
        
        .question-prompt {
            background-color: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .question-prompt h4 {
            margin-top: 0;
            color: #1f2937;
            font-size: 16px;
        }
        
        .prompt-text {
            line-height: 1.6;
            color: #374151;
            font-size: 15px;
        }
        
        /* Handling rich text from backend */
        :deep(.prompt-text strong),
        :deep(.prompt-text b) {
            font-weight: 700 !important;
            color: #000000 !important;
        }

        :deep(.prompt-text em),
        :deep(.prompt-text i) {
            font-style: italic !important;
        }

        :deep(.prompt-text u) {
            text-decoration: underline !important;
        }

        :deep(.prompt-text table) {
            width: auto !important;
            max-width: 100% !important;
            border-collapse: collapse !important;
            margin: 10px 0 !important;
            font-size: 14px !important;
        }

        :deep(.prompt-text table th) {
            background-color: #f3f4f6 !important;
            padding: 8px 12px !important;
            font-weight: 700 !important;
            border: 1px solid #000000 !important;
            color: #000000 !important;
        }

        :deep(.prompt-text table td) {
            padding: 6px 12px !important;
            border: 1px solid #000000 !important;
            color: #1f2937 !important;
        }

        :deep(.prompt-text ul),
        :deep(.prompt-text ol) {
            margin: 10px 0 10px 20px !important;
            padding-left: 20px !important;
        }

        :deep(.prompt-text li) {
            margin-bottom: 5px !important;
            line-height: 1.6 !important;
        }

        .task-image {
            width: 100%;
            max-width: 500px;
            height: auto;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            margin-top: 15px;
        }
        
        .right-panel {
            flex: 1;
            background-color: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .editor-header {
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-shrink: 0;
        }
        
        .word-count-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .word-count {
            font-weight: 500;
            color: #374151;
            font-size: 16px;
        }
        
        .word-count-number {
            font-weight: bold;
            color: #111827;
        }
        
        .autosave-status {
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .editor-area {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }
        
        .editor-textarea {
            width: 100%;
            height: 100%;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            font-size: 16px;
            line-height: 1.8;
            font-family: 'Times New Roman', Times, serif;
            resize: none;
            outline: none;
            transition: border-color 0.3s;
            box-sizing: border-box;
        }
        
        .editor-textarea:focus {
            border-color: #111827;
            box-shadow: 0 0 0 3px rgba(17, 24, 39, 0.08);
        }
        
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
            border-top: 1px solid #e5e7eb;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
            height: 60px;
            box-sizing: border-box;
        }
        
        .nav-left {
            display: flex;
            align-items: center;
            gap: 20px;
            flex: 1;
        }
        
        .task-nav {
            display: flex;
            gap: 10px;
            flex: 1;
        }
        
        .task-btn {
            flex: 1;
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            color: #374151;
            font-size: 14px;
            cursor: pointer;
            border-radius: 4px;
            transition: all 0.2s;
        }
        
        .task-btn.active {
            background-color: #111827;
            border-color: #111827;
            color: white;
        }
        
        .task-btn:hover:not(.active) {
            background-color: #f3f4f6;
        }
        
        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 24px;
        }
        
        .btn-secondary {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            border-color: #374151;
            color: #374151;
            background: #f3f4f6;
        }
        
        .submit-btn {
            background-color: #dc143c;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .submit-btn:hover {
            background-color: #b91032;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(220, 20, 60, 0.3);
        }
        
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.75);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background-color: white;
            padding: 32px;
            border-radius: 12px;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 16px;
            color: #1f2937;
        }
        
        .modal-message {
            font-size: 16px;
            margin-bottom: 24px;
            line-height: 1.6;
            color: #4b5563;
        }
        
        .word-summary {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        
        .word-summary-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .word-summary-item:last-child {
            border-bottom: none;
        }
        
        .modal-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 24px;
        }
        
        .modal-button {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .modal-button.primary {
            background-color: #10b981;
            color: white;
        }
        
        .modal-button.primary:hover {
            background-color: #059669;
        }
        
        .modal-button.secondary {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        
        .modal-button.secondary:hover {
            background-color: #d1d5db;
        }
        
        /* Smooth scrollbar for question panel */
        .question-content::-webkit-scrollbar {
            width: 8px;
        }
        
        .question-content::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .question-content::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .question-content::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        @media (max-width: 1024px) {
            .left-panel {
                width: 50%;
            }
        }
        
        @media (max-width: 768px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .left-panel {
                width: 100%;
                height: 40%;
                border-right: none;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .right-panel {
                height: 60%;
            }
        }

</style>
