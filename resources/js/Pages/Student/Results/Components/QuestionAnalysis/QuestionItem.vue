<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    question: {
        type: Object,
        required: true
    },
    index: {
        type: Number,
        required: true
    },
    attemptId: {
        type: [Number, String],
        default: null
    }
});

// Report modal
const isReportModalOpen = ref(false);
const reportIssueType = ref('');
const reportDescription = ref('');
const isSubmittingReport = ref(false);
const reportSubmitted = ref(false);

const issueTypes = [
    { value: 'wrong_statement', label: 'Wrong question statement' },
    { value: 'wrong_answer', label: 'Wrong option or answer' },
    { value: 'missing_content', label: 'Missing required content' },
    { value: 'not_related', label: 'Question not related to exam' },
    { value: 'other', label: 'Other' },
];

const openReportModal = () => {
    isReportModalOpen.value = true;
    reportIssueType.value = '';
    reportDescription.value = '';
    reportSubmitted.value = false;
};

const closeReportModal = () => {
    isReportModalOpen.value = false;
};

const submitReport = async () => {
    if (!reportIssueType.value) return;

    isSubmittingReport.value = true;
    try {
        await axios.post(`/student/test/questions/${props.question.question_id}/report`, {
            issue_type: reportIssueType.value,
            description: reportDescription.value || null,
            attempt_id: props.attemptId,
        });
        reportSubmitted.value = true;
    } catch (error) {
        console.error("Error submitting report:", error);
    } finally {
        isSubmittingReport.value = false;
    }
};
</script>

<template>
    <!-- Desktop Row -->
    <div :class="[
        'hidden md:grid grid-cols-12 gap-0 items-center px-4 py-3.5 transition-colors duration-150',
        question.is_correct ? 'hover:bg-emerald-50/40' : 'hover:bg-[#C8102E]/5',
        !question.is_answered ? 'hover:bg-gray-50' : ''
    ]">
        <!-- # Number -->
        <div class="col-span-1 text-center">
            <span class="text-sm font-bold text-gray-700">{{ question.number }}</span>
        </div>

        <!-- Status Icon -->
        <div class="col-span-1 flex justify-center">
            <div v-if="question.is_correct" class="w-7 h-7 rounded-full bg-emerald-100 flex items-center justify-center">
                <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
            </div>
            <div v-else-if="question.is_answered" class="w-7 h-7 rounded-full bg-[#C8102E]/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
            </div>
            <div v-else class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center">
                <span class="w-3 h-0.5 bg-gray-400 rounded-full"></span>
            </div>
        </div>

        <!-- Your Answer -->
        <div class="col-span-4 pl-2">
            <div :class="[
                'text-sm font-medium truncate max-w-full px-3 py-1.5 rounded-md',
                question.is_correct ? 'text-emerald-800 bg-emerald-50' : '',
                question.is_answered && !question.is_correct ? 'text-[#C8102E] bg-[#C8102E]/5' : '',
                !question.is_answered ? 'text-gray-400 italic' : ''
            ]">
                <span v-if="question.is_answered" v-html="question.student_answer"></span>
                <span v-else>Skipped</span>
            </div>
        </div>

        <!-- Correct Answer -->
        <div class="col-span-4 pl-2">
            <div class="text-sm font-medium text-gray-800 bg-gray-50 px-3 py-1.5 rounded-md truncate max-w-full">
                <span v-html="question.correct_answer"></span>
            </div>
        </div>

        <!-- Actions -->
        <div class="col-span-2 flex justify-center gap-1.5">
            <button @click="openReportModal" class="p-2 rounded-lg text-amber-500 hover:bg-amber-50 hover:text-amber-600 transition-colors" title="Report a mistake">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
            </button>
        </div>
    </div>

    <!-- Mobile Card -->
    <div :class="[
        'md:hidden p-4',
        question.is_correct ? 'hover:bg-emerald-50/30' : 'hover:bg-red-50/30',
    ]">
        <div class="flex items-start gap-3">
            <!-- Number + Status -->
            <div class="flex flex-col items-center gap-1 pt-0.5">
                <span class="text-xs font-bold text-gray-500">Q{{ question.number }}</span>
                <div v-if="question.is_correct" class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                </div>
                <div v-else-if="question.is_answered" class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                </div>
                <div v-else class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                    <span class="w-2.5 h-0.5 bg-gray-400 rounded-full"></span>
                </div>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Your Answer</div>
                        <div :class="[
                            'text-sm font-medium',
                            question.is_correct ? 'text-emerald-700' : '',
                            question.is_answered && !question.is_correct ? 'text-red-600' : '',
                            !question.is_answered ? 'text-gray-400 italic' : ''
                        ]">
                            <span v-if="question.is_answered" v-html="question.student_answer"></span>
                            <span v-else>Skipped</span>
                        </div>
                    </div>
                    <div class="flex gap-1 shrink-0">
                        <button @click="openReportModal" class="p-1.5 rounded-md text-amber-500 hover:bg-amber-50">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                        </button>
                    </div>
                </div>
                <div v-if="!question.is_correct" class="mt-1.5">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Correct</div>
                    <div class="text-sm font-medium text-gray-800" v-html="question.correct_answer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isReportModalOpen" class="fixed z-[100] inset-0 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeReportModal"></div>

                    <!-- Modal Content -->
                    <Transition
                        appear
                        enter-active-class="duration-300 ease-out"
                        enter-from-class="opacity-0 scale-95 translate-y-4"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="duration-200 ease-in"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-4"
                    >
                        <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-auto overflow-hidden transform transition-all">
                            <!-- Close button -->
                            <button @click="closeReportModal" class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-600 hover:rotate-90 transition-all duration-200">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>

                            <!-- Success State -->
                            <Transition
                                enter-active-class="duration-400 ease-out"
                                enter-from-class="opacity-0 scale-90"
                                enter-to-class="opacity-100 scale-100"
                                mode="out-in"
                            >
                                <div v-if="reportSubmitted" key="success" class="px-8 py-12 text-center">
                                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center animate-[bounceIn_0.5s_ease-out]">
                                        <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-900 mb-2">Report Submitted!</h3>
                                    <p class="text-gray-500 text-sm mb-6">Thank you for helping us improve. We'll review your report shortly.</p>
                                    <button @click="closeReportModal" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 hover:scale-105 active:scale-95 transition-all">
                                        Close
                                    </button>
                                </div>

                                <!-- Form State -->
                                <div v-else key="form">
                                    <!-- Header -->
                                    <div class="px-8 pt-8 pb-4 text-center">
                                        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-[#C8102E]/10 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-1">Found a mistake? Let us know!</h3>
                                        <p class="text-sm text-gray-500">Please describe the mistake as detailed as possible along with your expected correction. Thank you!</p>
                                    </div>

                                    <!-- Body -->
                                    <div class="px-8 py-4">
                                        <!-- Issue Type -->
                                        <div class="mb-5">
                                            <label class="block text-sm font-semibold text-gray-700 mb-3">What are the obvious issues?</label>
                                            <div class="grid grid-cols-2 gap-2.5">
                                                <label
                                                    v-for="issue in issueTypes"
                                                    :key="issue.value"
                                                    :class="[
                                                        'flex items-center gap-2.5 px-4 py-3 rounded-xl border cursor-pointer transition-all duration-200 text-sm',
                                                        reportIssueType === issue.value
                                                            ? 'border-[#C8102E] bg-[#C8102E]/5 text-[#C8102E] font-medium shadow-sm shadow-[#C8102E]/10 scale-[1.02]'
                                                            : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-gray-300 hover:bg-gray-100'
                                                    ]"
                                                >
                                                    <div :class="[
                                                        'w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 transition-all duration-200',
                                                        reportIssueType === issue.value ? 'border-[#C8102E]' : 'border-gray-300'
                                                    ]">
                                                        <Transition
                                                            enter-active-class="duration-200 ease-out"
                                                            enter-from-class="scale-0"
                                                            enter-to-class="scale-100"
                                                            leave-active-class="duration-150 ease-in"
                                                            leave-from-class="scale-100"
                                                            leave-to-class="scale-0"
                                                        >
                                                            <div v-if="reportIssueType === issue.value" class="w-2.5 h-2.5 rounded-full bg-[#C8102E]"></div>
                                                        </Transition>
                                                    </div>
                                                    <input type="radio" :value="issue.value" v-model="reportIssueType" class="sr-only">
                                                    <span>{{ issue.label }}</span>
                                                </label>
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="mb-6">
                                            <label class="block text-sm font-semibold text-gray-700 mb-2">Describe the problem in detail ( optional )</label>
                                            <textarea
                                                v-model="reportDescription"
                                                rows="3"
                                                class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:border-[#C8102E] focus:ring-2 focus:ring-[#C8102E]/20 focus:bg-white focus:shadow-sm transition-all duration-200 resize-none"
                                                placeholder="Please describe the problem you are experiencing to help us improve."
                                            ></textarea>
                                        </div>
                                    </div>

                                    <!-- Footer -->
                                    <div class="px-8 pb-8 flex items-center justify-center gap-3">
                                        <button @click="closeReportModal" class="px-6 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 hover:border-gray-300 active:scale-95 transition-all duration-200">
                                            Cancel
                                        </button>
                                        <button
                                            @click="submitReport"
                                            :disabled="!reportIssueType || isSubmittingReport"
                                            :class="[
                                                'px-6 py-2.5 text-sm font-semibold rounded-xl transition-all duration-200',
                                                reportIssueType && !isSubmittingReport
                                                    ? 'bg-[#C8102E] text-white hover:bg-[#A00E27] hover:scale-105 active:scale-95 shadow-sm shadow-[#C8102E]/20'
                                                    : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                            ]"
                                        >
                                            <template v-if="isSubmittingReport">
                                                <svg class="animate-spin -ml-1 mr-1.5 h-4 w-4 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Submitting...
                                            </template>
                                            <template v-else>Submit</template>
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </Transition>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
