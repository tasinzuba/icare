<script setup>
import { ref } from 'vue';

const props = defineProps({
    humanEvaluationRequest: { type: Object, default: null },
    isOfflineStudent: { type: Boolean, default: false },
    hasAiFeature: { type: Boolean, default: false },
    attemptId: { type: [Number, String], default: null }
});

const emit = defineEmits(['requestEvaluation']);
const activeTab = ref(props.hasAiFeature ? 'ai' : 'human');
</script>

<template>
    <div class="space-y-6">
        <!-- Tab Switcher -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex border-b border-gray-100">
                <!-- AI Tab -->
                <button 
                    v-if="hasAiFeature"
                    @click="activeTab = 'ai'"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2.5 py-4 px-4 text-sm font-semibold transition-all relative',
                        activeTab === 'ai' 
                            ? 'text-[#C8102E]' 
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                    ]"
                >
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" /></svg>
                    AI Evaluation
                    <div v-if="activeTab === 'ai'" class="absolute bottom-0 left-4 right-4 h-0.5 bg-[#C8102E] rounded-full"></div>
                </button>
                
                <!-- AI Locked State -->
                <div v-if="!hasAiFeature" class="flex-1 flex items-center justify-center gap-2 py-4 px-4 text-gray-400 cursor-not-allowed">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                    <span class="text-sm font-medium">AI Evaluation</span>
                    <span class="text-[10px] font-bold bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full uppercase">Premium</span>
                </div>

                <!-- Human Tab -->
                <button 
                    @click="activeTab = 'human'"
                    :class="[
                        'flex-1 flex items-center justify-center gap-2.5 py-4 px-4 text-sm font-semibold transition-all relative',
                        activeTab === 'human' 
                            ? 'text-[#C8102E]' 
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'
                    ]"
                >
                    <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Expert Evaluation
                    <div v-if="activeTab === 'human'" class="absolute bottom-0 left-4 right-4 h-0.5 bg-[#C8102E] rounded-full"></div>
                </button>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- AI Content -->
                <div v-show="activeTab === 'ai' && hasAiFeature">
                    <slot name="ai-evaluation"></slot>
                </div>

                <!-- Human Content -->
                <div v-show="activeTab === 'human'">
                    <!-- Not Requested -->
                    <div v-if="!humanEvaluationRequest" class="text-center py-10">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-[#C8102E]/5 to-[#C8102E]/10 flex items-center justify-center border border-[#C8102E]/10">
                            <svg class="w-10 h-10 text-[#C8102E]/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">Expert Evaluation Available</h3>
                        <p class="text-sm text-gray-500 max-w-sm mx-auto mb-6 leading-relaxed">
                            <template v-if="isOfflineStudent">
                                Request our certified IELTS examiners to review your test and provide detailed band scores with personalized feedback.
                            </template>
                            <template v-else>
                                Get your test reviewed by experienced IELTS examiners for detailed feedback and accurate band scores.
                            </template>
                        </p>
                        <!-- Offline student: request evaluation button -->
                        <button
                            v-if="isOfflineStudent"
                            @click="emit('requestEvaluation')"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#C8102E] text-white text-sm font-semibold rounded-xl hover:bg-[#A00E27] transition-colors shadow-sm shadow-[#C8102E]/20"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            Request Expert Evaluation
                        </button>
                        <!-- Online student: choose teacher link -->
                        <a
                            v-else-if="attemptId"
                            :href="`/student/human-evaluation/${attemptId}/teachers`"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-[#C8102E] text-white text-sm font-semibold rounded-xl hover:bg-[#A00E27] transition-colors shadow-sm shadow-[#C8102E]/20"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            Choose a Teacher
                        </a>
                    </div>

                    <!-- Pending -->
                    <div v-else-if="humanEvaluationRequest.status === 'pending'" class="flex items-center gap-5 p-5 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="w-14 h-14 rounded-full bg-white flex items-center justify-center shrink-0 border border-amber-200 relative">
                            <div class="absolute inset-0 bg-amber-400 rounded-full animate-ping opacity-15"></div>
                            <svg class="w-7 h-7 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-900 mb-1">Evaluation In Progress</h3>
                            <p class="text-sm text-gray-600">Your test is being reviewed by an expert examiner. You'll receive an email notification when complete.</p>
                        </div>
                        <span class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-white border border-amber-200 text-amber-700 shrink-0">
                            <span class="w-1.5 h-1.5 mr-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                            Pending
                        </span>
                    </div>

                    <!-- Completed -->
                    <div v-else-if="humanEvaluationRequest.status === 'completed' && humanEvaluationRequest.human_evaluation" class="space-y-5">
                        <!-- Evaluator Header -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="relative">
                                    <img :src="humanEvaluationRequest.teacher?.user?.avatar_url || `https://ui-avatars.com/api/?name=${humanEvaluationRequest.teacher?.user?.name}&background=C8102E&color=fff`" 
                                         class="w-11 h-11 rounded-full border-2 border-white shadow-sm object-cover" 
                                         :alt="humanEvaluationRequest.teacher?.user?.name">
                                    <div class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-900 text-sm">{{ humanEvaluationRequest.teacher?.user?.name || 'Expert Examiner' }}</h4>
                                    <p class="text-xs text-gray-500">IELTS Expert Examiner</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 bg-gradient-to-r from-[#C8102E] to-[#8B0000] px-4 py-2 rounded-xl">
                                <span class="text-[10px] font-bold text-white/70 uppercase tracking-wider">Band</span>
                                <span class="text-xl font-black text-white">{{ humanEvaluationRequest.human_evaluation.band_score }}</span>
                            </div>
                        </div>

                        <!-- Feedback -->
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center gap-2 text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">
                                <svg class="w-4 h-4 text-[#C8102E]/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" /></svg>
                                Expert Feedback
                            </div>
                            <div class="prose prose-sm max-w-none text-gray-700 leading-relaxed" v-html="humanEvaluationRequest.human_evaluation.feedback"></div>
                        </div>
                        
                        <!-- Attachment -->
                        <div v-if="humanEvaluationRequest.human_evaluation.attachment_path" class="flex justify-end">
                            <a :href="`/storage/${humanEvaluationRequest.human_evaluation.attachment_path}`" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-medium text-gray-700 hover:border-[#C8102E]/30 hover:text-[#C8102E] transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                Download Evaluation
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
