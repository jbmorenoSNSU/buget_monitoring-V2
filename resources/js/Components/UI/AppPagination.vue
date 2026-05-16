<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
});

const filteredLinks = computed(() =>
    props.links.filter((_, i) => i > 0 && i < props.links.length - 1)
);

const navigate = (url) => {
    if (url) router.get(url, {}, { preserveState: true, preserveScroll: true });
};
</script>

<template>
    <nav v-if="links.length > 3" class="flex items-center justify-between">
        <div class="flex items-center gap-1">
            <button
                v-for="link in links"
                :key="link.label"
                @click="navigate(link.url)"
                :disabled="!link.url"
                :class="[
                    'px-3 py-1.5 text-sm rounded-lg transition-colors cursor-pointer',
                    link.active
                        ? 'bg-[#1E40AF] text-white'
                        : link.url
                            ? 'text-slate-400 hover:bg-slate-700'
                            : 'text-slate-600 cursor-not-allowed',
                ]"
                v-html="link.label"
            />
        </div>
    </nav>
</template>
