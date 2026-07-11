<script setup>
import { computed } from 'vue';

const props = defineProps({
    testSet: { type: Object, default: null },
    attempt: { type: Object, required: true },
    bandScore: { type: [Number, String], default: 0 },
    duration: { type: String, default: 'N/A' },
    sectionName: { type: String, default: '' },
    aiEvaluated: { type: Boolean, default: false },
    aiBandScore: { type: [Number, String, null], default: null },
    // Full Test mode props
    isFullTest: { type: Boolean, default: false },
    title: { type: String, default: '' },
    hasAiScore: { type: Boolean, default: false },
    completedSections: { type: Number, default: 0 },
    totalSections: { type: Number, default: 4 },
});

const sectionConfig = {
    'listening': { label: 'Listening', emoji: '🎧' },
    'reading': { label: 'Reading', emoji: '📖' },
    'writing': { label: 'Writing', emoji: '✍️' },
    'speaking': { label: 'Speaking', emoji: '🗣️' }
};

const section = computed(() => {
    if (props.isFullTest) return { label: 'Full Test', emoji: '📋' };
    const name = props.testSet?.section?.name || 'reading';
    return sectionConfig[name] || sectionConfig['reading'];
});

const displayTitle = computed(() => props.title || props.testSet?.title || '');
const displayScore = computed(() => props.bandScore ?? props.attempt.band_score ?? 0);
const sectionType = computed(() => props.testSet?.section?.name || '');
</script>

<template>
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-6 sm:px-8 sm:py-7">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
                <!-- Left: Test Info -->
                <div class="flex-1 min-w-0">
                    <!-- Tags -->
                    <div class="flex flex-wrap items-center gap-2 mb-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-[#C8102E]/8 text-[#C8102E] text-xs font-bold rounded-full border border-[#C8102E]/15">
                            <span>{{ section.emoji }}</span>
                            <span class="capitalize">{{ isFullTest ? 'Full Test' : sectionType }}</span>
                        </span>
                        <span v-if="!isFullTest && testSet?.test_type === 'academic'" class="px-3 py-1 bg-violet-50 text-violet-700 text-xs font-semibold rounded-full border border-violet-100">Academic</span>
                        <span v-if="!isFullTest && testSet?.test_type === 'general'" class="px-3 py-1 bg-amber-50 text-amber-700 text-xs font-semibold rounded-full border border-amber-100">General</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-3 truncate max-w-xl">{{ displayTitle }}</h1>

                    <!-- Meta -->
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>{{ duration }}</span>
                        </div>
                        <span class="text-gray-300">•</span>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>{{ new Date(attempt.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</span>
                        </div>
                        <!-- Full Test: sections count -->
                        <template v-if="isFullTest">
                            <span class="text-gray-300">•</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" /></svg>
                                <span>{{ completedSections }}/{{ totalSections }} Sections</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right: Band Score — Full Test (overall) -->
                <div v-if="isFullTest" class="flex-shrink-0">
                    <div v-if="displayScore" class="w-24 h-24 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex flex-col items-center justify-center shadow-lg shadow-[#C8102E]/20">
                        <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Overall</div>
                        <div class="text-3xl font-black text-white leading-none">{{ displayScore }}</div>
                        <div v-if="hasAiScore" class="mt-1 inline-flex items-center px-1.5 py-0.5 bg-white/20 text-[8px] font-semibold text-white rounded-full">
                            <i class="fas fa-robot mr-0.5 text-[7px]"></i>AI
                        </div>
                    </div>
                    <div v-else class="w-24 h-24 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex flex-col items-center justify-center shadow-lg shadow-amber-500/20">
                        <svg class="w-6 h-6 text-white/70 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs font-bold text-white">Pending</div>
                    </div>
                </div>

                <!-- Right: Band Score (Listening/Reading) -->
                <div v-else-if="['listening', 'reading'].includes(sectionType)" class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex flex-col items-center justify-center shadow-lg shadow-[#C8102E]/20">
                        <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Band</div>
                        <div class="text-3xl font-black text-white leading-none">{{ displayScore }}</div>
                    </div>
                </div>

                <!-- Right: Band Score (Writing/Speaking) -->
                <div v-else-if="['writing', 'speaking'].includes(sectionType)" class="flex-shrink-0">
                    <!-- Human-evaluated → show final band (crimson) -->
                    <div v-if="displayScore && Number(displayScore) > 0" class="w-24 h-24 rounded-2xl bg-gradient-to-br from-[#C8102E] to-[#8B0000] flex flex-col items-center justify-center shadow-lg shadow-[#C8102E]/20">
                        <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Band</div>
                        <div class="text-3xl font-black text-white leading-none">{{ displayScore }}</div>
                    </div>
                    <!-- AI Evaluated only → show AI score (emerald) -->
                    <div v-else-if="aiEvaluated && aiBandScore" class="w-24 h-24 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 flex flex-col items-center justify-center shadow-lg shadow-emerald-500/20">
                        <div class="text-[10px] font-bold text-white/70 uppercase tracking-wider">AI Band</div>
                        <div class="text-3xl font-black text-white leading-none">{{ aiBandScore }}</div>
                    </div>
                    <!-- Not evaluated → Pending -->
                    <div v-else class="w-24 h-24 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex flex-col items-center justify-center shadow-lg shadow-amber-500/20">
                        <svg class="w-6 h-6 text-white/70 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs font-bold text-white">Pending</div>
                    </div>
                </div>
            </div>

            <!-- Slot for additional content (e.g., section scores in Full Test) -->
            <slot />
        </div>
    </div>
</template>
