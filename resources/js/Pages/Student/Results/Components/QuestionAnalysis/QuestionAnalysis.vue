<script setup>
import { ref, computed, watch } from 'vue';
import QuestionItem from './QuestionItem.vue';

const props = defineProps({
    questions: {
        type: Array,
        required: true
    },
    attemptId: {
        type: [Number, String],
        default: null
    }
});

const parts = computed(() => {
    if (!props.questions || props.questions.length === 0) return [];
    return [...new Set(props.questions.map(q => q.part_number))].sort((a, b) => a - b);
});

const activePart = ref(null);

watch(parts, (newParts) => {
    if (newParts.length > 0 && !activePart.value) {
        activePart.value = newParts[0];
    }
}, { immediate: true });

const displayedQuestions = computed(() => {
    return props.questions.filter(q => q.part_number === activePart.value);
});

const partStats = computed(() => {
    const stats = {};
    parts.value.forEach(p => {
        const qs = props.questions.filter(q => q.part_number === p);
        stats[p] = {
            correct: qs.filter(q => q.is_correct).length,
            total: qs.length
        };
    });
    return stats;
});
</script>

<template>
    <div v-if="questions.length > 0" class="mt-8">
        <!-- Header -->
        <div class="bg-white rounded-t-xl border border-gray-200 border-b-0 px-6 py-5">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex items-center justify-center shadow-lg shadow-[#C8102E]/20">
                        <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Detailed Analysis</h2>
                        <p class="text-xs text-gray-500">Review each question with correct answers</p>
                    </div>
                </div>
                
                <!-- Part Tabs as Pills -->
                <div v-if="parts.length > 1" class="flex gap-2">
                    <button 
                        v-for="part in parts" 
                        :key="part"
                        @click="activePart = part"
                        :class="[
                            'relative px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200',
                            activePart === part 
                                ? 'bg-[#C8102E] text-white shadow-md shadow-[#C8102E]/30' 
                                : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        ]"
                    >
                        Part {{ part }}
                        <span :class="[
                            'ml-1.5 text-[10px] font-bold px-1.5 py-0.5 rounded-full',
                            activePart === part ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-500'
                        ]">
                            {{ partStats[part]?.correct }}/{{ partStats[part]?.total }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Table Header -->
        <div class="hidden md:grid grid-cols-12 gap-0 bg-gray-50 border border-gray-200 border-b-0 px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">
            <div class="col-span-1 text-center">#</div>
            <div class="col-span-1 text-center">Status</div>
            <div class="col-span-4 pl-2">Your Answer</div>
            <div class="col-span-4 pl-2">Correct Answer</div>
            <div class="col-span-2 text-center">Actions</div>
        </div>

        <!-- Question Rows -->
        <div class="border border-gray-200 rounded-b-xl overflow-hidden bg-white divide-y divide-gray-100">
            <QuestionItem
                v-for="(question, index) in displayedQuestions"
                :key="question.id"
                :question="question"
                :index="index"
                :attemptId="attemptId"
            />
        </div>
    </div>
</template>
