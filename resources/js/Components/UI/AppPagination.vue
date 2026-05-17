<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: Array, default: () => [] },
    meta: { type: Object, default: null },
    clientSide: { type: Boolean, default: false },
});

const emit = defineEmits(['navigate']);

const filteredLinks = computed(() =>
    props.links.filter((_, i) => i > 0 && i < props.links.length - 1)
);

const navigate = (url) => {
    if (!url) return;
    if (props.clientSide) {
        emit('navigate', url);
    } else {
        router.get(url, {}, { preserveState: true, preserveScroll: true });
    }
};

</script>

<template>
    <div class="flex flex-col sm:flex-row justify-between items-center gap-4 w-full">
        <!-- Pagination Meta Text -->
        <div v-if="meta" class="text-sm text-slate-400">
            Showing 
            <span class="font-semibold text-slate-200">{{ meta.from || 0 }}</span> 
            to 
            <span class="font-semibold text-slate-200">{{ meta.to || 0 }}</span> 
            of 
            <span class="font-semibold text-slate-200">{{ meta.total || 0 }}</span> 
            entries
        </div>
        <div v-else></div>
        
        <!-- Pagination Links -->
        <nav v-if="links.length > 3" class="flex items-center">
            <div class="flex items-center gap-1">
                <button
                    v-for="link in links"
                    :key="link.label"
                    @click="navigate(link.url)"
                    :disabled="!link.url"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg transition-colors cursor-pointer select-none',
                        link.active
                            ? 'bg-[#1E40AF] text-white font-medium'
                            : link.url
                                ? 'text-slate-400 hover:bg-slate-700/60'
                                : 'text-slate-600 cursor-not-allowed',
                    ]"
                    v-html="link.label"
                />
            </div>
        </nav>
    </div>
</template>

