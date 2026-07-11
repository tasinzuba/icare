<script setup>
import { ref, computed, watch } from 'vue';
import AudioVisualizer from './AudioVisualizer.vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    evaluation: { type: Object, default: null },
    sectionName: { type: String, default: 'writing' },
    studentAnswers: { type: Array, default: () => [] },
});

const emit = defineEmits(['close']);

// State
const activeTab = ref(1);
const viewMode = ref('feedback');
const expandedCriteria = ref(null);
const tooltip = ref(null);
const showQuestion = ref(false);

const toggleCriteria = (key) => {
    expandedCriteria.value = expandedCriteria.value === key ? null : key;
};

watch(() => props.show, (val) => {
    if (val) {
        activeTab.value = 1;
        viewMode.value = 'feedback';
        expandedCriteria.value = null;
        tooltip.value = null;
        showQuestion.value = false;
    }
});

// ── Data helpers ──────────────────────────
const activeData = computed(() => {
    if (!props.evaluation) return null;
    if (props.sectionName === 'writing') {
        return props.evaluation.tasks?.find(t => t.task_number === activeTab.value) || props.evaluation.tasks?.[0];
    }
    return props.evaluation.parts?.find(p => p.part_number === activeTab.value) || props.evaluation.parts?.[0];
});

const items = computed(() => {
    if (!props.evaluation) return [];
    return props.sectionName === 'writing' ? (props.evaluation.tasks || []) : (props.evaluation.parts || []);
});

// Match current task/part question from studentAnswers
const activeQuestion = computed(() => {
    if (!props.studentAnswers?.length) return null;
    // For writing: match by task_number (order_number)
    if (props.sectionName === 'writing') {
        return props.studentAnswers.find(a => a.task_number === activeTab.value) || null;
    }
    // For speaking: return first match by part_number
    return props.studentAnswers.find(a => (a.part_number || a.task_number) === activeTab.value) || null;
});

// For speaking: all recordings in current part
const activePartRecordings = computed(() => {
    if (props.sectionName !== 'speaking' || !props.studentAnswers?.length) return [];
    return props.studentAnswers.filter(a => (a.part_number || a.task_number) === activeTab.value);
});

const band = (score) => {
    if (!score && score !== 0) return '-';
    const s = parseFloat(score);
    return isNaN(s) ? '-' : s.toFixed(1);
};

const scorePercent = (score) => {
    const s = parseFloat(score);
    return isNaN(s) ? 0 : Math.min((s / 9) * 100, 100);
};

const scoreColor = (score) => {
    const s = parseFloat(score);
    if (isNaN(s)) return 'text-gray-400';
    if (s >= 7) return 'text-emerald-600';
    if (s >= 5.5) return 'text-amber-600';
    return 'text-red-500';
};

const scoreBg = (score) => {
    const s = parseFloat(score);
    if (isNaN(s)) return 'bg-gray-50';
    if (s >= 7) return 'bg-emerald-50';
    if (s >= 5.5) return 'bg-amber-50';
    return 'bg-red-50';
};

const scoreBarColor = (score) => {
    const s = parseFloat(score);
    if (isNaN(s)) return '#d1d5db';
    if (s >= 7) return '#10B981';
    if (s >= 5.5) return '#F59E0B';
    return '#EF4444';
};

const fullLabel = (key) => ({
    'Task Achievement': 'Task Achievement',
    'Task Response': 'Task Response',
    'Coherence and Cohesion': 'Coherence & Cohesion',
    'Lexical Resource': 'Lexical Resource',
    'Grammar': 'Grammatical Range & Accuracy',
    'Grammatical Range and Accuracy': 'Grammatical Range & Accuracy',
    'Fluency and Coherence': 'Fluency & Coherence',
    'Pronunciation': 'Pronunciation',
}[key] || key);

const criterionIcon = (key) => {
    const k = key.toLowerCase();
    if (k.includes('task') || k.includes('response')) return 'fa-bullseye';
    if (k.includes('coherence') || k.includes('cohesion') || k.includes('fluency')) return 'fa-link';
    if (k.includes('lexical') || k.includes('vocabulary')) return 'fa-book';
    if (k.includes('grammar') || k.includes('pronunciation')) return 'fa-spell-check';
    return 'fa-pen';
};

const feedbackFor = (criterionKey) => {
    if (!activeData.value?.feedback) return null;
    const fb = activeData.value.feedback;
    if (fb[criterionKey]) return fb[criterionKey];
    const snake = criterionKey.toLowerCase().replace(/\s+/g, '_');
    if (fb[snake]) return fb[snake];
    const lower = criterionKey.toLowerCase();
    for (const [k, v] of Object.entries(fb)) {
        if (k.toLowerCase().includes(lower.split(' ')[0].toLowerCase())) return v;
    }
    return null;
};

// ── Error counts ──────────────────────────
const grammarCount = computed(() => activeData.value?.grammar_corrections?.length || 0);
const vocabCount = computed(() => activeData.value?.vocabulary_suggestions?.length || 0);

// ── Essay parts with error highlighting ──────────────────────────
const essayParts = computed(() => {
    if (!activeData.value?.essay_text) return [];

    const text = activeData.value.essay_text;
    const annotations = [];

    // Collect grammar corrections
    (activeData.value.grammar_corrections || []).forEach((c, i) => {
        if (!c.original) return;
        const lowerText = text.toLowerCase();
        const lowerOrig = c.original.toLowerCase();
        let searchFrom = 0;
        while (searchFrom < text.length) {
            const idx = lowerText.indexOf(lowerOrig, searchFrom);
            if (idx === -1) break;
            const overlaps = annotations.some(a => idx < a.end && idx + c.original.length > a.start);
            if (!overlaps) {
                annotations.push({
                    start: idx,
                    end: idx + c.original.length,
                    type: 'grammar',
                    original: c.original,
                    corrected: c.corrected,
                    reason: c.type || 'Grammar',
                    id: 'g' + i,
                });
                break;
            }
            searchFrom = idx + 1;
        }
    });

    // Collect vocabulary suggestions
    (activeData.value.vocabulary_suggestions || []).forEach((v, i) => {
        if (!v.original) return;
        const lowerText = text.toLowerCase();
        const lowerOrig = v.original.toLowerCase();
        let searchFrom = 0;
        while (searchFrom < text.length) {
            const idx = lowerText.indexOf(lowerOrig, searchFrom);
            if (idx === -1) break;
            const overlaps = annotations.some(a => idx < a.end && idx + v.original.length > a.start);
            if (!overlaps) {
                annotations.push({
                    start: idx,
                    end: idx + v.original.length,
                    type: 'vocabulary',
                    original: v.original,
                    corrected: v.suggested,
                    reason: v.reason || 'Vocabulary',
                    id: 'v' + i,
                });
                break;
            }
            searchFrom = idx + 1;
        }
    });

    // Sort by position
    annotations.sort((a, b) => a.start - b.start);

    // Build parts
    const parts = [];
    let lastEnd = 0;
    for (const ann of annotations) {
        if (ann.start > lastEnd) {
            parts.push({ type: 'text', content: text.slice(lastEnd, ann.start) });
        }
        parts.push({
            type: ann.type,
            content: text.slice(ann.start, ann.end),
            original: ann.original,
            corrected: ann.corrected,
            reason: ann.reason,
            id: ann.id,
        });
        lastEnd = ann.end;
    }
    if (lastEnd < text.length) {
        parts.push({ type: 'text', content: text.slice(lastEnd) });
    }
    return parts;
});

// ── Tooltip ──────────────────────────
const showTooltip = (part, event) => {
    const rect = event.target.getBoundingClientRect();
    tooltip.value = {
        type: part.type,
        original: part.original,
        corrected: part.corrected,
        reason: part.reason,
        top: rect.bottom + 8,
        left: Math.max(160, Math.min(rect.left + rect.width / 2, window.innerWidth - 160)),
    };
};

const hideTooltip = () => {
    tooltip.value = null;
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show && evaluation"
                 class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-5"
                 @keydown.esc="emit('close')" tabindex="-1">

                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/40 backdrop-blur-[2px]" @click="emit('close')"></div>

                <!-- ═══════ MODAL ═══════ -->
                <div class="modal-box relative bg-white rounded-2xl shadow-2xl w-full max-w-[960px] max-h-[88vh] overflow-hidden flex flex-col z-10 border-t-[3px] border-[#C8102E]">

                    <!-- ══════ HEADER ══════ -->
                    <div class="shrink-0 flex items-center justify-between px-5 sm:px-6 py-3.5 bg-gradient-to-r from-gray-50/80 to-white border-b border-gray-100">
                        <!-- Left: icon + title -->
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#9B0000] flex items-center justify-center shadow-sm shadow-red-200/50 shrink-0">
                                <i class="fas fa-robot text-white text-sm"></i>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-[15px] font-bold text-gray-900 leading-tight">AI Evaluation</h3>
                                <p class="text-[11px] text-gray-400 mt-0.5">
                                    {{ sectionName === 'writing' ? 'Writing' : 'Speaking' }} Analysis
                                    <span v-if="items.length > 1" class="text-gray-300">&bull;</span>
                                    <span v-if="items.length > 1">{{ sectionName === 'writing' ? 'Task' : 'Part' }} {{ activeTab }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- Right: task tabs + close -->
                        <div class="flex items-center gap-2.5">
                            <div v-if="items.length > 1" class="flex bg-gray-100 rounded-lg p-0.5 mr-1">
                                <button v-for="item in items" :key="item.task_number || item.part_number"
                                    @click="activeTab = item.task_number || item.part_number; expandedCriteria = null; tooltip = null; showQuestion = false"
                                    :class="[
                                        'px-3 py-1.5 text-xs font-semibold rounded-md transition-all duration-200',
                                        (activeTab === (item.task_number || item.part_number))
                                            ? 'bg-white text-gray-900 shadow-sm'
                                            : 'text-gray-400 hover:text-gray-600'
                                    ]">
                                    {{ sectionName === 'writing' ? 'T' : 'P' }}{{ item.task_number || item.part_number }}
                                </button>
                            </div>
                            <button @click="emit('close')"
                                    class="w-8 h-8 rounded-lg hover:bg-gray-100 flex items-center justify-center transition-colors text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <!-- ══════ BODY ══════ -->
                    <div class="flex-1 flex min-h-0 overflow-hidden modal-body">

                        <!-- ── LEFT: Score Panel ── -->
                        <div class="left-panel w-[280px] shrink-0 border-r border-gray-100 bg-gradient-to-b from-gray-50/60 to-white flex flex-col overflow-y-auto">

                            <!-- Score Ring -->
                            <div class="px-6 pt-6 pb-4 text-center">
                                <div class="relative w-[108px] h-[108px] mx-auto">
                                    <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                                        <defs>
                                            <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                                <stop offset="0%" stop-color="#C8102E" />
                                                <stop offset="100%" stop-color="#8B0000" />
                                            </linearGradient>
                                        </defs>
                                        <circle cx="50" cy="50" r="43" fill="none" stroke="#f3f4f6" stroke-width="6"/>
                                        <circle cx="50" cy="50" r="43" fill="none" stroke="url(#ringGrad)" stroke-width="6"
                                            stroke-dasharray="270.18"
                                            :stroke-dashoffset="270.18 - (270.18 * scorePercent(evaluation.overall_band) / 100)"
                                            stroke-linecap="round"
                                            class="score-ring"/>
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="text-[30px] font-extrabold text-gray-900 leading-none tracking-tight">{{ band(evaluation.overall_band) }}</span>
                                        <span class="text-[10px] text-gray-400 mt-0.5 font-medium">/ 9.0</span>
                                    </div>
                                </div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-[0.1em] mt-3">Overall Band</p>
                            </div>

                            <!-- Criteria Scores -->
                            <div class="px-3 pb-3 space-y-1 flex-1">
                                <button v-for="(score, criterion) in activeData?.criteria" :key="'ls-' + criterion"
                                    @click="expandedCriteria = (expandedCriteria === criterion ? null : criterion); viewMode = 'feedback'"
                                    :class="[
                                        'w-full rounded-xl px-3 py-2.5 text-left transition-all duration-200 group',
                                        expandedCriteria === criterion
                                            ? 'bg-white shadow-sm ring-1 ring-gray-200/80'
                                            : 'hover:bg-white/60'
                                    ]">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-6 h-6 rounded-lg bg-gray-100 group-hover:bg-gray-200/70 flex items-center justify-center shrink-0 transition-colors">
                                            <i :class="['fas text-[10px] text-gray-400', criterionIcon(criterion)]"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="text-[11px] font-medium text-gray-600 truncate pr-2">{{ fullLabel(criterion) }}</span>
                                                <span :class="['text-xs font-bold tabular-nums shrink-0', scoreColor(score)]">{{ band(score) }}</span>
                                            </div>
                                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full transition-all duration-700 ease-out score-bar"
                                                     :style="{ width: scorePercent(score) + '%', backgroundColor: scoreBarColor(score) }"></div>
                                            </div>
                                        </div>
                                    </div>
                                </button>
                            </div>

                            <!-- Bottom Stats -->
                            <div class="px-4 py-3 border-t border-gray-100 space-y-2.5">
                                <!-- Word Count -->
                                <div v-if="sectionName === 'writing' && activeData?.word_count" class="flex items-center justify-between">
                                    <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                        <i class="fas fa-font text-[9px]"></i> Words
                                    </span>
                                    <span class="text-xs font-bold tabular-nums"
                                          :class="activeData.word_count >= activeData.required_words ? 'text-emerald-600' : 'text-red-500'">
                                        {{ activeData.word_count }}<span class="text-gray-300 font-normal"> / {{ activeData.required_words }}</span>
                                    </span>
                                </div>
                                <!-- Errors Found -->
                                <div v-if="grammarCount + vocabCount > 0" class="flex items-center justify-between">
                                    <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                        <i class="fas fa-exclamation-circle text-[9px]"></i> Issues
                                    </span>
                                    <div class="flex items-center gap-1.5">
                                        <span v-if="grammarCount" class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-red-50 text-red-500">{{ grammarCount }} gram.</span>
                                        <span v-if="vocabCount" class="text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-600">{{ vocabCount }} vocab</span>
                                    </div>
                                </div>
                                <!-- Task Band -->
                                <div v-if="activeData?.band_score" class="flex items-center justify-between">
                                    <span class="text-[11px] text-gray-400 flex items-center gap-1.5">
                                        <i class="fas fa-star text-[9px]"></i>
                                        {{ sectionName === 'writing' ? 'Task' : 'Part' }} Band
                                    </span>
                                    <span :class="['text-xs font-bold tabular-nums', scoreColor(activeData.band_score)]">{{ band(activeData.band_score) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- ── RIGHT: Content Panel ── -->
                        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

                            <!-- View Toggle (Writing) -->
                            <div v-if="sectionName === 'writing'" class="shrink-0 px-5 py-2.5 border-b border-gray-100 flex items-center gap-1.5 bg-white">
                                <button @click="viewMode = 'feedback'; tooltip = null"
                                    :class="[
                                        'flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
                                        viewMode === 'feedback'
                                            ? 'bg-[#C8102E] text-white shadow-sm shadow-red-200/40'
                                            : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'
                                    ]">
                                    <i class="fas fa-comments text-[10px]"></i> AI Feedback
                                </button>
                                <button @click="viewMode = 'response'; expandedCriteria = null"
                                    :class="[
                                        'flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
                                        viewMode === 'response'
                                            ? 'bg-[#C8102E] text-white shadow-sm shadow-red-200/40'
                                            : 'text-gray-400 hover:bg-gray-50 hover:text-gray-600'
                                    ]">
                                    <i class="fas fa-file-alt text-[10px]"></i> Your Essay
                                    <span v-if="grammarCount + vocabCount > 0"
                                          class="ml-0.5 text-[9px] font-bold bg-white/20 px-1.5 py-0.5 rounded-full"
                                          :class="viewMode === 'response' ? 'bg-white/20' : 'bg-red-50 text-red-500'">
                                        {{ grammarCount + vocabCount }}
                                    </span>
                                </button>
                            </div>

                            <!-- Scrollable Content -->
                            <div class="flex-1 overflow-y-auto">

                                <!-- ═══ FEEDBACK VIEW ═══ -->
                                <template v-if="(sectionName === 'writing' && viewMode === 'feedback' && activeData) || (sectionName === 'speaking' && activeData)">
                                    <div class="p-5 space-y-2.5">

                                        <!-- Speaking: Your Recordings (all in this part) -->
                                        <div v-if="sectionName === 'speaking' && activePartRecordings.length" class="rounded-xl border border-gray-100 overflow-hidden">
                                            <div class="px-4 py-3 flex items-center gap-3 border-b border-gray-50 bg-gray-50/30">
                                                <div class="w-7 h-7 rounded-lg bg-[#C8102E]/10 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-headphones text-[11px] text-[#C8102E]"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800">Your Recordings</span>
                                                <span class="text-[10px] font-semibold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">{{ activePartRecordings.length }}</span>
                                            </div>
                                            <div class="p-3 space-y-2">
                                                <div v-for="(rec, ri) in activePartRecordings" :key="ri">
                                                    <AudioVisualizer
                                                        v-if="rec.recording_url"
                                                        :src="rec.recording_url"
                                                        :mimeType="rec.recording_mime_type || 'audio/webm'"
                                                        :label="'Q' + (ri + 1)"
                                                        :compact="true"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Speaking Transcription -->
                                        <div v-if="sectionName === 'speaking' && activeData.transcription"
                                             class="rounded-xl border border-gray-100 overflow-hidden">
                                            <button @click="toggleCriteria('__transcription')"
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50/50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-microphone text-[11px] text-purple-400"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800 flex-1">Transcription</span>
                                                <i :class="['fas fa-chevron-down text-[10px] text-gray-300 transition-transform duration-200', expandedCriteria === '__transcription' && 'rotate-180']"></i>
                                            </button>
                                            <Transition name="slide">
                                                <div v-if="expandedCriteria === '__transcription'" class="px-4 pb-4 border-t border-gray-50">
                                                    <p class="mt-3 text-[13px] text-gray-500 leading-[1.8] whitespace-pre-wrap">{{ activeData.transcription }}</p>
                                                </div>
                                            </Transition>
                                        </div>

                                        <!-- Per-Criteria Feedback Cards -->
                                        <div v-for="(score, criterion) in activeData.criteria" :key="'fb-' + criterion"
                                             :class="[
                                                 'rounded-xl border overflow-hidden transition-all duration-200',
                                                 expandedCriteria === criterion ? 'border-gray-200 shadow-sm' : 'border-gray-100'
                                             ]">
                                            <button @click="toggleCriteria(criterion)"
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50/50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-gray-50 flex items-center justify-center shrink-0">
                                                    <i :class="['fas text-[11px] text-gray-400', criterionIcon(criterion)]"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800 flex-1 min-w-0 truncate">{{ fullLabel(criterion) }}</span>
                                                <span :class="['text-[11px] font-bold px-2 py-0.5 rounded-full shrink-0', scoreBg(score), scoreColor(score)]">
                                                    {{ band(score) }}
                                                </span>
                                                <i :class="['fas fa-chevron-down text-[10px] text-gray-300 transition-transform duration-200 shrink-0 ml-1', expandedCriteria === criterion && 'rotate-180']"></i>
                                            </button>
                                            <Transition name="slide">
                                                <div v-if="expandedCriteria === criterion" class="px-4 pb-4 border-t border-gray-50">
                                                    <p v-if="feedbackFor(criterion)"
                                                       class="mt-3 text-[13px] text-gray-600 leading-[1.8]">
                                                        {{ feedbackFor(criterion) }}
                                                    </p>
                                                    <p v-else class="mt-3 text-[13px] text-gray-300 italic">No detailed feedback available.</p>
                                                </div>
                                            </Transition>
                                        </div>

                                        <!-- Errors & Corrections -->
                                        <div v-if="sectionName === 'writing' && (grammarCount + vocabCount > 0)"
                                             :class="[
                                                 'rounded-xl border overflow-hidden transition-all duration-200',
                                                 expandedCriteria === '__corrections' ? 'border-red-200 shadow-sm' : 'border-gray-100'
                                             ]">
                                            <button @click="toggleCriteria('__corrections')"
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50/50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-search text-[11px] text-red-400"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800 flex-1">Errors &amp; Corrections</span>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 shrink-0">
                                                    {{ grammarCount + vocabCount }}
                                                </span>
                                                <i :class="['fas fa-chevron-down text-[10px] text-gray-300 transition-transform duration-200 shrink-0 ml-1', expandedCriteria === '__corrections' && 'rotate-180']"></i>
                                            </button>
                                            <Transition name="slide">
                                                <div v-if="expandedCriteria === '__corrections'" class="border-t border-gray-50">
                                                    <!-- Grammar -->
                                                    <div v-for="(c, i) in activeData.grammar_corrections" :key="'gc' + i"
                                                         class="flex items-start gap-3 px-4 py-2.5 border-b border-gray-50 last:border-b-0">
                                                        <span class="shrink-0 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-red-50 text-red-500 mt-0.5">
                                                            {{ c.type || 'Grammar' }}
                                                        </span>
                                                        <div class="flex-1 text-[13px] leading-relaxed">
                                                            <span class="text-red-400/80 line-through">{{ c.original }}</span>
                                                            <i class="fas fa-long-arrow-alt-right text-[10px] text-gray-300 mx-2"></i>
                                                            <span class="text-emerald-700 font-medium">{{ c.corrected }}</span>
                                                        </div>
                                                    </div>
                                                    <!-- Vocabulary -->
                                                    <div v-for="(v, i) in activeData.vocabulary_suggestions" :key="'vs' + i"
                                                         class="flex items-start gap-3 px-4 py-2.5 border-b border-gray-50 last:border-b-0">
                                                        <span class="shrink-0 text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-amber-50 text-amber-600 mt-0.5">
                                                            Vocab
                                                        </span>
                                                        <div class="flex-1 text-[13px] leading-relaxed">
                                                            <span class="text-amber-500">{{ v.original }}</span>
                                                            <i class="fas fa-long-arrow-alt-right text-[10px] text-gray-300 mx-2"></i>
                                                            <span class="text-emerald-700 font-medium">{{ v.suggested }}</span>
                                                            <span v-if="v.reason" class="text-[11px] text-gray-400 ml-1.5">({{ v.reason }})</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>

                                        <!-- Improvement Tips -->
                                        <div v-if="activeData.improvement_tips?.length"
                                             class="rounded-xl bg-gradient-to-br from-blue-50/60 to-indigo-50/40 border border-blue-100/60 p-4">
                                            <div class="flex items-center gap-2 mb-3">
                                                <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center">
                                                    <i class="fas fa-lightbulb text-[11px] text-blue-500"></i>
                                                </div>
                                                <span class="text-[13px] font-bold text-blue-900">Tips for Improvement</span>
                                            </div>
                                            <ul class="space-y-2.5">
                                                <li v-for="(tip, i) in activeData.improvement_tips" :key="i"
                                                    class="flex items-start gap-2.5 text-[13px] text-blue-800/75 leading-relaxed">
                                                    <span class="w-5 h-5 rounded-full bg-blue-100/80 text-blue-600 text-[10px] font-bold flex items-center justify-center shrink-0 mt-0.5">
                                                        {{ i + 1 }}
                                                    </span>
                                                    <span>{{ tip }}</span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- Question Prompt Toggle -->
                                        <div v-if="activeQuestion && (activeQuestion.question_text || activeQuestion.question_image)"
                                             :class="[
                                                 'rounded-xl border overflow-hidden transition-all duration-200',
                                                 showQuestion ? 'border-amber-200 shadow-sm' : 'border-gray-100'
                                             ]">
                                            <button @click="showQuestion = !showQuestion"
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50/50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-question-circle text-[11px] text-amber-500"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800 flex-1">
                                                    {{ sectionName === 'writing' ? 'Task' : 'Part' }} {{ activeTab }} — Question
                                                </span>
                                                <i :class="['fas fa-chevron-down text-[10px] text-gray-300 transition-transform duration-200 shrink-0', showQuestion && 'rotate-180']"></i>
                                            </button>
                                            <Transition name="slide">
                                                <div v-if="showQuestion" class="border-t border-amber-100/60">
                                                    <div class="p-4 bg-amber-50/30">
                                                        <!-- Question Title -->
                                                        <p v-if="activeQuestion.question_title"
                                                           class="text-[11px] font-bold text-amber-700 uppercase tracking-wider mb-2">
                                                            {{ activeQuestion.question_title }}
                                                        </p>
                                                        <!-- Question Image -->
                                                        <div v-if="activeQuestion.question_image" class="mb-3">
                                                            <img :src="activeQuestion.question_image"
                                                                 alt="Question image"
                                                                 class="max-w-full h-auto rounded-lg border border-amber-200/60 shadow-sm max-h-[240px] object-contain" />
                                                        </div>
                                                        <!-- Question Text -->
                                                        <div v-if="activeQuestion.question_text"
                                                             class="prose prose-sm max-w-none text-gray-700 leading-relaxed question-content"
                                                             v-html="activeQuestion.question_text"></div>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </template>

                                <!-- ═══ YOUR ESSAY (with inline error highlighting) ═══ -->
                                <template v-if="sectionName === 'writing' && viewMode === 'response' && activeData">

                                    <!-- Error Legend Bar -->
                                    <div v-if="grammarCount + vocabCount > 0"
                                         class="sticky top-0 z-10 bg-white/95 backdrop-blur-sm border-b border-gray-100 px-5 py-2 flex items-center gap-5">
                                        <span class="text-[11px] font-medium text-gray-400 uppercase tracking-wider">Legend</span>
                                        <div v-if="grammarCount" class="flex items-center gap-1.5">
                                            <span class="inline-block w-4 h-0 border-b-2 border-red-400" style="border-bottom-style:wavy"></span>
                                            <span class="text-[11px] text-gray-500">Grammar <span class="font-semibold text-red-500">({{ grammarCount }})</span></span>
                                        </div>
                                        <div v-if="vocabCount" class="flex items-center gap-1.5">
                                            <span class="inline-block w-4 h-0 border-b-2 border-amber-400 border-dotted"></span>
                                            <span class="text-[11px] text-gray-500">Vocabulary <span class="font-semibold text-amber-600">({{ vocabCount }})</span></span>
                                        </div>
                                        <span class="text-[10px] text-gray-300 ml-auto">Hover for details</span>
                                    </div>

                                    <!-- Annotated Essay -->
                                    <div class="p-5 sm:p-6" @mouseleave="hideTooltip">
                                        <div v-if="essayParts.length"
                                             class="essay-text text-[14px] text-gray-700 leading-[2.1] whitespace-pre-wrap font-[system-ui]">
                                            <template v-for="(part, i) in essayParts" :key="i">
                                                <span v-if="part.type === 'text'">{{ part.content }}</span>
                                                <span v-else-if="part.type === 'grammar'"
                                                      class="error-grammar"
                                                      @mouseenter="showTooltip(part, $event)"
                                                      @mouseleave="hideTooltip"
                                                      @click="showTooltip(part, $event)">{{ part.content }}</span>
                                                <span v-else
                                                      class="error-vocab"
                                                      @mouseenter="showTooltip(part, $event)"
                                                      @mouseleave="hideTooltip"
                                                      @click="showTooltip(part, $event)">{{ part.content }}</span>
                                            </template>
                                        </div>
                                        <div v-else class="text-center py-20">
                                            <div class="w-14 h-14 rounded-2xl bg-gray-50 flex items-center justify-center mx-auto mb-3">
                                                <i class="fas fa-file-alt text-gray-300 text-xl"></i>
                                            </div>
                                            <p class="text-sm text-gray-400">Response text not available</p>
                                        </div>
                                    </div>

                                    <!-- Question Prompt Toggle (in Response view) -->
                                    <div v-if="activeQuestion && (activeQuestion.question_text || activeQuestion.question_image)"
                                         class="mx-5 sm:mx-6 mb-5">
                                        <div :class="[
                                                 'rounded-xl border overflow-hidden transition-all duration-200',
                                                 showQuestion ? 'border-amber-200 shadow-sm' : 'border-gray-100'
                                             ]">
                                            <button @click="showQuestion = !showQuestion"
                                                    class="w-full flex items-center gap-3 px-4 py-3 text-left hover:bg-gray-50/50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center shrink-0">
                                                    <i class="fas fa-question-circle text-[11px] text-amber-500"></i>
                                                </div>
                                                <span class="text-[13px] font-semibold text-gray-800 flex-1">
                                                    View Question Prompt
                                                </span>
                                                <i :class="['fas fa-chevron-down text-[10px] text-gray-300 transition-transform duration-200 shrink-0', showQuestion && 'rotate-180']"></i>
                                            </button>
                                            <Transition name="slide">
                                                <div v-if="showQuestion" class="border-t border-amber-100/60">
                                                    <div class="p-4 bg-amber-50/30">
                                                        <p v-if="activeQuestion.question_title"
                                                           class="text-[11px] font-bold text-amber-700 uppercase tracking-wider mb-2">
                                                            {{ activeQuestion.question_title }}
                                                        </p>
                                                        <div v-if="activeQuestion.question_image" class="mb-3">
                                                            <img :src="activeQuestion.question_image"
                                                                 alt="Question image"
                                                                 class="max-w-full h-auto rounded-lg border border-amber-200/60 shadow-sm max-h-[240px] object-contain" />
                                                        </div>
                                                        <div v-if="activeQuestion.question_text"
                                                             class="prose prose-sm max-w-none text-gray-700 leading-relaxed question-content"
                                                             v-html="activeQuestion.question_text"></div>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>
                                    </div>
                                </template>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- ═══ ERROR TOOLTIP ═══ -->
                <Transition name="tooltip">
                    <div v-if="tooltip"
                         class="fixed z-[200] pointer-events-none"
                         :style="{ top: tooltip.top + 'px', left: tooltip.left + 'px' }">
                        <div class="tooltip-card bg-white rounded-xl shadow-xl border border-gray-200 p-3 w-[280px] -translate-x-1/2">
                            <div class="flex items-center gap-2 mb-2">
                                <span :class="[
                                    'text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md',
                                    tooltip.type === 'grammar' ? 'bg-red-50 text-red-500' : 'bg-amber-50 text-amber-600'
                                ]">
                                    {{ tooltip.type === 'grammar' ? 'Grammar' : 'Vocabulary' }}
                                </span>
                                <span class="text-[10px] text-gray-300">{{ tooltip.reason }}</span>
                            </div>
                            <div class="text-[13px] leading-relaxed">
                                <span :class="tooltip.type === 'grammar' ? 'text-red-400 line-through' : 'text-amber-500'">{{ tooltip.original }}</span>
                                <i class="fas fa-long-arrow-alt-right text-[10px] text-gray-300 mx-1.5"></i>
                                <span class="text-emerald-600 font-semibold">{{ tooltip.corrected }}</span>
                            </div>
                        </div>
                    </div>
                </Transition>

            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
/* ── Modal transitions ── */
.modal-enter-active, .modal-leave-active { transition: opacity 0.25s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }

.modal-box {
    animation: modalSlideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modalSlideIn {
    from { opacity: 0; transform: translateY(20px) scale(0.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ── Score ring animation ── */
.score-ring {
    animation: ringFill 1s cubic-bezier(0.16, 1, 0.3, 1) 0.3s both;
}
@keyframes ringFill {
    from { stroke-dashoffset: 270.18; }
}

/* ── Score bar animation ── */
.score-bar {
    animation: barFill 0.8s cubic-bezier(0.16, 1, 0.3, 1) 0.4s both;
}
@keyframes barFill {
    from { width: 0 !important; }
}

/* ── Accordion slide ── */
.slide-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden; }
.slide-leave-active { transition: all 0.2s ease; overflow: hidden; }
.slide-enter-from { opacity: 0; max-height: 0; }
.slide-enter-to   { opacity: 1; max-height: 600px; }
.slide-leave-from { opacity: 1; max-height: 600px; }
.slide-leave-to   { opacity: 0; max-height: 0; }

/* ── Tooltip transition ── */
.tooltip-enter-active { transition: all 0.15s ease-out; }
.tooltip-leave-active { transition: all 0.1s ease-in; }
.tooltip-enter-from { opacity: 0; transform: translateY(-4px); }
.tooltip-leave-to   { opacity: 0; transform: translateY(-4px); }

/* ── Error highlighting — Grammarly style ── */
.error-grammar {
    text-decoration: underline wavy #EF4444;
    text-decoration-thickness: 1.5px;
    text-underline-offset: 3px;
    cursor: pointer;
    border-radius: 2px;
    transition: background-color 0.15s ease;
    padding: 0 1px;
}
.error-grammar:hover {
    background-color: #FEE2E2;
}

.error-vocab {
    text-decoration: underline dotted #F59E0B;
    text-decoration-thickness: 2px;
    text-underline-offset: 3px;
    cursor: pointer;
    border-radius: 2px;
    transition: background-color 0.15s ease;
    padding: 0 1px;
}
.error-vocab:hover {
    background-color: #FEF3C7;
}

/* ── Tooltip card ── */
.tooltip-card {
    filter: drop-shadow(0 4px 20px rgba(0, 0, 0, 0.12));
}
.tooltip-card::before {
    content: '';
    position: absolute;
    top: -6px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-bottom: 6px solid white;
}

/* ── Question content styling ── */
.question-content :deep(p) {
    margin-bottom: 0.5em;
    font-size: 13px;
    line-height: 1.7;
}
.question-content :deep(ul),
.question-content :deep(ol) {
    margin-left: 1.2em;
    margin-bottom: 0.5em;
    font-size: 13px;
}
.question-content :deep(strong),
.question-content :deep(b) {
    font-weight: 600;
    color: #374151;
}

/* ── Mobile responsive ── */
@media (max-width: 768px) {
    .modal-body {
        flex-direction: column;
    }
    .left-panel {
        width: 100% !important;
        max-height: 200px;
        flex-direction: row;
        flex-wrap: wrap;
        border-right: none !important;
        border-bottom: 1px solid #f3f4f6;
    }
}
</style>
