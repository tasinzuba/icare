<template>
    <div class="bg-red-50 border border-red-200 rounded-xl p-6 mb-6">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-red-100 rounded-lg shrink-0 mt-0.5">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-red-900 mb-1">Warning: No Answers Submitted!</h3>
                <p class="text-red-700 leading-relaxed text-sm">
                    You have submitted the test without answering any questions. Consequently, your score is 0. Please ensure you answer the questions next time to receive a fair evaluation.
                </p>
                <!-- Retake button link can be added here if needed by parent -->
                <slot name="actions"></slot>
            </div>
        </div>
    </div>
</template>
