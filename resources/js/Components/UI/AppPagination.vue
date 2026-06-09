<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    links: { type: [Array, Object], default: () => [] },
    meta: { type: Object, default: null },
    clientSide: { type: Boolean, default: false },
});

const emit = defineEmits(['navigate']);

const isCursorPagination = computed(() => {
    return props.meta && props.meta.total === undefined;
});

const arrayLinks = computed(() => {
    if (Array.isArray(props.links)) return props.links;
    return [];
});

const cursorLinks = computed(() => {
    if (!Array.isArray(props.links) && props.links !== null) {
        return props.links;
    }
    return {};
});

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
        <div v-if="meta && !isCursorPagination" class="text-sm text-slate-400">
            Showing 
            <span class="font-semibold text-slate-200">{{ meta.from || 0 }}</span> 
            to 
            <span class="font-semibold text-slate-200">{{ meta.to || 0 }}</span> 
            of 
            <span class="font-semibold text-slate-200">{{ meta.total || 0 }}</span> 
            entries
        </div>
        <div v-else-if="meta && isCursorPagination" class="text-sm text-slate-400">
            <span v-if="arrayLinks.length > 0 || cursorLinks.next || cursorLinks.prev">Navigating entries</span>
        </div>
        <div v-else></div>
        
        <!-- Pagination Links (LengthAware) -->
        <nav v-if="!isCursorPagination && arrayLinks.length > 3" class="flex items-center">
            <div class="flex items-center gap-1">
                <button
                    v-for="(link, i) in arrayLinks"
                    :key="i"
                    @click="navigate(link.url)"
                    :disabled="!link.url"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg transition-colors cursor-pointer select-none',
                        link.active
                            ? 'bg-primary text-white font-medium'
                            : link.url
                                ? 'text-slate-400 hover:bg-slate-700/60'
                                : 'text-slate-600 cursor-not-allowed',
                    ]"
                    v-html="link.label"
                />
            </div>
        </nav>

        <!-- Pagination Links (Cursor) -->
        <nav v-if="isCursorPagination && (cursorLinks.prev || cursorLinks.next)" class="flex items-center">
            <div class="flex items-center gap-2">
                <button
                    @click="navigate(cursorLinks.prev)"
                    :disabled="!cursorLinks.prev"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg transition-colors select-none',
                        cursorLinks.prev
                            ? 'text-slate-200 bg-slate-800 hover:bg-slate-700/60 cursor-pointer'
                            : 'text-slate-600 bg-slate-800/50 cursor-not-allowed',
                    ]"
                >
                    &laquo; Previous
                </button>
                <button
                    @click="navigate(cursorLinks.next)"
                    :disabled="!cursorLinks.next"
                    :class="[
                        'px-3 py-1.5 text-sm rounded-lg transition-colors select-none',
                        cursorLinks.next
                            ? 'text-slate-200 bg-slate-800 hover:bg-slate-700/60 cursor-pointer'
                            : 'text-slate-600 bg-slate-800/50 cursor-not-allowed',
                    ]"
                >
                    Next &raquo;
                </button>
            </div>
        </nav>
    </div>
</template>

