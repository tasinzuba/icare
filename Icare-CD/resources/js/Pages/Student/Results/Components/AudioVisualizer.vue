<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    src: { type: String, required: true },
    mimeType: { type: String, default: 'audio/webm' },
    label: { type: String, default: '' },
    compact: { type: Boolean, default: false },
});

const audio = ref(null);
const isPlaying = ref(false);
const currentTime = ref(0);
const duration = ref(0);
const isLoaded = ref(false);
const isLoading = ref(false);
const progressBar = ref(null);

// Generate consistent pseudo-random waveform bars
const barCount = computed(() => props.compact ? 40 : 60);
const waveformBars = computed(() => {
    const bars = [];
    // Use a simple seed-based approach for consistent bars per src
    let seed = 0;
    for (let i = 0; i < props.src.length; i++) {
        seed = ((seed << 5) - seed + props.src.charCodeAt(i)) | 0;
    }
    for (let i = 0; i < barCount.value; i++) {
        seed = (seed * 16807 + 7) % 2147483647;
        const h = 0.15 + (Math.abs(seed % 1000) / 1000) * 0.85;
        // Create a natural-looking waveform envelope
        const pos = i / barCount.value;
        const envelope = Math.sin(pos * Math.PI) * 0.4 + 0.6;
        bars.push(Math.max(0.12, h * envelope));
    }
    return bars;
});

const progressPercent = computed(() => {
    if (!duration.value) return 0;
    return (currentTime.value / duration.value) * 100;
});

const formatTime = (seconds) => {
    if (!seconds || isNaN(seconds)) return '0:00';
    const m = Math.floor(seconds / 60);
    const s = Math.floor(seconds % 60);
    return `${m}:${s.toString().padStart(2, '0')}`;
};

const togglePlay = () => {
    if (!audio.value) return;
    if (isPlaying.value) {
        audio.value.pause();
    } else {
        audio.value.play().catch(() => {});
    }
};

const onTimeUpdate = () => {
    if (audio.value) currentTime.value = audio.value.currentTime;
};

const onLoadedMetadata = () => {
    if (audio.value) {
        duration.value = audio.value.duration;
        isLoaded.value = true;
        isLoading.value = false;
    }
};

const onPlay = () => { isPlaying.value = true; };
const onPause = () => { isPlaying.value = false; };
const onEnded = () => { isPlaying.value = false; currentTime.value = 0; };

const onLoadStart = () => { isLoading.value = true; };
const onCanPlay = () => { isLoading.value = false; isLoaded.value = true; };

const seekTo = (e) => {
    if (!audio.value || !duration.value || !progressBar.value) return;
    const rect = progressBar.value.getBoundingClientRect();
    const percent = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
    audio.value.currentTime = percent * duration.value;
};

onBeforeUnmount(() => {
    if (audio.value) {
        audio.value.pause();
        audio.value.src = '';
    }
});
</script>

<template>
    <div :class="['audio-visualizer rounded-xl border transition-all duration-200', isPlaying ? 'border-[#C8102E]/30 bg-red-50/30 shadow-sm' : 'border-gray-200 bg-gray-50/80']">
        <!-- Hidden audio element -->
        <audio ref="audio"
               :src="src"
               preload="metadata"
               @timeupdate="onTimeUpdate"
               @loadedmetadata="onLoadedMetadata"
               @play="onPlay"
               @pause="onPause"
               @ended="onEnded"
               @loadstart="onLoadStart"
               @canplay="onCanPlay">
        </audio>

        <div :class="['flex items-center gap-3', compact ? 'px-3 py-2.5' : 'px-4 py-3']">
            <!-- Play/Pause Button -->
            <button @click="togglePlay"
                    :class="[
                        'shrink-0 rounded-full flex items-center justify-center transition-all duration-200',
                        compact ? 'w-9 h-9' : 'w-11 h-11',
                        isPlaying
                            ? 'bg-[#C8102E] text-white shadow-md shadow-red-200/50'
                            : 'bg-white text-[#C8102E] border border-gray-200 hover:border-[#C8102E]/40 hover:shadow-sm'
                    ]">
                <!-- Loading spinner -->
                <svg v-if="isLoading" :class="['animate-spin', compact ? 'w-4 h-4' : 'w-5 h-5']" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <!-- Play icon -->
                <svg v-else-if="!isPlaying" :class="compact ? 'w-4 h-4 ml-0.5' : 'w-5 h-5 ml-0.5'" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <!-- Pause icon -->
                <svg v-else :class="compact ? 'w-4 h-4' : 'w-5 h-5'" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                </svg>
            </button>

            <!-- Waveform + Progress -->
            <div class="flex-1 min-w-0">
                <!-- Waveform bars -->
                <div ref="progressBar"
                     class="relative h-8 flex items-end gap-px cursor-pointer group"
                     @click="seekTo">
                    <div v-for="(h, i) in waveformBars" :key="i"
                         class="flex-1 rounded-sm transition-colors duration-150"
                         :style="{
                             height: (h * 100) + '%',
                             minHeight: '3px',
                             backgroundColor: (i / barCount) * 100 <= progressPercent
                                 ? (isPlaying ? '#C8102E' : '#6B7280')
                                 : (isPlaying ? '#C8102E20' : '#d1d5db')
                         }">
                    </div>
                    <!-- Hover overlay line -->
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                        <div class="absolute top-0 bottom-0 w-px bg-[#C8102E]/40"
                             :style="{ left: progressPercent + '%' }"></div>
                    </div>
                </div>

                <!-- Time -->
                <div class="flex items-center justify-between mt-1">
                    <span class="text-[10px] tabular-nums text-gray-400 font-medium">{{ formatTime(currentTime) }}</span>
                    <span v-if="label" class="text-[10px] text-gray-300 truncate px-2">{{ label }}</span>
                    <span class="text-[10px] tabular-nums text-gray-400 font-medium">{{ formatTime(duration) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.audio-visualizer {
    contain: layout;
}
</style>
