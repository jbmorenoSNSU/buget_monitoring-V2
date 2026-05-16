<script setup>
defineProps({
    padding: { type: String, default: 'p-6' },
});
</script>

<template>
    <div :class="['bg-[#161B26] border border-[#232936] rounded-xl shadow-sm text-[#F8FAFC]', padding]">
        <slot />
    </div>
</template>
