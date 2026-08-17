<script setup>
/**
 * Review & Explanations — the post-test study view for listening and reading.
 *
 * Answers on one side, the source text on the other, one part at a time. Reviewing means moving
 * between the two constantly ("where did this answer come from?"), so they sit side by side and
 * Locate scrolls the right pane rather than opening a panel that hides the list.
 */
import { ref, computed, watch, nextTick } from 'vue';
import ReportModal from './ReportModal.vue';

const props = defineProps({
    questions: { type: Array, required: true },
    passages: { type: Array, default: () => [] },
    sectionName: { type: String, default: 'reading' },
    attemptId: { type: [Number, String], default: null },
});

const isListening = computed(() => props.sectionName === 'listening');
const sourceLabel = computed(() => (isListening.value ? 'Audioscript' : 'Passage'));

/* ---------------------------------------------------------------- parts */

const parts = computed(() => {
    const found = [...new Set(props.questions.map((q) => q.part_number ?? 1))];
    return found.sort((a, b) => a - b);
});

const activePart = ref(null);

watch(parts, (list) => {
    if (list.length && !list.includes(activePart.value)) {
        activePart.value = list[0];
    }
}, { immediate: true });

const activeIndex = computed(() => parts.value.indexOf(activePart.value));
const hasPrevious = computed(() => activeIndex.value > 0);
const hasNext = computed(() => activeIndex.value > -1 && activeIndex.value < parts.value.length - 1);

// Moving part swaps both panes at once, so send the reader back to the top of the pair.
const goToPart = (part) => {
    activePart.value = part;
    openPanel.value = null;
    nextTick(() => {
        answerPane.value?.scrollTo({ top: 0, behavior: 'smooth' });
        sourcePane.value?.scrollTo({ top: 0, behavior: 'smooth' });
    });
};

const previousPart = () => { if (hasPrevious.value) goToPart(parts.value[activeIndex.value - 1]); };
const nextPart = () => { if (hasNext.value) goToPart(parts.value[activeIndex.value + 1]); };

/* ------------------------------------------------------------ this part */

const partQuestions = computed(() =>
    props.questions.filter((q) => (q.part_number ?? 1) === activePart.value)
);

// A source text with no part of its own covers every part — that is how one full-length audio
// behaves, and how a set with a single passage behaves.
const partPassages = computed(() => {
    const scoped = props.passages.filter((p) => p.part_number === activePart.value);
    if (scoped.length) return scoped;
    return props.passages.filter((p) => !p.part_number);
});

const hasSource = computed(() => partPassages.value.length > 0);

const partStats = computed(() => {
    const stats = {};
    parts.value.forEach((p) => {
        const qs = props.questions.filter((q) => (q.part_number ?? 1) === p);
        stats[p] = { correct: qs.filter((q) => q.is_correct).length, total: qs.length };
    });
    return stats;
});

/* --------------------------------------------------------------- panels */

const answerPane = ref(null);
const sourcePane = ref(null);

// Which question currently has its explanation open. One at a time: two open notes push the list
// around and lose the reader's place.
const openPanel = ref(null);

const toggleExplanation = (question) => {
    const key = `${question.id}-explanation`;
    openPanel.value = openPanel.value === key ? null : key;
};

const isExplanationOpen = (question) => openPanel.value === `${question.id}-explanation`;

const hasExplanation = (q) => !!(q.explanation || '').toString().trim();
const hasLocation = (q) => !!(q.location_text || q.location);

/**
 * Scroll the source pane to this answer's marker and flash it.
 *
 * On a wide screen the two panes scroll independently, so the marker is centred within its own
 * pane and the answer list stays exactly where it is. Measured from bounding rects rather than
 * offsetTop, which is relative to whichever ancestor happens to be positioned.
 *
 * Stacked on a narrow screen the pane has no height cap and so does not scroll at all; there the
 * page itself has to move, which is what scrollIntoView does.
 */
const locate = (question) => {
    const marker = question.location;
    const el = marker ? document.getElementById(`marker-${marker}`) : null;

    if (!el) {
        // No marker rendered — fall back to showing the marked sentence inline.
        openPanel.value = openPanel.value === `${question.id}-location` ? null : `${question.id}-location`;
        return;
    }

    openPanel.value = null;

    const pane = sourcePane.value;
    const paneScrolls = pane && pane.scrollHeight > pane.clientHeight + 1;

    if (paneScrolls) {
        const paneRect = pane.getBoundingClientRect();
        const elRect = el.getBoundingClientRect();
        const top = pane.scrollTop + (elRect.top - paneRect.top) - pane.clientHeight / 2 + elRect.height / 2;
        pane.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
    } else {
        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Left in place rather than flashed: the point is to read the sentence, not to catch a blink.
    // Clearing first keeps exactly one marker lit, so the passage never fills up with highlights.
    document.querySelectorAll('.marker-text.marker-active').forEach((n) => n.classList.remove('marker-active'));
    el.classList.add('marker-active');
};

const isLocationOpen = (question) => openPanel.value === `${question.id}-location`;

/* --------------------------------------------------------------- report */

const reportFor = ref(null);
const openReport = (question) => { reportFor.value = question; };
const closeReport = () => { reportFor.value = null; };
</script>

<template>
    <div v-if="questions.length" class="mt-8 rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
        <!-- Header -->
        <div class="bg-[#1e3a5f] px-5 py-4 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-white flex items-center gap-2.5">
                <svg class="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                Review &amp; Explanations
            </h2>

            <!-- Part pills -->
            <div v-if="parts.length > 1" class="flex flex-wrap gap-2">
                <button v-for="part in parts" :key="part" @click="goToPart(part)"
                        :class="[
                            'px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors',
                            activePart === part ? 'bg-[#C8102E] text-white' : 'bg-white/10 text-white/80 hover:bg-white/20'
                        ]">
                    Part {{ part }}
                    <span :class="['ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full', activePart === part ? 'bg-white/25' : 'bg-white/10']">
                        {{ partStats[part]?.correct }}/{{ partStats[part]?.total }}
                    </span>
                </button>
            </div>
        </div>

        <!-- Two panes: source text on the left, answers on the right. They stack on small screens,
             where side-by-side would leave neither readable, and there the answers come first —
             a passage above them would mean scrolling past the whole text to reach question 1. -->
        <div class="flex flex-col lg:flex-row bg-white">
            <!-- Source text -->
            <!-- 60/40 rather than half each: the source text is prose and needs the line length,
                 while an answer row is a couple of words and a button. -->
            <div v-if="hasSource" class="order-2 lg:order-1 lg:w-3/5 flex flex-col border-t lg:border-t-0 lg:border-r border-gray-200">
                <div class="px-5 py-2.5 bg-gray-50 border-b border-gray-200 flex items-center justify-between gap-3">
                    <span class="text-xs font-bold uppercase tracking-wide text-gray-500">{{ sourceLabel }}</span>
                    <span class="text-[11px] text-gray-400">Locate jumps to the highlighted answer</span>
                </div>
                <div ref="sourcePane" class="overflow-y-auto p-5 lg:max-h-[calc(70vh-42px)] review-source">
                    <div v-for="passage in partPassages" :key="passage.id" class="mb-6 last:mb-0">
                        <h4 v-if="passage.title" class="text-sm font-bold text-gray-900 mb-2">{{ passage.title }}</h4>
                        <audio v-if="isListening && passage.audio_url" :src="passage.audio_url" controls
                               class="w-full mb-3" preload="none"></audio>
                        <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed"
                             v-html="passage.processed_content"></div>
                    </div>
                </div>
            </div>

            <!-- Answers. Beside the source text they take a narrow column; with no source to sit
                 next to, one card per row across the full width is mostly empty space, so they
                 pair up instead and the list is half as tall. -->
            <div ref="answerPane"
                 :class="[
                     'order-1 lg:order-2 overflow-y-auto p-4 sm:p-5',
                     hasSource
                         ? 'lg:w-2/5 lg:max-h-[70vh] space-y-3'
                         : 'w-full grid grid-cols-1 md:grid-cols-2 gap-3 content-start'
                 ]">
                <div v-for="question in partQuestions" :key="question.id"
                     class="rounded-xl border border-gray-200 px-4 py-3">
                    <!-- Correct answer -->
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="text-sm font-bold text-gray-800">{{ question.number }}</span>
                        <span class="text-xs text-gray-500">Answer:</span>
                        <span class="text-sm font-bold text-[#C8102E]" v-html="question.correct_answer"></span>
                    </div>

                    <!-- What the student put -->
                    <div class="mt-1.5 flex items-baseline gap-2 flex-wrap">
                        <span class="text-xs text-gray-500">You:</span>
                        <span v-if="!question.is_answered" class="text-sm italic text-gray-400">Skipped</span>
                        <span v-else
                              :class="[
                                  'text-sm font-medium px-2 py-0.5 rounded',
                                  question.is_correct ? 'text-emerald-800 bg-emerald-50' : 'text-[#C8102E] bg-[#C8102E]/5'
                              ]"
                              v-html="question.student_answer"></span>
                        <span v-if="question.is_answered && question.is_correct" class="text-xs font-semibold text-emerald-600">correct</span>
                    </div>

                    <!-- Actions -->
                    <div class="mt-2.5 flex flex-wrap gap-2">
                        <button v-if="hasLocation(question)" @click="locate(question)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border border-sky-200 text-sky-700 bg-sky-50 hover:bg-sky-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Locate
                        </button>
                        <button v-if="hasExplanation(question)" @click="toggleExplanation(question)"
                                :class="[
                                    'inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border transition-colors',
                                    isExplanationOpen(question) ? 'border-violet-300 text-violet-800 bg-violet-100' : 'border-violet-200 text-violet-700 bg-violet-50 hover:bg-violet-100'
                                ]">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4-.8L3 20l1.3-3.9A7.96 7.96 0 013 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                            Explain
                        </button>
                        <button @click="openReport(question)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 text-xs font-medium rounded-md border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                            Report
                        </button>
                    </div>

                    <!-- Explanation -->
                    <div v-if="isExplanationOpen(question)" class="mt-2.5 rounded-lg bg-violet-50 px-3 py-2.5">
                        <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-line">{{ question.explanation }}</p>
                    </div>

                    <!-- Location fallback, used when the marker is not on screen to scroll to -->
                    <div v-else-if="isLocationOpen(question)" class="mt-2.5 rounded-lg bg-sky-50 px-3 py-2.5">
                        <p v-if="question.location_text" class="text-sm text-gray-800 leading-relaxed"
                           style="background:#fef9c3;border-left:3px solid #eab308;padding:6px 10px;border-radius:4px;">
                            “{{ question.location_text }}”
                        </p>
                        <p v-else class="text-sm text-gray-600">Reference: {{ question.location }}</p>
                    </div>
                </div>

                <!-- col-span so the empty state spans both columns in the paired layout; it is
                     inert when the answers are a plain stack. -->
                <p v-if="!partQuestions.length" class="md:col-span-2 text-sm text-gray-500 py-6 text-center">
                    No questions in this part.
                </p>
            </div>
        </div>

        <!-- Part navigation -->
        <div v-if="parts.length > 1" class="bg-gray-100 border-t border-gray-200 px-5 py-3 flex items-center justify-between gap-3">
            <span class="text-xs font-bold uppercase tracking-wide text-gray-500">Part {{ activePart }}</span>
            <div class="flex gap-2">
                <button @click="previousPart" :disabled="!hasPrevious"
                        :class="[
                            'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg transition-colors',
                            hasPrevious ? 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                        ]">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" /></svg>
                    Previous
                </button>
                <button @click="nextPart" :disabled="!hasNext"
                        :class="[
                            'inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg transition-colors',
                            hasNext ? 'bg-[#C8102E] text-white hover:bg-[#A00E27]' : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                        ]">
                    Next
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                </button>
            </div>
        </div>

        <ReportModal :open="!!reportFor" :questionId="reportFor?.question_id" :attemptId="attemptId" @close="closeReport" />
    </div>
</template>

<style scoped>
/* The source text is injected with v-html, so the marker spans need :deep() to be reachable.

   Marked answers are deliberately invisible until asked for. Highlighting all of them on arrival
   hands the reader every answer's position at once, which is the thing they are meant to look for,
   and leaves the passage looking pre-annotated rather than like the one they sat with. */
.review-source :deep(.marker-text) {
    background: transparent;
    border-radius: 3px;
    padding: 1px 3px;
    scroll-margin-top: 1rem;
    transition: background 0.25s ease, box-shadow 0.25s ease;
}

/* Applied by Locate, and left in place so the answer can be read rather than glimpsed. Only one
   marker carries it at a time, so the next Locate moves the highlight rather than adding to it. */
.review-source :deep(.marker-text.marker-active) {
    background: #fde68a;
    box-shadow: 0 0 0 3px rgba(253, 230, 138, 0.6);
}

/* The question number appears with the highlight, naming what was just located. */
.review-source :deep(.marker-text.marker-active)::before {
    content: attr(data-marker);
    display: inline-block;
    margin-right: 5px;
    padding: 0 5px;
    border-radius: 3px;
    background: #f59e0b;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    vertical-align: middle;
}
</style>
