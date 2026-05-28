<script setup>
import AppIcon from '@/Components/UI/AppIcon.vue';

defineProps({
    label: { type: String, required: true },
    value: { type: String, required: true },
    accentColor: { type: String, default: '#6366F1' },
    icon: { type: String, default: '' },
    trend: { type: String, default: '' },
    trendUp: { type: Boolean, default: true },
});
</script>

<template>
    <div class="group relative bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden flex transition-all duration-300 hover:-translate-y-1 hover:border-transparent cursor-default">
        
        <!-- Subtle Glow Background on Hover -->
        <div class="absolute inset-0 opacity-0 group-hover:opacity-[0.04] transition-opacity duration-300"
             :style="{ backgroundImage: `radial-gradient(circle at left top, ${accentColor}, transparent 60%)` }">
        </div>
        
        <!-- Subtle Glow Border on Hover -->
        <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none border border-solid"
             :style="{ borderColor: accentColor + '60' }">
        </div>

        <!-- Left Accent Line -->
        <div class="w-1.5 relative z-10 transition-colors duration-300" :style="{ backgroundColor: accentColor }" />
        
        <div class="flex-1 p-5 relative z-10 flex flex-col justify-center">
            <div class="flex justify-between items-center mb-2">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">{{ label }}</p>
                
                <div v-if="icon" class="w-8 h-8 rounded-lg flex items-center justify-center opacity-80"
                     :style="{ backgroundColor: accentColor + '20', color: accentColor }">
                    <AppIcon :name="icon" class="w-4 h-4" />
                </div>
            </div>
            
            <p class="text-2xl font-bold text-slate-50 tracking-tight flex items-baseline">
                <span class="text-slate-500 font-medium text-[1.1rem] mr-1" v-if="value.includes('₱')">₱</span>
                {{ value.replace('₱', '').trim() }}
            </p>

            <div v-if="trend" class="mt-2 flex items-center gap-1.5">
                <span :class="['flex items-center justify-center w-4 h-4 rounded-full text-[10px]', trendUp ? 'bg-income/20 text-income' : 'bg-expense/20 text-expense']">
                    {{ trendUp ? '↑' : '↓' }}
                </span>
                <span :class="['text-xs font-medium', trendUp ? 'text-income' : 'text-expense']">
                    {{ trend }}
                </span>
            </div>
        </div>
    </div>
</template>
