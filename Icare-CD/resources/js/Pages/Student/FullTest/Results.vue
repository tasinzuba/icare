<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import StudentDashboardLayout from '@/Layouts/StudentDashboardLayout.vue';
import QuestionAnalysis from '../Results/Components/QuestionAnalysis/QuestionAnalysis.vue';
import ScoreBreakdown from '../Results/Components/ScoreBreakdown.vue';
import AiEvaluationModal from '../Results/Components/AiEvaluationModal.vue';
import AudioVisualizer from '../Results/Components/AudioVisualizer.vue';
import NoAnswersAlert from '../Results/Components/NoAnswersAlert.vue';
import ResultHeroCard from '../Results/Components/ResultHeroCard.vue';

const props = defineProps({
    fullTestAttempt: { type: Object, required: true },
    fullTest: { type: Object, required: true },
    availableSections: { type: Array, required: true },
    sectionsData: { type: Object, required: true },
    duration: { type: String, default: '' },
    completedSectionsCount: { type: Number, default: 0 },
    effectiveScores: { type: Object, default: () => ({}) },
    scoreTypes: { type: Object, default: () => ({}) },
    displayOverallBand: { type: Number, default: null },
    hasAnyAiScore: { type: Boolean, default: false },
    bandDescription: { type: String, default: null },
    strongest: { type: String, default: null },
    weakest: { type: String, default: null },
    isOfflineStudent: { type: Boolean, default: false },
    canUseAI: { type: Boolean, default: false },
    canUseHuman: { type: Boolean, default: false },
    evaluationType: { type: String, default: 'ai' },
    hasHumanEvaluationFeature: { type: Boolean, default: false },
});

// ── Section styles ──
const sectionStyles = {
    listening: { icon: 'fa-headphones', color: 'blue', gradient: 'from-blue-500 to-blue-600', bgLight: 'bg-blue-50', borderLight: 'border-blue-100', textColor: 'text-blue-600' },
    reading: { icon: 'fa-book-open', color: 'emerald', gradient: 'from-emerald-500 to-emerald-600', bgLight: 'bg-emerald-50', borderLight: 'border-emerald-100', textColor: 'text-emerald-600' },
    writing: { icon: 'fa-pen-fancy', color: 'violet', gradient: 'from-violet-500 to-violet-600', bgLight: 'bg-violet-50', borderLight: 'border-violet-100', textColor: 'text-violet-600' },
    speaking: { icon: 'fa-microphone', color: 'orange', gradient: 'from-orange-500 to-orange-600', bgLight: 'bg-orange-50', borderLight: 'border-orange-100', textColor: 'text-orange-600' },
};

// ── Helpers ──
const bandScoreRange = (score) => {
    if (!score && score !== 0) return 'N/A';
    const s = parseFloat(score);
    const lower = Math.floor(s * 2) / 2;
    const upper = Math.ceil(s * 2) / 2;
    if (lower === upper) return lower.toFixed(1);
    return `${lower.toFixed(1)}-${upper.toFixed(1)}`;
};

const parseJsonSafe = async (res) => {
    const text = await res.text();
    try { return JSON.parse(text); }
    catch {
        if (res.status === 419) throw new Error('Session expired. Please refresh the page and try again.');
        if (res.status === 401) throw new Error('Please login again and retry.');
        if (text.includes('<!DOCTYPE') || text.includes('<html')) throw new Error('Session expired or permission denied. Please refresh the page.');
        throw new Error(`Server error (${res.status}). Please try again.`);
    }
};

const checkResponse = async (res, fallbackError = 'Request failed') => {
    const data = await parseJsonSafe(res);
    if (!res.ok) throw new Error(data?.error || data?.debug || `${fallbackError} (${res.status})`);
    if (data.success === false) throw new Error(data.error || data.debug || fallbackError);
    return data;
};

// ── AI Evaluation (per-section) ──
const activeAiSection = ref(null);
const showAiModal = ref(false);
const aiEvalLoading = ref({});
const aiEvalError = ref({});

const startAIEvaluation = async (sectionKey) => {
    aiEvalLoading.value = { ...aiEvalLoading.value, [sectionKey]: true };
    aiEvalError.value = { ...aiEvalError.value, [sectionKey]: '' };

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) throw new Error('CSRF token not found. Please refresh the page.');

        const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' };
        const fetchOpts = (body) => ({ method: 'POST', headers, credentials: 'same-origin', body: JSON.stringify(body) });
        const attemptId = props.sectionsData[sectionKey]?.attemptId;

        if (sectionKey === 'speaking') {
            const statusRes = await fetch('/ai/evaluate/speaking/status', fetchOpts({ attempt_id: attemptId }));
            const statusData = await checkResponse(statusRes, 'Failed to check evaluation status');
            const pendingRecordings = (statusData.recordings || []).filter(r => !r.evaluated);
            for (const recording of pendingRecordings) {
                const evalRes = await fetch('/ai/evaluate/speaking/single', fetchOpts({ answer_id: recording.answer_id }));
                if (!evalRes.ok) console.warn('Failed to evaluate recording:', (await parseJsonSafe(evalRes))?.error);
            }
            const finalRes = await fetch('/ai/evaluate/speaking/finalize', fetchOpts({ attempt_id: attemptId }));
            await checkResponse(finalRes, 'Failed to finalize evaluation');
        } else {
            const res = await fetch('/ai/evaluate/writing', fetchOpts({ attempt_id: attemptId }));
            await checkResponse(res, 'Failed to evaluate writing');
        }

        aiEvalLoading.value = { ...aiEvalLoading.value, [sectionKey]: false };
        // Reload page to get fresh data, then open modal
        activeAiSection.value = sectionKey;
        router.reload({
            onSuccess: () => { showAiModal.value = true; }
        });
    } catch (error) {
        aiEvalError.value = { ...aiEvalError.value, [sectionKey]: error.message || 'An error occurred.' };
        aiEvalLoading.value = { ...aiEvalLoading.value, [sectionKey]: false };
    }
};

const openAiModal = (sectionKey) => {
    activeAiSection.value = sectionKey;
    showAiModal.value = true;
};

const activeAiEvaluation = computed(() => {
    if (!activeAiSection.value) return null;
    return props.sectionsData[activeAiSection.value]?.aiEvaluation || null;
});

const activeAiStudentAnswers = computed(() => {
    if (!activeAiSection.value) return [];
    return props.sectionsData[activeAiSection.value]?.studentAnswers || [];
});

// ── Writing tabs (per section) ──
const activeWritingTask = ref(1);
const writingData = computed(() => props.sectionsData.writing || null);
const activeWritingAnswer = computed(() => {
    if (!writingData.value?.studentAnswers) return null;
    return writingData.value.studentAnswers.find(a => a.task_number === activeWritingTask.value) || writingData.value.studentAnswers[0] || null;
});

// ── Speaking tabs (per section) ──
const activeSpeakingPart = ref(1);
const speakingData = computed(() => props.sectionsData.speaking || null);
const speakingParts = computed(() => {
    if (!speakingData.value?.studentAnswers?.length) return {};
    const groups = {};
    speakingData.value.studentAnswers.forEach(a => {
        const pn = a.part_number || 1;
        if (!groups[pn]) groups[pn] = [];
        groups[pn].push(a);
    });
    return groups;
});
const speakingPartNumbers = computed(() => Object.keys(speakingParts.value).map(Number).sort());
const activeSpeakingAnswers = computed(() => speakingParts.value[activeSpeakingPart.value] || []);

// ── Human evaluation request handler ──
const handleRequestEvaluation = () => {
    window.location.href = `/student/test/full-test/attempt/${props.fullTestAttempt.id}/request-evaluation`;
};

// ── Progress bar percentage ──
const bandProgressPct = computed(() => {
    if (!props.displayOverallBand) return 0;
    return Math.min(100, (props.displayOverallBand / 9) * 100);
});
</script>

<template>
    <Head :title="`${fullTest.title} - Full Test Results`" />

    <StudentDashboardLayout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <a href="/student/test/full-test"
                   class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-gray-900 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Back to Full Tests
                </a>
            </div>

            <!-- ═══ HERO CARD with Section Scores ═══ -->
            <ResultHeroCard
                :isFullTest="true"
                :attempt="fullTestAttempt"
                :title="fullTest.title"
                :bandScore="displayOverallBand"
                :duration="duration"
                :hasAiScore="hasAnyAiScore && !fullTestAttempt.overall_band_score"
                :completedSections="completedSectionsCount"
                :totalSections="availableSections.length"
            >
                <!-- Section Scores inside hero card -->
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div v-for="sk in availableSections" :key="sk"
                             class="text-center p-4 rounded-xl border"
                             :class="[sectionStyles[sk]?.bgLight, sectionStyles[sk]?.borderLight]">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center mx-auto mb-2 shadow-sm bg-gradient-to-br"
                                 :class="sectionStyles[sk]?.gradient">
                                <i class="fas text-white text-sm" :class="sectionStyles[sk]?.icon"></i>
                            </div>
                            <p class="text-xs font-medium mb-1" :class="sectionStyles[sk]?.textColor">{{ sk.charAt(0).toUpperCase() + sk.slice(1) }}</p>
                            <template v-if="effectiveScores[sk] !== null && effectiveScores[sk] !== undefined">
                                <p class="text-2xl font-black text-gray-900">{{ parseFloat(effectiveScores[sk]).toFixed(1) }}</p>
                                <span v-if="scoreTypes[sk] === 'ai'" class="inline-flex items-center px-2 py-0.5 bg-blue-100 text-blue-700 text-[10px] font-semibold rounded-full mt-1">
                                    <i class="fas fa-robot mr-1 text-[8px]"></i>AI
                                </span>
                            </template>
                            <p v-else class="text-sm font-semibold text-amber-500">Pending</p>
                        </div>
                    </div>

                    <!-- Band Progress Bar -->
                    <div v-if="displayOverallBand" class="mt-5 pt-4 border-t border-gray-100">
                        <div class="flex justify-between mb-2">
                            <span v-for="band in [1,2,3,4,5,6,7,8,9]" :key="band"
                                  class="text-xs font-medium"
                                  :class="displayOverallBand >= band ? 'text-[#C8102E]' : 'text-gray-300'">{{ band }}</span>
                        </div>
                        <div class="h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-[#C8102E] to-[#A00E27] rounded-full transition-all duration-1000"
                                 :style="{ width: bandProgressPct + '%' }"></div>
                        </div>
                        <p v-if="bandDescription" class="text-center text-sm text-gray-500 mt-3">
                            <i class="fas fa-award text-[#C8102E] mr-1"></i>{{ bandDescription }}
                        </p>
                    </div>
                </div>
            </ResultHeroCard>

            <!-- ═══ MAIN CONTENT GRID ═══ -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- LEFT (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- ── Performance Summary ── -->
                    <div v-if="strongest && weakest" class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                        <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                <i class="fas fa-lightbulb text-[#C8102E]"></i> Performance Summary
                            </h3>
                        </div>
                        <div class="p-5">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-arrow-up text-emerald-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-emerald-600 font-medium">Strongest</p>
                                            <p class="font-bold text-gray-900 capitalize">{{ strongest }}</p>
                                            <div class="flex items-center gap-1">
                                                <p class="text-lg font-black text-emerald-600">{{ parseFloat(effectiveScores[strongest]).toFixed(1) }}</p>
                                                <span v-if="scoreTypes[strongest] === 'ai'" class="text-[9px] text-blue-600"><i class="fas fa-robot"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-bullseye text-amber-600"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-amber-600 font-medium">Focus Area</p>
                                            <p class="font-bold text-gray-900 capitalize">{{ weakest }}</p>
                                            <div class="flex items-center gap-1">
                                                <p class="text-lg font-black text-amber-600">{{ parseFloat(effectiveScores[weakest]).toFixed(1) }}</p>
                                                <span v-if="scoreTypes[weakest] === 'ai'" class="text-[9px] text-blue-600"><i class="fas fa-robot"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ═══ SECTION-BY-SECTION ANALYSIS ═══ -->
                    <template v-for="sectionKey in availableSections" :key="sectionKey">
                        <template v-if="sectionsData[sectionKey]?.status === 'completed'">

                            <!-- ── Listening / Reading: ScoreBreakdown + QuestionAnalysis ── -->
                            <template v-if="['listening', 'reading'].includes(sectionKey)">
                                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                    <div class="p-5 border-b border-gray-100 bg-gradient-to-r"
                                         :class="[sectionStyles[sectionKey]?.bgLight, 'to-white']">
                                        <div class="flex items-center justify-between">
                                            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                                <i class="fas" :class="[sectionStyles[sectionKey]?.icon, sectionStyles[sectionKey]?.textColor]"></i>
                                                {{ sectionKey.charAt(0).toUpperCase() + sectionKey.slice(1) }} - Question Analysis
                                            </h3>
                                            <div class="flex items-center gap-4 text-sm">
                                                <span :class="sectionStyles[sectionKey]?.textColor" class="font-semibold">{{ sectionsData[sectionKey].totalQuestions }} Questions</span>
                                                <span v-if="sectionsData[sectionKey].bandScore"
                                                      class="px-3 py-1 rounded-full font-bold"
                                                      :class="[sectionStyles[sectionKey]?.bgLight, sectionStyles[sectionKey]?.textColor]">
                                                    Band: {{ bandScoreRange(sectionsData[sectionKey].bandScore) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-5">
                                        <!-- Question Analysis (reuse component) -->
                                        <QuestionAnalysis
                                            :questions="sectionsData[sectionKey].formattedQuestions"
                                            :attemptId="sectionsData[sectionKey].attemptId"
                                        />
                                    </div>
                                </div>
                            </template>

                            <!-- ── Writing: Tabbed Submission ── -->
                            <template v-if="sectionKey === 'writing' && sectionsData.writing?.studentAnswers?.length">
                                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                    <div class="border-b border-gray-200">
                                        <div class="p-5 pb-0">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                                    <i class="fas fa-pen-fancy text-violet-600"></i>
                                                    Writing - Submission
                                                </h3>
                                                <span v-if="sectionsData.writing.bandScore"
                                                      class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full font-bold text-sm">
                                                    Band: {{ bandScoreRange(sectionsData.writing.bandScore) }}
                                                </span>
                                                <span v-else-if="sectionsData.writing.aiBandScore"
                                                      class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold text-sm flex items-center gap-1">
                                                    <i class="fas fa-robot text-[10px]"></i>
                                                    Band: {{ bandScoreRange(sectionsData.writing.aiBandScore) }}
                                                </span>
                                                <span v-else class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full font-semibold text-xs">
                                                    Pending Evaluation
                                                </span>
                                            </div>
                                            <!-- Task Tabs -->
                                            <div class="flex">
                                                <button v-for="answer in sectionsData.writing.studentAnswers"
                                                    :key="answer.task_number"
                                                    @click="activeWritingTask = answer.task_number"
                                                    :class="[
                                                        'flex-1 py-3 text-sm font-semibold text-center rounded-t-lg transition-all duration-200 border border-b-0',
                                                        activeWritingTask === answer.task_number
                                                            ? 'bg-white text-[#C8102E] border-gray-200 -mb-px z-10'
                                                            : 'bg-gray-50 text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-100'
                                                    ]">
                                                    Task {{ answer.task_number }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="activeWritingAnswer" class="p-6 space-y-5">
                                        <!-- Question -->
                                        <div v-if="activeWritingAnswer.question_text || activeWritingAnswer.question_image">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-1 h-5 bg-violet-500 rounded-full"></div>
                                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Question</h4>
                                            </div>
                                            <div class="bg-violet-50/50 border border-violet-200/50 rounded-xl p-5 space-y-4">
                                                <div v-if="activeWritingAnswer.question_image" class="flex justify-center">
                                                    <img :src="activeWritingAnswer.question_image" alt="Question image" class="max-w-full rounded-lg border border-violet-200/40 shadow-sm" />
                                                </div>
                                                <div v-if="activeWritingAnswer.question_text" class="prose prose-sm max-w-none text-gray-700 leading-relaxed" v-html="activeWritingAnswer.question_text"></div>
                                            </div>
                                        </div>

                                        <!-- My Answer -->
                                        <div>
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-1 h-5 bg-emerald-500 rounded-full"></div>
                                                <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider">My Answer</h4>
                                            </div>
                                            <div v-if="activeWritingAnswer.answer_text"
                                                 class="bg-white border border-gray-200 rounded-xl p-5 prose prose-sm max-w-none text-gray-700 whitespace-pre-wrap leading-relaxed max-h-96 overflow-y-auto">
                                                {{ activeWritingAnswer.answer_text }}
                                            </div>
                                            <div v-else class="bg-gray-50 border border-gray-200 rounded-xl p-8 text-center">
                                                <p class="text-gray-400 italic">No answer provided for this task.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Writing AI Evaluation Button -->
                                <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                                         :class="sectionsData.writing.aiEvaluated ? 'bg-emerald-100' : 'bg-gradient-to-br from-violet-500/10 to-violet-500/5'">
                                        <i class="fas" :class="sectionsData.writing.aiEvaluated ? 'fa-check-circle text-emerald-600 text-xl' : 'fa-robot text-violet-500 text-xl'"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm">Writing AI Evaluation</h4>
                                        <p v-if="sectionsData.writing.aiEvaluated" class="text-xs text-emerald-600 font-semibold">Band {{ bandScoreRange(sectionsData.writing.aiBandScore) }}</p>
                                        <p v-else class="text-xs text-gray-500">Instant AI feedback for writing</p>
                                        <p v-if="aiEvalError.writing" class="text-xs text-red-500 mt-1">{{ aiEvalError.writing }}</p>
                                    </div>
                                    <button v-if="sectionsData.writing.aiEvaluated" @click="openAiModal('writing')"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-all shrink-0">View</button>
                                    <button v-else-if="canUseAI && sectionsData.writing?.hasAiFeature" @click="startAIEvaluation('writing')" :disabled="aiEvalLoading.writing"
                                        class="px-4 py-2 bg-gradient-to-r from-violet-500 to-violet-600 text-white rounded-lg text-xs font-semibold hover:shadow-md transition-all disabled:opacity-50 shrink-0">
                                        <svg v-if="aiEvalLoading.writing" class="animate-spin h-3.5 w-3.5 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ aiEvalLoading.writing ? 'Evaluating...' : 'Get AI Evaluation' }}
                                    </button>
                                </div>
                            </template>

                            <!-- ── Speaking: Tabbed Submission with AudioVisualizer ── -->
                            <template v-if="sectionKey === 'speaking' && sectionsData.speaking?.studentAnswers?.length">
                                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                                    <div class="border-b border-gray-200">
                                        <div class="p-5 pb-0">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="font-bold text-gray-900 flex items-center gap-2">
                                                    <i class="fas fa-microphone text-orange-600"></i>
                                                    Speaking - Submission
                                                </h3>
                                                <span v-if="sectionsData.speaking.bandScore"
                                                      class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full font-bold text-sm">
                                                    Band: {{ bandScoreRange(sectionsData.speaking.bandScore) }}
                                                </span>
                                                <span v-else-if="sectionsData.speaking.aiBandScore"
                                                      class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full font-bold text-sm flex items-center gap-1">
                                                    <i class="fas fa-robot text-[10px]"></i>
                                                    Band: {{ bandScoreRange(sectionsData.speaking.aiBandScore) }}
                                                </span>
                                                <span v-else class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full font-semibold text-xs">
                                                    Pending Evaluation
                                                </span>
                                            </div>
                                            <!-- Part Tabs -->
                                            <div class="flex">
                                                <button v-for="pn in speakingPartNumbers" :key="pn"
                                                    @click="activeSpeakingPart = pn"
                                                    :class="[
                                                        'flex-1 py-3 text-sm font-semibold text-center rounded-t-lg transition-all duration-200 border border-b-0',
                                                        activeSpeakingPart === pn
                                                            ? 'bg-white text-[#C8102E] border-gray-200 -mb-px z-10'
                                                            : 'bg-gray-50 text-gray-500 border-transparent hover:text-gray-700 hover:bg-gray-100'
                                                    ]">
                                                    Part {{ pn }}
                                                    <span class="ml-1 text-[10px] font-normal text-gray-400">({{ speakingParts[pn]?.length || 0 }})</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="activeSpeakingAnswers.length" class="p-6 space-y-5">
                                        <div v-for="(answer, idx) in activeSpeakingAnswers" :key="answer.id" class="rounded-xl border border-gray-100 overflow-hidden">
                                            <div class="px-4 py-2.5 bg-gray-50/70 border-b border-gray-100 flex items-center gap-2.5">
                                                <div class="w-6 h-6 rounded-full bg-[#C8102E]/10 flex items-center justify-center shrink-0">
                                                    <span class="text-[10px] font-bold text-[#C8102E]">{{ idx + 1 }}</span>
                                                </div>
                                                <span class="text-xs font-semibold text-gray-600">Question {{ idx + 1 }}</span>
                                            </div>
                                            <div class="p-4 space-y-3">
                                                <div v-if="answer.question_text" class="bg-amber-50/50 border border-amber-200/50 rounded-lg p-4">
                                                    <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed text-[13px]" v-html="answer.question_text"></div>
                                                </div>
                                                <AudioVisualizer v-if="answer.recording_url"
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

                                <!-- Speaking AI Evaluation Button -->
                                <div class="bg-white rounded-2xl border border-gray-200 p-5 flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
                                         :class="sectionsData.speaking.aiEvaluated ? 'bg-emerald-100' : 'bg-gradient-to-br from-orange-500/10 to-orange-500/5'">
                                        <i class="fas" :class="sectionsData.speaking.aiEvaluated ? 'fa-check-circle text-emerald-600 text-xl' : 'fa-robot text-orange-500 text-xl'"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-900 text-sm">Speaking AI Evaluation</h4>
                                        <p v-if="sectionsData.speaking.aiEvaluated" class="text-xs text-emerald-600 font-semibold">Band {{ bandScoreRange(sectionsData.speaking.aiBandScore) }}</p>
                                        <p v-else class="text-xs text-gray-500">AI feedback for speaking</p>
                                        <p v-if="aiEvalError.speaking" class="text-xs text-red-500 mt-1">{{ aiEvalError.speaking }}</p>
                                    </div>
                                    <button v-if="sectionsData.speaking.aiEvaluated" @click="openAiModal('speaking')"
                                        class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-xs font-semibold hover:bg-emerald-700 transition-all shrink-0">View</button>
                                    <button v-else-if="canUseAI && sectionsData.speaking?.hasAiFeature" @click="startAIEvaluation('speaking')" :disabled="aiEvalLoading.speaking"
                                        class="px-4 py-2 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg text-xs font-semibold hover:shadow-md transition-all disabled:opacity-50 shrink-0">
                                        <svg v-if="aiEvalLoading.speaking" class="animate-spin h-3.5 w-3.5 text-white inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        {{ aiEvalLoading.speaking ? 'Evaluating...' : 'Get AI Evaluation' }}
                                    </button>
                                </div>
                            </template>

                        </template>
                    </template>

                    <!-- ── Human Evaluation Status ── -->
                    <template v-if="availableSections.some(s => ['writing','speaking'].includes(s))">
                        <div v-if="Object.values(sectionsData).some(d => d.humanEvaluationRequested && !d.humanEvaluationCompleted)"
                             class="bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl p-5 border border-amber-200">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-clock text-amber-600 text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-bold text-amber-800 mb-1">Human Evaluation In Progress</h4>
                                    <p class="text-sm text-amber-700">
                                        Your
                                        {{ Object.entries(sectionsData).filter(([k,v]) => v.humanEvaluationRequested && !v.humanEvaluationCompleted).map(([k]) => k.charAt(0).toUpperCase() + k.slice(1)).join(' and ') }}
                                        {{ Object.entries(sectionsData).filter(([k,v]) => v.humanEvaluationRequested && !v.humanEvaluationCompleted).length > 1 ? 'sections are' : 'section is' }}
                                        being evaluated by a teacher.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- RIGHT SIDEBAR (1/3) -->
                <div class="space-y-6">
                    <!-- Evaluation Type Badge (offline only) -->
                    <div v-if="isOfflineStudent" class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-3">Evaluation</h3>
                        <div class="p-3 bg-gray-50 rounded-xl">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Evaluation Type:</span>
                                <span class="font-semibold" :class="evaluationType === 'ai' ? 'text-blue-600' : evaluationType === 'human' ? 'text-purple-600' : 'text-emerald-600'">
                                    <template v-if="evaluationType === 'ai'"><i class="fas fa-robot mr-1"></i>AI Only</template>
                                    <template v-else-if="evaluationType === 'human'"><i class="fas fa-user-tie mr-1"></i>Human Only</template>
                                    <template v-else><i class="fas fa-balance-scale mr-1"></i>AI + Human</template>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions Card -->
                    <div class="bg-white rounded-2xl border border-gray-200 p-5">
                        <h3 class="font-bold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            <!-- Writing AI Evaluation -->
                            <template v-if="sectionsData.writing?.status === 'completed'">
                                <button v-if="sectionsData.writing.aiEvaluated" @click="openAiModal('writing')"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-violet-500 to-violet-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                    <i class="fas fa-pen-fancy"></i> View Writing AI Evaluation
                                </button>
                                <button v-else-if="canUseAI && sectionsData.writing?.hasAiFeature" @click="startAIEvaluation('writing')" :disabled="aiEvalLoading.writing"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-violet-500 to-violet-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all disabled:opacity-50">
                                    <svg v-if="aiEvalLoading.writing" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <i v-else class="fas fa-pen-fancy"></i>
                                    {{ aiEvalLoading.writing ? 'Evaluating...' : 'Get Writing AI Evaluation' }}
                                </button>
                                <p v-if="aiEvalError.writing" class="text-xs text-red-500 -mt-1 px-1">{{ aiEvalError.writing }}</p>
                            </template>

                            <!-- Speaking AI Evaluation -->
                            <template v-if="sectionsData.speaking?.status === 'completed'">
                                <button v-if="sectionsData.speaking.aiEvaluated" @click="openAiModal('speaking')"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                    <i class="fas fa-microphone"></i> View Speaking AI Evaluation
                                </button>
                                <button v-else-if="canUseAI && sectionsData.speaking?.hasAiFeature" @click="startAIEvaluation('speaking')" :disabled="aiEvalLoading.speaking"
                                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all disabled:opacity-50">
                                    <svg v-if="aiEvalLoading.speaking" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <i v-else class="fas fa-microphone"></i>
                                    {{ aiEvalLoading.speaking ? 'Evaluating...' : 'Get Speaking AI Evaluation' }}
                                </button>
                                <p v-if="aiEvalError.speaking" class="text-xs text-red-500 -mt-1 px-1">{{ aiEvalError.speaking }}</p>
                            </template>

                            <!-- Human Evaluation Request -->
                            <button v-if="canUseHuman && hasHumanEvaluationFeature && availableSections.some(s => ['writing','speaking'].includes(s) && sectionsData[s]?.status === 'completed' && !sectionsData[s]?.humanEvaluationRequested)"
                                @click="handleRequestEvaluation"
                                class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-xl font-semibold hover:shadow-lg hover:scale-[1.02] transition-all">
                                <i class="fas fa-user-tie"></i> Request Human Evaluation
                            </button>

                            <!-- Navigation Links -->
                            <a href="/student/test/full-test"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-all">
                                <i class="fas fa-list"></i> All Full Tests
                            </a>
                            <a href="/student/dashboard"
                               class="w-full flex items-center justify-center gap-2 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition-all">
                                <i class="fas fa-home"></i> Dashboard
                            </a>
                        </div>
                    </div>

                    <!-- Test Info Card -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-24">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <i class="fas fa-info-circle text-gray-400"></i>
                            About Full Test Scoring
                        </h3>
                        <ul class="space-y-3 text-sm text-gray-600">
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                                <span>Overall band is the average of all 4 section scores, rounded to nearest 0.5.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-check text-emerald-500 mt-0.5 shrink-0"></i>
                                <span>Listening & Reading are auto-scored based on correct answers.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-robot text-blue-500 mt-0.5 shrink-0"></i>
                                <span>Writing & Speaking require AI or human evaluation for band scores.</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <i class="fas fa-bolt text-[#C8102E] mt-0.5 shrink-0"></i>
                                <span>Click 'Explain Answer' on any question for AI-powered explanations.</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- AI Evaluation Modal (reuse) -->
    <AiEvaluationModal
        :show="showAiModal"
        :evaluation="activeAiEvaluation"
        :sectionName="activeAiSection || 'writing'"
        :studentAnswers="activeAiStudentAnswers"
        @close="showAiModal = false"
    />
    </StudentDashboardLayout>
</template>
