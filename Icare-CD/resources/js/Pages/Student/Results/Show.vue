<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StudentDashboardLayout from '@/Layouts/StudentDashboardLayout.vue';
import ResultHeroCard from './Components/ResultHeroCard.vue';
import NoAnswersAlert from './Components/NoAnswersAlert.vue';
import QuestionAnalysis from './Components/QuestionAnalysis/QuestionAnalysis.vue';
import AiEvaluationModal from './Components/AiEvaluationModal.vue';
import AudioVisualizer from './Components/AudioVisualizer.vue';

const props = defineProps({
    attempt: {
        type: Object,
        required: true
    },
    testSet: {
        type: Object,
        required: true
    },
    sectionName: {
        type: String,
        required: true
    },
    passages: {
        type: Array,
        default: () => []
    },
    correctAnswers: {
        type: Number,
        default: 0
    },
    totalQuestions: {
        type: Number,
        default: 0
    },
    answeredQuestions: {
        type: Number,
        default: 0
    },
    accuracy: {
        type: Number,
        default: 0
    },
    bandScore: {
        type: [Number, String],
        default: 0
    },
    performanceLevel: {
        type: String,
        default: ''
    },
    scoreMessage: {
        type: String,
        default: ''
    },
    formattedQuestions: {
        type: Array,
        default: () => []
    },
    humanEvaluationRequest: {
        type: Object,
        default: null
    },
    studentAnswers: {
        type: Array,
        default: () => []
    },
    canRetake: {
        type: Boolean,
        default: false
    },
    isLatestAttempt: {
        type: Boolean,
        default: false
    },
    hasAiFeature: {
        type: Boolean,
        default: false
    },
    hasHumanEvaluationFeature: {
        type: Boolean,
        default: false
    },
    aiEvaluated: {
        type: Boolean,
        default: false
    },
    aiBandScore: {
        type: [Number, String, null],
        default: null
    },
    completionRate: {
        type: Number,
        default: 0
    },
    aiEvaluation: {
        type: Object,
        default: null
    }
});

// AI Evaluation Modal
const showAiModal = ref(false);

const formatDuration = (start, end) => {
    const s = start || props.attempt.created_at;
    const e = end || props.attempt.updated_at;
    if (!s || !e) return 'N/A';
    const startTime = new Date(s);
    const endTime = new Date(e);
    const diffMs = endTime - startTime;
    if (diffMs <= 0) return 'N/A';
    const diffMins = Math.round(diffMs / 60000);

    if (diffMins < 1) {
        return 'Less than 1 min';
    } else if (diffMins < 60) {
        return `${diffMins} minutes`;
    } else {
        const hours = Math.floor(diffMins / 60);
        const mins = diffMins % 60;
        return `${hours}h ${mins}m`;
    }
};

const handleRequestEvaluation = () => {
    if (confirm('Are you sure you want to request an expert evaluation?')) {
        window.location.href = `/student/human-evaluation/${props.attempt.id}/teachers`;
    }
};

// AI Evaluation
const aiEvalLoading = ref(false);
const aiEvalError = ref('');

const bandScoreRange = (score) => {
    if (!score) return 'N/A';
    const s = parseFloat(score);
    const lower = Math.floor(s * 2) / 2;
    const upper = Math.ceil(s * 2) / 2;
    if (lower === upper) return lower.toFixed(1);
    return `${lower.toFixed(1)}-${upper.toFixed(1)}`;
};

const parseJsonSafe = async (res) => {
    const text = await res.text();
    try {
        return JSON.parse(text);
    } catch {
        // Non-JSON response (HTML from 419/302/500 or redirect followed)
        if (res.status === 419) throw new Error('Session expired. Please refresh the page and try again.');
        if (res.status === 401) throw new Error('Please login again and retry.');
        if (text.includes('<!DOCTYPE') || text.includes('<html')) {
            // Followed a redirect to an HTML page (middleware redirect or session expiry)
            throw new Error('Session expired or permission denied. Please refresh the page.');
        }
        throw new Error(`Server error (${res.status}). Please try again.`);
    }
};

const checkResponse = async (res, fallbackError = 'Request failed') => {
    const data = await parseJsonSafe(res);
    // Check HTTP status
    if (!res.ok) {
        const errMsg = data?.error || data?.debug || `${fallbackError} (${res.status})`;
        throw new Error(errMsg);
    }
    // Check success flag (some endpoints return 200 with success:false)
    if (data.success === false) {
        throw new Error(data.error || data.debug || fallbackError);
    }
    return data;
};

const startAIEvaluation = async () => {
    aiEvalLoading.value = true;
    aiEvalError.value = '';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page.');
        }

        const headers = {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        };

        const fetchOpts = (body) => ({
            method: 'POST',
            headers,
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });

        if (props.sectionName === 'speaking') {
            // Progressive speaking evaluation
            const statusRes = await fetch('/ai/evaluate/speaking/status', fetchOpts({ attempt_id: props.attempt.id }));
            const statusData = await checkResponse(statusRes, 'Failed to check evaluation status');

            const recordings = statusData.recordings || [];
            const pendingRecordings = recordings.filter(r => !r.evaluated);

            for (const recording of pendingRecordings) {
                const evalRes = await fetch('/ai/evaluate/speaking/single', fetchOpts({ answer_id: recording.answer_id }));
                if (!evalRes.ok) {
                    const errData = await parseJsonSafe(evalRes);
                    console.warn('Failed to evaluate recording:', errData?.error);
                }
            }

            const finalRes = await fetch('/ai/evaluate/speaking/finalize', fetchOpts({ attempt_id: props.attempt.id }));
            await checkResponse(finalRes, 'Failed to finalize evaluation');

        } else {
            // Writing evaluation
            const res = await fetch('/ai/evaluate/writing', fetchOpts({ attempt_id: props.attempt.id }));
            await checkResponse(res, 'Failed to evaluate writing');
        }

        // Evaluation done → reload page to get fresh data, then open modal
        aiEvalLoading.value = false;
        router.reload({
            only: ['aiEvaluated', 'aiBandScore', 'aiEvaluation'],
            onSuccess: () => {
                showAiModal.value = true;
            }
        });
    } catch (error) {
        aiEvalError.value = error.message || 'An error occurred. Please try again.';
        aiEvalLoading.value = false;
    }
};

const handleRetake = () => {
    router.post(`/student/test/results/${props.attempt.id}/retake`);
};

// Writing task tabs
const activeTask = ref(1);
const activeTaskAnswer = computed(() => {
    return props.studentAnswers.find(a => a.task_number === activeTask.value) || props.studentAnswers[0] || null;
});

// Speaking part tabs — group by part_number
const activePart = ref(1);
const speakingParts = computed(() => {
    if (props.sectionName !== 'speaking' || !props.studentAnswers?.length) return {};
    const groups = {};
    props.studentAnswers.forEach(a => {
        const pn = a.part_number || 1;
        if (!groups[pn]) groups[pn] = [];
        groups[pn].push(a);
    });
    return groups;
});
const speakingPartNumbers = computed(() => Object.keys(speakingParts.value).map(Number).sort());
const activePartAnswers = computed(() => speakingParts.value[activePart.value] || []);
</script>

<template>
    <Head :title="`${testSet.title} - Results`" />

    <StudentDashboardLayout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-8 px-4 sm:px-0">
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    Test Result Details
                </h2>
                <a
                    href="/student/test/results"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                >
                    <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Results
                </a>
            </div>

            <!-- Top Hero Card -->
            <ResultHeroCard
                :testSet="testSet"
                :attempt="attempt"
                :bandScore="bandScore"
                :sectionName="sectionName"
                :aiEvaluated="aiEvaluated"
                :aiBandScore="aiBandScore"
                :duration="formatDuration(attempt.started_at, attempt.completed_at)"
            />

            <!-- Evaluation Buttons (Writing/Speaking) — Compact -->
            <div v-if="['writing', 'speaking'].includes(sectionName)" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <!-- AI Evaluation Button -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                         :class="aiEvaluated ? 'bg-emerald-100' : 'bg-gradient-to-br from-[#C8102E]/10 to-[#C8102E]/5'">
                        <svg v-if="aiEvaluated" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-else class="w-6 h-6 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm">AI Evaluation</h4>
                        <!-- Evaluated: show score -->
                        <p v-if="aiEvaluated" class="text-xs text-emerald-600 font-semibold">Band {{ aiBandScore ? bandScoreRange(aiBandScore) : 'N/A' }}</p>
                        <!-- Not evaluated -->
                        <p v-else-if="completionRate > 0" class="text-xs text-gray-500">Instant AI feedback</p>
                        <p v-else class="text-xs text-gray-400">Complete test first</p>
                        <!-- Error message -->
                        <p v-if="aiEvalError" class="text-xs text-red-500 mt-1">{{ aiEvalError }}</p>
                    </div>
                    <!-- Evaluated → View Modal -->
                    <button v-if="aiEvaluated"
                        @click="showAiModal = true"
                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-all shrink-0">
                        View
                    </button>
                    <!-- Not evaluated → Get -->
                    <button v-else-if="completionRate > 0 && hasAiFeature"
                        @click="startAIEvaluation"
                        :disabled="aiEvalLoading"
                        class="px-4 py-2 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-lg text-xs font-semibold hover:shadow-md transition-all disabled:opacity-50 shrink-0">
                        <svg v-if="aiEvalLoading" class="animate-spin h-3.5 w-3.5 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ aiEvalLoading ? 'Evaluating...' : 'Get Now' }}
                    </button>
                    <!-- No AI feature → locked -->
                    <span v-else-if="!hasAiFeature && completionRate > 0" class="text-[10px] font-bold bg-gray-100 text-gray-400 px-2.5 py-1 rounded-full uppercase shrink-0">Premium</span>
                </div>

                <!-- Expert Evaluation Button -->
                <div v-if="hasHumanEvaluationFeature || humanEvaluationRequest" class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                         :class="humanEvaluationRequest?.status === 'completed' ? 'bg-emerald-100' : humanEvaluationRequest?.status === 'pending' ? 'bg-amber-100' : 'bg-violet-50'">
                        <svg v-if="humanEvaluationRequest?.status === 'completed'" class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-else-if="humanEvaluationRequest?.status === 'pending'" class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <svg v-else class="w-6 h-6 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-gray-900 text-sm">Expert Evaluation</h4>
                        <!-- Completed: show band -->
                        <p v-if="humanEvaluationRequest?.status === 'completed' && humanEvaluationRequest?.human_evaluation" class="text-xs text-emerald-600 font-semibold">
                            Band {{ humanEvaluationRequest.human_evaluation.band_score }}
                        </p>
                        <!-- Pending -->
                        <p v-else-if="humanEvaluationRequest?.status === 'pending'" class="text-xs text-amber-600 font-medium">Under review</p>
                        <!-- Not requested -->
                        <p v-else class="text-xs text-gray-500">By IELTS examiner</p>
                    </div>
                    <!-- Completed → View -->
                    <a v-if="humanEvaluationRequest?.status === 'completed'" :href="`/student/human-evaluation/${attempt.id}/result`"
                       class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-all shrink-0">
                        View
                    </a>
                    <!-- Pending → badge -->
                    <span v-else-if="humanEvaluationRequest?.status === 'pending'" class="inline-flex items-center px-3 py-1.5 rounded-full text-[10px] font-bold bg-amber-50 border border-amber-200 text-amber-700 shrink-0">
                        <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                        Pending
                    </span>
                    <!-- Not requested: offline → request, online → choose teacher -->
                    <button v-else-if="$page.props.dashboardNav?.isOfflineStudent"
                        @click="handleRequestEvaluation"
                        class="px-4 py-2 bg-violet-600 text-white rounded-lg text-xs font-semibold hover:bg-violet-700 transition-all shrink-0">
                        Request
                    </button>
                    <a v-else :href="`/student/human-evaluation/${attempt.id}/teachers`"
                       class="px-4 py-2 bg-gray-900 text-white rounded-lg text-xs font-semibold hover:bg-black transition-all shrink-0">
                        Choose Teacher
                    </a>
                </div>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                <!-- MAIN CONTENT COLUMN (2/3 width) -->
                <div class="w-full lg:w-2/3">

                    <!-- Listening/Reading -->
                    <template v-if="['listening', 'reading'].includes(sectionName)">
                        <NoAnswersAlert v-if="answeredQuestions === 0 && totalQuestions > 0">
                            <template #actions>
                                <div class="mt-4">
                                    <a :href="`/student/test/${testSet.section?.name || 'reading'}/confirm/${attempt.test_set_id}`" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:border-red-700 focus:ring focus:ring-red-200 transition">
                                        Retake Test
                                    </a>
                                </div>
                            </template>
                        </NoAnswersAlert>

                        <QuestionAnalysis :questions="formattedQuestions" :attemptId="attempt.id" />
                    </template>

                    <!-- Writing/Speaking -->
                    <template v-else-if="['writing', 'speaking'].includes(sectionName)">
                        <!-- Writing: Tabbed Question + Submission -->
                        <div v-if="sectionName === 'writing' && studentAnswers.length > 0" class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                            <!-- Header + Task Tabs -->
                            <div class="border-b border-gray-200">
                                <div class="px-6 pt-5 pb-0">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center shadow-lg shadow-[#C8102E]/20">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">My Submission</h3>
                                            <p class="text-xs text-gray-500">Review your writing tasks</p>
                                        </div>
                                    </div>
                                    <!-- Task Tabs -->
                                    <div class="flex">
                                        <button
                                            v-for="answer in studentAnswers"
                                            :key="answer.task_number"
                                            @click="activeTask = answer.task_number"
                                            :class="[
                                                'flex-1 py-3 text-sm font-semibold text-center rounded-t-lg transition-all duration-200 border border-b-0',
                                                activeTask === answer.task_number
                                                    ? 'bg-white text-[#C8102E] border-gray-200 -mb-px z-10'
                                                    : 'bg-gray-50 text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-100'
                                            ]"
                                        >
                                            Task {{ answer.task_number }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Task Content -->
                            <div v-if="activeTaskAnswer" class="p-6">
                                <!-- Question / Prompt -->
                                <div v-if="activeTaskAnswer.question_text || activeTaskAnswer.question_image" class="mb-5">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-1 h-5 bg-[#C8102E] rounded-full"></div>
                                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Question</h4>
                                    </div>
                                    <div class="bg-amber-50/60 border border-amber-200/60 rounded-xl p-5">
                                        <!-- Question Image -->
                                        <div v-if="activeTaskAnswer.question_image" class="mb-4">
                                            <img :src="activeTaskAnswer.question_image" alt="Question image" class="max-w-full rounded-lg border border-amber-200/40 shadow-sm" />
                                        </div>
                                        <!-- Question Text -->
                                        <div v-if="activeTaskAnswer.question_text" class="prose prose-sm max-w-none text-gray-800 leading-relaxed" v-html="activeTaskAnswer.question_text"></div>
                                    </div>
                                </div>

                                <!-- Student's Answer -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                                        <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">My Answer</h4>
                                    </div>
                                    <div v-if="activeTaskAnswer.answer_text" class="bg-white border border-gray-200 rounded-xl p-5 prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap leading-relaxed">{{ activeTaskAnswer.answer_text }}</div>
                                    <div v-else class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                                        <p class="text-gray-400 italic">No answer provided for this task.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Speaking: Tabbed Submission Card (grouped by Part) -->
                        <div v-if="sectionName === 'speaking' && studentAnswers.length > 0" class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                            <!-- Header + Part Tabs -->
                            <div class="border-b border-gray-200">
                                <div class="px-6 pt-5 pb-0">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center shadow-lg shadow-[#C8102E]/20">
                                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900">My Submission</h3>
                                            <p class="text-xs text-gray-500">Review your speaking recordings</p>
                                        </div>
                                    </div>
                                    <!-- Part Tabs -->
                                    <div class="flex">
                                        <button
                                            v-for="pn in speakingPartNumbers"
                                            :key="pn"
                                            @click="activePart = pn"
                                            :class="[
                                                'flex-1 py-3 text-sm font-semibold text-center rounded-t-lg transition-all duration-200 border border-b-0',
                                                activePart === pn
                                                    ? 'bg-white text-[#C8102E] border-gray-200 -mb-px z-10'
                                                    : 'bg-gray-50 text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-100'
                                            ]"
                                        >
                                            Part {{ pn }}
                                            <span class="ml-1 text-[10px] font-normal text-gray-400">({{ speakingParts[pn]?.length || 0 }})</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Active Part Content — all questions in this part -->
                            <div v-if="activePartAnswers.length" class="p-6 space-y-5">
                                <div v-for="(answer, idx) in activePartAnswers" :key="answer.task_number"
                                     :class="['rounded-xl border border-gray-100 overflow-hidden', idx > 0 ? '' : '']">

                                    <!-- Question header bar -->
                                    <div class="px-4 py-2.5 bg-gray-50/70 border-b border-gray-100 flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-full bg-[#C8102E]/10 flex items-center justify-center shrink-0">
                                            <span class="text-[10px] font-bold text-[#C8102E]">{{ idx + 1 }}</span>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600">
                                            {{ answer.question_title || ('Question ' + (idx + 1)) }}
                                        </span>
                                    </div>

                                    <div class="p-4 space-y-3">
                                        <!-- Question Text -->
                                        <div v-if="answer.question_text || answer.question_image">
                                            <div class="bg-amber-50/50 border border-amber-200/50 rounded-lg p-4">
                                                <div v-if="answer.question_image" class="mb-3">
                                                    <img :src="answer.question_image" alt="Question image" class="max-w-full rounded-lg border border-amber-200/40 shadow-sm" />
                                                </div>
                                                <div v-if="answer.question_text" class="prose prose-sm max-w-none text-gray-700 leading-relaxed text-[13px]" v-html="answer.question_text"></div>
                                            </div>
                                        </div>

                                        <!-- Recording -->
                                        <AudioVisualizer
                                            v-if="answer.recording_url"
                                            :src="answer.recording_url"
                                            :mimeType="answer.recording_mime_type || 'audio/webm'"
                                            :label="'Q' + (idx + 1)"
                                        />
                                        <p v-else class="text-gray-400 italic text-sm text-center py-3">No recording available.</p>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="p-8 text-center">
                                <p class="text-gray-400 italic text-sm">No recordings found for this part.</p>
                            </div>
                        </div>

                    </template>
                </div>

                <!-- SIDEBAR COLUMN (1/3 width) -->
                <div class="w-full lg:w-1/3 space-y-6">
                    <!-- Performance Feedback (Listening/Reading only) -->
                    <div v-if="scoreMessage && ['listening', 'reading'].includes(sectionName)" class="bg-gradient-to-br from-[#C8102E] to-[#8B0000] rounded-2xl shadow-lg border border-[#C8102E]/50 p-6 text-white relative overflow-hidden">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                        <div class="absolute bottom-0 left-0 -mb-8 -ml-8 w-32 h-32 bg-[#C8102E] opacity-30 rounded-full blur-2xl"></div>

                        <div class="relative z-10">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-white/70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                                <h3 class="font-bold text-xs text-white/70 uppercase tracking-wider">Performance Feedback</h3>
                            </div>
                            <p class="text-[15px] font-medium leading-relaxed">{{ scoreMessage }}</p>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            <button
                                v-if="canRetake && isLatestAttempt"
                                @click="handleRetake"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-[#C8102E] to-[#A00E27] text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Retake Test
                            </button>
                            <a :href="`/student/test/${sectionName}`"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                All {{ sectionName.charAt(0).toUpperCase() + sectionName.slice(1) }} Tests
                            </a>
                            <a href="/student/test/results"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition-all">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                View All Results
                            </a>
                        </div>
                    </div>

                    <!-- Info Card - L/R specific -->
                    <div v-if="['listening', 'reading'].includes(sectionName)" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Understanding Your Score
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Each correct answer awards 1 mark.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Scores out of 40 are converted to the IELTS 9-band scale.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <span>Hover or click 'Explain Answer' to get AI-powered detailed explanations for challenging questions.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Info Card - W/S specific -->
                    <div v-if="['writing', 'speaking'].includes(sectionName)" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            About Evaluation
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-[#C8102E] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                                <span>AI Evaluation provides instant band score prediction with detailed feedback.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-violet-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                                <span>Expert Evaluation is reviewed by experienced IELTS teachers for detailed feedback.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ sectionName === 'writing' ? 'Writing is scored on Task Achievement, Coherence, Vocabulary, and Grammar.' : 'Speaking is scored on Fluency, Vocabulary, Grammar, and Pronunciation.' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- AI Evaluation Modal -->
    <AiEvaluationModal
        :show="showAiModal"
        :evaluation="aiEvaluation"
        :sectionName="sectionName"
        :studentAnswers="studentAnswers"
        @close="showAiModal = false"
    />
    </StudentDashboardLayout>
</template>
