<script setup lang="ts">
import { computed } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

interface Badge {
    id: string;
    name: string;
    color: string;
    icon: string;
}

const props = defineProps<{
    score: number;
    badges: Badge[];
}>();

const scoreColor = computed(() => {
    if (props.score >= 80) return 'text-emerald-400 stroke-emerald-400';
    if (props.score >= 50) return 'text-teal-400 stroke-teal-400';
    if (props.score >= 30) return 'text-amber-400 stroke-amber-400';
    return 'text-rose-400 stroke-rose-400';
});

const circumference = 2 * Math.PI * 40; // r=40
const strokeDashoffset = computed(() => circumference - (props.score / 100) * circumference);
</script>

<template>
    <div class="bg-card-bg border border-border rounded-2xl p-6 mb-6 flex flex-col md:flex-row items-center gap-8">
        <!-- Circular Progress -->
        <div class="relative w-32 h-32 flex-shrink-0 flex items-center justify-center">
            <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
                <circle
                    class="stroke-slate-800"
                    stroke-width="8"
                    fill="transparent"
                    r="40"
                    cx="50"
                    cy="50"
                />
                <circle
                    :class="['transition-all duration-1000 ease-out', scoreColor]"
                    stroke-width="8"
                    stroke-linecap="round"
                    fill="transparent"
                    r="40"
                    cx="50"
                    cy="50"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="strokeDashoffset"
                />
            </svg>
            <div class="absolute flex flex-col items-center justify-center">
                <span class="text-3xl font-bold" :class="scoreColor.split(' ')[0]">{{ score }}</span>
                <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Score</span>
            </div>
        </div>

        <!-- Details & Badges -->
        <div class="flex-1 w-full text-center md:text-left">
            <h3 class="text-lg font-semibold text-slate-100 mb-1">Financial Health Score</h3>
            <p class="text-sm text-slate-400 mb-4">Your score is based on your savings rate, budget adherence, and safe-to-spend buffer.</p>
            
            <div class="flex flex-wrap gap-2 justify-center md:justify-start">
                <div v-if="badges && badges.length === 0" class="text-xs text-slate-500 italic">
                    Complete budget goals to earn badges!
                </div>
                <div v-for="badge in badges" :key="badge.id" 
                    :class="['flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border', badge.color]">
                    <AppIcon :name="badge.icon" size="14" />
                    {{ badge.name }}
                </div>
            </div>
        </div>
    </div>
</template>
