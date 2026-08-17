<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';

// Extracted from QuestionItem so the review panel can offer Report without duplicating the form.
const props = defineProps({
    open: { type: Boolean, default: false },
    questionId: { type: [Number, String], default: null },
    attemptId: { type: [Number, String], default: null },
});

const emit = defineEmits(['close']);

const issueType = ref('');
const description = ref('');
const isSubmitting = ref(false);
const submitted = ref(false);
const errorMessage = ref('');

const issueTypes = [
    { value: 'wrong_statement', label: 'Wrong question statement' },
    { value: 'wrong_answer', label: 'Wrong option or answer' },
    { value: 'missing_content', label: 'Missing required content' },
    { value: 'not_related', label: 'Question not related to exam' },
    { value: 'other', label: 'Other' },
];

// Reset on open so a second report does not inherit the first one's answers or success state.
watch(() => props.open, (isOpen) => {
    if (isOpen) {
        issueType.value = '';
        description.value = '';
        submitted.value = false;
        errorMessage.value = '';
    }
});

const submit = async () => {
    if (!issueType.value) return;

    isSubmitting.value = true;
    errorMessage.value = '';
    try {
        await axios.post(`/student/test/questions/${props.questionId}/report`, {
            issue_type: issueType.value,
            description: description.value || null,
            attempt_id: props.attemptId,
        });
        submitted.value = true;
    } catch (error) {
        // Shown rather than only logged: a failure used to leave the form sitting there looking
        // as though nothing had been pressed, so a student had no way to tell it had not sent.
        console.error('Error submitting report:', error);
        errorMessage.value = error?.response?.status === 419
            ? 'Your session expired. Please refresh the page and try again.'
            : (error?.response?.data?.message || 'Could not send your report. Please try again.');
    } finally {
        isSubmitting.value = false;
    }
};
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100"
            leave-active-class="duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0"
        >
            <div v-if="open" class="fixed z-[100] inset-0 overflow-y-auto" role="dialog" aria-modal="true">
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="emit('close')"></div>

                    <div class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full mx-auto overflow-hidden">
                        <button @click="emit('close')" class="absolute top-4 right-4 z-10 text-gray-400 hover:text-gray-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>

                        <div v-if="submitted" class="px-8 py-12 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Report Submitted!</h3>
                            <p class="text-gray-500 text-sm mb-6">Thank you for helping us improve. We'll review your report shortly.</p>
                            <button @click="emit('close')" class="px-6 py-2.5 bg-gray-900 text-white text-sm font-semibold rounded-xl hover:bg-gray-800 transition-colors">Close</button>
                        </div>

                        <div v-else>
                            <div class="px-8 pt-8 pb-4 text-center">
                                <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-[#C8102E]/10 flex items-center justify-center">
                                    <svg class="w-7 h-7 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" /></svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-1">Found a mistake? Let us know!</h3>
                                <p class="text-sm text-gray-500">Please describe the mistake as detailed as possible along with your expected correction. Thank you!</p>
                            </div>

                            <div class="px-8 py-4">
                                <div class="mb-5">
                                    <label class="block text-sm font-semibold text-gray-700 mb-3">What are the obvious issues?</label>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <label v-for="issue in issueTypes" :key="issue.value"
                                               :class="[
                                                   'flex items-center gap-2.5 px-4 py-3 rounded-xl border cursor-pointer transition-all text-sm',
                                                   issueType === issue.value
                                                       ? 'border-[#C8102E] bg-[#C8102E]/5 text-[#C8102E] font-medium'
                                                       : 'border-gray-200 bg-gray-50 text-gray-700 hover:border-gray-300'
                                               ]">
                                            <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0', issueType === issue.value ? 'border-[#C8102E]' : 'border-gray-300']">
                                                <div v-if="issueType === issue.value" class="w-2.5 h-2.5 rounded-full bg-[#C8102E]"></div>
                                            </div>
                                            <input type="radio" :value="issue.value" v-model="issueType" class="sr-only">
                                            <span>{{ issue.label }}</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Describe the problem in detail ( optional )</label>
                                    <textarea v-model="description" rows="3"
                                              class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:border-[#C8102E] focus:ring-2 focus:ring-[#C8102E]/20 focus:bg-white transition-all resize-none"
                                              placeholder="Please describe the problem you are experiencing to help us improve."></textarea>
                                </div>
                            </div>

                            <p v-if="errorMessage" class="px-8 -mt-2 mb-4 text-sm text-red-600 text-center">
                                {{ errorMessage }}
                            </p>

                            <div class="px-8 pb-8 flex items-center justify-center gap-3">
                                <button @click="emit('close')" class="px-6 py-2.5 border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors">Cancel</button>
                                <button @click="submit" :disabled="!issueType || isSubmitting"
                                        :class="[
                                            'px-6 py-2.5 text-sm font-semibold rounded-xl transition-all',
                                            issueType && !isSubmitting ? 'bg-[#C8102E] text-white hover:bg-[#A00E27]' : 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                        ]">
                                    {{ isSubmitting ? 'Submitting...' : 'Submit' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
