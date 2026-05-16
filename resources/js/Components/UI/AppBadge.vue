<script setup>
defineProps({
    type: { type: String, default: 'income' },
    label: { type: String, default: '' },
});

const classes = {
    income: 'bg-[#10B981]/10 text-[#10B981]',
    expense: 'bg-[#F43F5E]/10 text-[#F43F5E]',
    transfer: 'bg-[#6366F1]/10 text-[#6366F1]',
    both: 'bg-[#3B82F6]/10 text-[#3B82F6]',
    active: 'bg-[#10B981]/10 text-[#10B981]',
    inactive: 'bg-slate-800/50 text-slate-500',
    safe: 'bg-[#10B981]/10 text-[#10B981]',
    warning: 'bg-[#F59E0B]/10 text-[#F59E0B]',
    danger: 'bg-[#F43F5E]/10 text-[#F43F5E]',
};
</script>

<template>
    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', classes[type] || classes.income]">
        {{ label || type }}
    </span>
</template>
