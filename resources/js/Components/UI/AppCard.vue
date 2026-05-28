<script setup>
defineProps({
    padding: { type: String, default: 'p-6' },
});
</script>

<template>
    <div :class="['bg-card-bg border border-border rounded-xl shadow-sm text-slate-50', padding]">
        <slot />
    </div>
</template>
