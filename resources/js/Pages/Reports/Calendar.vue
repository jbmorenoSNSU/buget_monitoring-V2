<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps({
    data: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const { formatPeso } = useCurrency();

const filterMonth = ref(props.filters.month);
const filterYear = ref(props.filters.year);

const monthOptions = Array.from({ length: 12 }, (_, i) => ({ 
    value: i + 1, 
    label: new Date(2000, i).toLocaleString('en', { month: 'long' }) 
}));

const yearOptions = Array.from({ length: 5 }, (_, i) => ({ 
    value: new Date().getFullYear() - 2 + i, 
    label: String(new Date().getFullYear() - 2 + i) 
}));

const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const calendarDays = computed(() => Object.values(props.data));

const firstDay = computed(() => {
    if (calendarDays.value.length === 0) return 0;
    return calendarDays.value[0].weekday;
});

const paddingStart = computed(() => {
    // weekday is 0-6 (Sun-Sat)
    return Array.from({ length: firstDay.value }, (_, i) => i);
});

const paddingEnd = computed(() => {
    const totalCells = paddingStart.value.length + calendarDays.value.length;
    const remaining = 42 - totalCells; // 6 rows of 7
    return Array.from({ length: remaining }, (_, i) => i);
});

const applyFilters = () => {
    router.get('/reports/calendar', { 
        month: filterMonth.value, 
        year: filterYear.value 
    }, { preserveState: true });
};

const resetFilters = () => {
    filterMonth.value = new Date().getMonth() + 1;
    filterYear.value = new Date().getFullYear();
    applyFilters();
};

const getTransactionColor = (type) => {
    if (type === 'income') return 'text-[#10B981]';
    if (type === 'expense') return 'text-[#F43F5E]';
    return 'text-[#6366F1]';
};
</script>

<template>
    <AppLayout title="Financial Calendar">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-3 items-center">
                <AppSelect v-model="filterMonth" :options="monthOptions" class="w-40" @change="applyFilters" />
                <AppSelect v-model="filterYear" :options="yearOptions" class="w-32" @change="applyFilters" />
                <AppButton variant="secondary" size="sm" @click="resetFilters">This Month</AppButton>
            </div>
            
            <div class="flex gap-4">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-[#10B981]"></div>
                    <span class="text-xs text-slate-400">Income</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-[#F43F5E]"></div>
                    <span class="text-xs text-slate-400">Expense</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-[#6366F1]"></div>
                    <span class="text-xs text-slate-400">Transfer</span>
                </div>
            </div>
        </div>

        <AppCard class="p-0 overflow-hidden border-[#232936] shadow-2xl">
            <!-- Scrollable container for mobile -->
            <div class="overflow-x-auto">
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 bg-[#0F111A] min-w-[600px] md:min-w-0">
                    <!-- Header -->
                    <div v-for="day in weekDays" :key="day" 
                        class="py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-widest bg-[#161B26] border-b border-r border-[#232936] last:border-r-0">
                        {{ day }}
                    </div>

                    <!-- Padding for start of month -->
                    <div v-for="p in paddingStart" :key="'p'+p" 
                        class="min-h-[120px] md:min-h-[160px] border-b border-r border-[#232936] bg-[#161B26]/10">
                    </div>
                    
                    <!-- Day Cells -->
                    <div v-for="day in calendarDays" :key="day.date" 
                        :class="[
                            'min-h-[120px] md:min-h-[160px] border-b border-r border-[#232936] p-3 transition-all duration-300 group flex flex-col',
                            day.is_today ? 'bg-[#6366F1]/5' : 'hover:bg-[#161B26]'
                        ]">
                        <div class="flex justify-between items-start mb-2">
                            <span :class="[
                                'text-sm font-bold transition-colors',
                                day.is_today ? 'flex items-center justify-center w-7 h-7 rounded-full bg-[#6366F1] text-white shadow-lg shadow-[#6366F1]/20' : 'text-slate-500 group-hover:text-slate-300'
                            ]">
                                {{ day.day }}
                            </span>
                        </div>

                        <!-- Daily Summaries -->
                        <div class="flex-1 space-y-1.5 overflow-hidden">
                            <div v-if="day.income > 0" class="text-[10px] md:text-xs font-bold text-[#10B981] flex items-center gap-1">
                                <span class="opacity-50 font-medium">+</span>{{ formatPeso(day.income) }}
                            </div>
                            <div v-if="day.expense > 0" class="text-[10px] md:text-xs font-bold text-[#F43F5E] flex items-center gap-1">
                                <span class="opacity-50 font-medium">-</span>{{ formatPeso(day.expense) }}
                            </div>
                            
                            <!-- Itemized Mini-list -->
                            <div v-if="day.items.length > 0" class="mt-3 space-y-1">
                                <div v-for="item in day.items.slice(0, 3)" :key="item.id" 
                                    class="text-[9px] md:text-[10px] text-slate-500 truncate flex items-center gap-1.5 group-hover:text-slate-400">
                                    <div class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: item.category_color || '#94A3B8' }"></div>
                                    <span class="truncate">{{ item.description }}</span>
                                </div>
                                <div v-if="day.items.length > 3" class="text-[8px] md:text-[9px] text-slate-600 font-bold uppercase tracking-tighter pt-0.5">
                                    +{{ day.items.length - 3 }} more
                                </div>
                            </div>
                        </div>

                        <!-- Daily Balance Indicator (Subtle) -->
                        <div v-if="day.income > 0 || day.expense > 0" class="mt-auto pt-2">
                            <div class="h-0.5 w-full bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-[#10B981]" :style="{ width: (day.income / (day.income + day.expense) * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Padding for end of month -->
                    <div v-for="p in paddingEnd" :key="'pe'+p" 
                        class="min-h-[120px] md:min-h-[160px] border-b border-r border-[#232936] bg-[#161B26]/10">
                    </div>
                </div>
            </div>
        </AppCard>
    </AppLayout>
</template>

<style scoped>
/* Ensure grid items on the far right don't have a right border */
.grid > div:nth-child(7n) {
    border-right: none;
}
</style>
