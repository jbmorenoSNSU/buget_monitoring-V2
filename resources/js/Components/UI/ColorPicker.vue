<script setup>
defineProps({
    modelValue: { type: String, default: '#6366F1' },
    label: { type: String, default: 'Color' },
});

const emit = defineEmits(['update:modelValue']);

const presetColors = [
    '#6366F1', '#4F46E5', '#10B981', '#059669',
    '#F43F5E', '#E11D48', '#F59E0B', '#D97706',
    '#0EA5E9', '#0D9488', '#8B5CF6', '#D946EF',
    '#94A3B8', '#64748B', '#475569', '#334155',
];
</script>

<template>
    <div>
        <label class="block text-sm font-medium text-slate-300 mb-2">{{ label }}</label>
        <div class="flex flex-wrap gap-2">
            <button
                v-for="color in presetColors"
                :key="color"
                type="button"
                @click="emit('update:modelValue', color)"
                :class="[
                    'w-8 h-8 rounded-lg border-2 transition-all duration-200 cursor-pointer hover:scale-110',
                    modelValue === color ? 'border-white ring-2 ring-offset-2 ring-offset-card-bg ring-primary scale-110' : 'border-transparent',
                ]"
                :style="{ backgroundColor: color }"
            />
        </div>
    </div>
</template>
