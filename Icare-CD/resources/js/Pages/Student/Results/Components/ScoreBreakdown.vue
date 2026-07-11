<script setup>
import { computed } from 'vue';

const props = defineProps({
    questionsAnalysis: { type: Array, required: true },
    totalQuestions: { type: Number, required: true },
    attempted: { type: Number, required: true },
    correctAnswers: { type: Number, required: true }
});

const correctPct = computed(() => props.totalQuestions > 0 ? (props.correctAnswers / props.totalQuestions) * 100 : 0);
const wrongCount = computed(() => props.attempted - props.correctAnswers);
const wrongPct = computed(() => props.totalQuestions > 0 ? (wrongCount.value / props.totalQuestions) * 100 : 0);
const skippedCount = computed(() => props.totalQuestions - props.attempted);
const skippedPct = computed(() => props.totalQuestions > 0 ? (skippedCount.value / props.totalQuestions) * 100 : 0);
</script>

<template>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-[#C8102E]/10 flex items-center justify-center">
                <svg class="w-4 h-4 text-[#C8102E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <h2 class="text-base font-bold text-gray-900">Score Breakdown</h2>
        </div>
        
        <div class="p-6">
            <!-- Stats Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="bg-gray-50 rounded-xl p-4 text-center border border-gray-100">
                    <div class="text-2xl font-black text-gray-900">{{ totalQuestions }}</div>
                    <div class="text-xs font-semibold text-gray-500 mt-1">Total</div>
                </div>
                <div class="bg-blue-50 rounded-xl p-4 text-center border border-blue-100">
                    <div class="text-2xl font-black text-blue-700">{{ attempted }}</div>
                    <div class="text-xs font-semibold text-blue-600 mt-1">Attempted</div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-4 text-center border border-emerald-100">
                    <div class="text-2xl font-black text-emerald-700">{{ correctAnswers }}</div>
                    <div class="text-xs font-semibold text-emerald-600 mt-1">Correct</div>
                </div>
                <div class="bg-[#C8102E]/5 rounded-xl p-4 text-center border border-[#C8102E]/10">
                    <div class="text-2xl font-black text-[#C8102E]">{{ wrongCount }}</div>
                    <div class="text-xs font-semibold text-[#C8102E]/70 mt-1">Wrong</div>
                </div>
            </div>

            <!-- Progress Bar -->
            <div>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="font-semibold text-gray-700">Accuracy</span>
                    <span class="font-bold text-[#C8102E]">{{ correctPct.toFixed(1) }}%</span>
                </div>
                <div class="h-3 bg-gray-100 rounded-full overflow-hidden flex">
                    <div class="bg-emerald-500 h-full transition-all duration-700 ease-out" :style="{ width: correctPct + '%' }"></div>
                    <div class="bg-[#C8102E] h-full transition-all duration-700 ease-out" :style="{ width: wrongPct + '%' }"></div>
                    <div class="bg-gray-300 h-full transition-all duration-700 ease-out" :style="{ width: skippedPct + '%' }"></div>
                </div>
                <div class="flex items-center gap-5 mt-3 text-xs font-medium text-gray-500">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Correct {{ correctPct.toFixed(0) }}%
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-[#C8102E]"></span> Wrong {{ wrongPct.toFixed(0) }}%
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-gray-300"></span> Skipped {{ skippedPct.toFixed(0) }}%
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
