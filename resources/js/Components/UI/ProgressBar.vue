<script setup>
import { computed } from 'vue';

const props = defineProps({
    percent: { type: Number, default: 0 },
    label: { type: String, default: '' },
    showPercent: { type: Boolean, default: true },
    height: { type: String, default: 'h-3' },
    colorClass: { type: String, default: '' },
});

const color = computed(() => {
    if (props.colorClass) return props.colorClass;
    if (props.percent < 75) return 'bg-income';
    if (props.percent < 90) return 'bg-warning';
    return 'bg-danger';
});

const bgColor = computed(() => 'bg-page-bg');

const clampedPercent = computed(() => Math.min(props.percent, 100));
</script>

<template>
    <div>
        <div v-if="label || showPercent" class="flex justify-between items-center mb-1">
            <span v-if="label" class="text-sm text-slate-400">{{ label }}</span>
            <span v-if="showPercent" class="text-sm font-medium" :class="percent >= 90 ? 'text-danger' : percent >= 75 ? 'text-warning' : 'text-income'">
                {{ percent.toFixed(1) }}%
            </span>
        </div>
        <div :class="['w-full rounded-full overflow-hidden', bgColor, height]">
            <div
                :class="['rounded-full transition-all duration-500 ease-out', color, height]"
                :style="{ width: clampedPercent + '%' }"
            />
        </div>
    </div>
</template>
