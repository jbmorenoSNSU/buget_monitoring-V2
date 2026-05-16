<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    accentColor: { type: String, default: '#6366F1' },
    icon: { type: String, default: '' },
    trend: { type: String, default: '' },
    trendUp: { type: Boolean, default: true },
});
</script>

<template>
    <div class="bg-[#161B26] border border-[#232936] rounded-xl shadow-sm overflow-hidden flex">
        <div class="w-1.5" :style="{ backgroundColor: accentColor }" />
        <div class="flex-1 p-5">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">{{ label }}</p>
            <p class="text-2xl font-bold text-[#F8FAFC]">{{ value }}</p>
            <p v-if="trend" :class="['text-xs mt-1 font-medium', trendUp ? 'text-[#10B981]' : 'text-[#F43F5E]']">
                {{ trendUp ? '↑' : '↓' }} {{ trend }}
            </p>
        </div>
    </div>
</template>
