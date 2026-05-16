<script setup>
import { Link } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';

defineProps({
    title: { type: String, default: '' },
});

const emit = defineEmits(['toggleSidebar']);
</script>

<template>
    <header class="sticky top-0 z-30 bg-[#161B26] border-b border-[#232936] px-6 h-[65px] flex items-center justify-between">
        <div class="flex items-center gap-4">
            <button
                @click="emit('toggleSidebar')"
                class="lg:hidden p-2 rounded-lg hover:bg-[#232936] text-slate-400 hover:text-slate-200 cursor-pointer transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <h1 class="text-xl font-semibold text-[#F8FAFC]">{{ title }}</h1>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-slate-500 hidden xl:block">{{ new Date().toLocaleDateString('en-PH', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
            <Link href="/transactions/create">
                <AppButton size="sm" class="rounded-full px-3 sm:px-5 shadow-lg shadow-indigo-500/20">
                    <span class="sm:mr-1">+</span> <span class="hidden sm:inline">Add Transaction</span>
                </AppButton>
            </Link>
            <div class="w-8 h-8 rounded-full bg-[#232936] flex items-center justify-center border border-[#334155] text-xs font-bold text-slate-300">
                J
            </div>
        </div>
    </header>
</template>
