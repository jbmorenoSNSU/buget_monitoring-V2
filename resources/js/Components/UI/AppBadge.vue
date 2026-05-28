<script setup>
defineProps({
    type: { type: String, default: 'income' },
    label: { type: String, default: '' },
});

const classes = {
    income: 'bg-income/10 text-income',
    expense: 'bg-expense/10 text-expense',
    transfer: 'bg-transfer/10 text-transfer',
    both: 'bg-blue-500/10 text-blue-400',
    active: 'bg-success/10 text-success',
    inactive: 'bg-slate-800/50 text-slate-500',
    safe: 'bg-success/10 text-success',
    warning: 'bg-warning/10 text-warning',
    danger: 'bg-danger/10 text-danger',
};
</script>

<template>
    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', classes[type] || classes.income]">
        {{ label || type }}
    </span>
</template>
