<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Financial Calendar');
import { computed, ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    data: Record<string, any>;
    filters: Record<string, any>;
    persons: any[];
    accounts: any[];
}>();

const { formatPeso } = useCurrency();

const filterMonth = ref(props.filters.month);
const filterYear = ref(props.filters.year);
const filterPerson = ref(props.filters.person_id || '');
const filterAccount = ref(props.filters.account_id || '');

const monthOptions = Array.from({ length: 12 }, (_, i) => ({ 
    value: i + 1, 
    label: new Date(2000, i).toLocaleString('en', { month: 'long' }) 
}));

const yearOptions = Array.from({ length: 5 }, (_, i) => ({ 
    value: new Date().getFullYear() - 2 + i, 
    label: String(new Date().getFullYear() - 2 + i) 
}));

const personOptions = computed(() => {
    return [
        { value: '', label: 'All Persons' },
        ...props.persons.map(p => ({ value: p.id, label: p.name }))
    ];
});

const accountOptions = computed(() => {
    return [
        { value: '', label: 'All Accounts' },
        ...props.accounts.map(a => ({ value: a.id, label: a.name }))
    ];
});

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
    const query: Record<string, any> = { month: filterMonth.value, year: filterYear.value };
    if (filterPerson.value) query.person_id = filterPerson.value;
    if (filterAccount.value) query.account_id = filterAccount.value;
    
    router.get('/reports/calendar', query, { preserveState: true });
};

const resetFilters = () => {
    filterMonth.value = new Date().getMonth() + 1;
    filterYear.value = new Date().getFullYear();
    filterPerson.value = '';
    filterAccount.value = '';
    applyFilters();
};

const getTransactionColor = (type: string) => {
    if (type === 'income') return 'text-income';
    if (type === 'expense') return 'text-expense';
    return 'text-transfer';
};

// Modal Logic
const showModal = ref(false);
const selectedDay = ref<any>(null);

const openDayModal = (day: any) => {
    selectedDay.value = day;
    showModal.value = true;
};

const closeDayModal = () => {
    showModal.value = false;
    selectedDay.value = null;
};
</script>

<template>
    <Head title="Financial Calendar" />
    <div>
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div class="flex flex-wrap gap-3 items-center">
                <AppSelect v-model="filterMonth" :options="monthOptions" class="w-40" @change="applyFilters" />
                <AppSelect v-model="filterYear" :options="yearOptions" class="w-32" @change="applyFilters" />
                <AppSelect v-if="persons.length" v-model="filterPerson" :options="personOptions" class="w-40" @change="applyFilters" />
                <AppSelect v-if="accounts.length" v-model="filterAccount" :options="accountOptions" class="w-40" @change="applyFilters" />
                <AppButton variant="secondary" size="sm" @click="resetFilters">Reset</AppButton>
            </div>
            
            <div class="flex gap-4">
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-income"></div>
                    <span class="text-xs text-slate-400">Income</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-expense"></div>
                    <span class="text-xs text-slate-400">Expense</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-slate-500"></div>
                    <span class="text-xs text-slate-400">Forecast</span>
                </div>
            </div>
        </div>

        <AppCard class="p-0 overflow-hidden border-border shadow-2xl">
            <!-- Scrollable container for mobile -->
            <div class="overflow-x-auto">
                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 bg-page-bg min-w-[600px] md:min-w-0">
                    <!-- Header -->
                    <div v-for="day in weekDays" :key="day" 
                        class="py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-widest bg-card-bg border-b border-r border-border last:border-r-0">
                        {{ day }}
                    </div>

                    <!-- Padding for start of month -->
                    <div v-for="p in paddingStart" :key="'p'+p" 
                        class="min-h-[120px] md:min-h-[160px] border-b border-r border-border bg-card-bg/10">
                    </div>
                    
                    <!-- Day Cells -->
                    <div v-for="day in calendarDays" :key="day.date" 
                        @click="openDayModal(day)"
                        :class="[
                            'min-h-[120px] md:min-h-[160px] border-b border-r border-border p-3 transition-all duration-300 group flex flex-col cursor-pointer',
                            day.is_today ? 'bg-primary/5' : 'hover:bg-card-bg',
                            day.net > 0 ? 'border-b-emerald-500/20 shadow-[inset_0_-2px_0_rgba(16,185,129,0.2)]' : (day.net < 0 ? 'border-b-rose-500/20 shadow-[inset_0_-2px_0_rgba(244,63,94,0.2)]' : '')
                        ]">
                        <div class="flex justify-between items-start mb-2">
                            <span :class="[
                                'text-sm font-bold transition-colors',
                                day.is_today ? 'flex items-center justify-center w-7 h-7 rounded-full bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-500 group-hover:text-slate-300'
                            ]">
                                {{ day.day }}
                            </span>
                        </div>

                        <!-- Daily Summaries -->
                        <div class="flex-1 space-y-1.5 overflow-hidden pointer-events-none">
                            <div v-if="day.income > 0" class="text-[10px] md:text-xs font-bold text-income flex items-center gap-1">
                                <span class="opacity-50 font-medium">+</span>{{ formatPeso(day.income) }}
                            </div>
                            <div v-if="day.expense > 0" class="text-[10px] md:text-xs font-bold text-expense flex items-center gap-1">
                                <span class="opacity-50 font-medium">-</span>{{ formatPeso(day.expense) }}
                            </div>
                            
                            <!-- Itemized Mini-list -->
                            <div v-if="day.items && day.items.length > 0" class="mt-3 space-y-1">
                                <div v-for="item in day.items.slice(0, 3)" :key="item.id" 
                                    :class="[
                                        'text-[9px] md:text-[10px] truncate flex items-center gap-1.5',
                                        item.is_upcoming ? 'text-slate-600 italic' : 'text-slate-500 group-hover:text-slate-400'
                                    ]">
                                    <div class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: item.category_color || (item.is_upcoming ? '#64748b' : '#94A3B8') }"></div>
                                    <span v-if="item.is_upcoming" class="shrink-0 text-[8px] opacity-70">🕒</span>
                                    <span class="truncate">{{ item.description }}</span>
                                </div>
                                <div v-if="day.items.length > 3" class="text-[8px] md:text-[9px] text-slate-600 font-bold uppercase tracking-tighter pt-0.5">
                                    +{{ day.items.length - 3 }} more
                                </div>
                            </div>
                        </div>

                        <!-- Daily Balance Indicator (Subtle bar) -->
                        <div v-if="day.income > 0 || day.expense > 0" class="mt-auto pt-2 pointer-events-none">
                            <div class="h-0.5 w-full bg-slate-800 rounded-full overflow-hidden flex">
                                <div class="h-full bg-income" :style="{ width: (day.income / (day.income + day.expense) * 100) + '%' }"></div>
                                <div class="h-full bg-expense" :style="{ width: (day.expense / (day.income + day.expense) * 100) + '%' }"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Padding for end of month -->
                    <div v-for="p in paddingEnd" :key="'pe'+p" 
                        class="min-h-[120px] md:min-h-[160px] border-b border-r border-border bg-card-bg/10">
                    </div>
                </div>
            </div>
        </AppCard>

        <!-- Interactive Day Modal -->
        <AppModal :show="showModal" :title="selectedDay ? 'Transactions for ' + new Date(selectedDay.date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : ''" @close="closeDayModal" maxWidth="lg">
            <div v-if="selectedDay">
                <!-- Day Summary -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-card-bg border border-border p-3 rounded-lg text-center">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Income</div>
                        <div class="text-sm font-bold text-income">+{{ formatPeso(selectedDay.income) }}</div>
                    </div>
                    <div class="bg-card-bg border border-border p-3 rounded-lg text-center">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Expense</div>
                        <div class="text-sm font-bold text-expense">-{{ formatPeso(selectedDay.expense) }}</div>
                    </div>
                    <div class="bg-card-bg border border-border p-3 rounded-lg text-center">
                        <div class="text-xs text-slate-500 uppercase tracking-widest mb-1">Net</div>
                        <div :class="['text-sm font-bold', selectedDay.net >= 0 ? 'text-income' : 'text-expense']">
                            {{ selectedDay.net >= 0 ? '+' : '' }}{{ formatPeso(selectedDay.net) }}
                        </div>
                    </div>
                </div>

                <!-- Transaction List -->
                <div v-if="selectedDay.items && selectedDay.items.length > 0" class="space-y-3 max-h-[50vh] overflow-y-auto pr-2">
                    <div v-for="item in selectedDay.items" :key="item.id" 
                        :class="[
                            'flex items-center justify-between p-3 rounded-lg border',
                            item.is_upcoming ? 'bg-page-bg/50 border-slate-800 border-dashed opacity-80' : 'bg-card-bg border-border'
                        ]">
                        <div class="flex items-center gap-3 overflow-hidden">
                            <div class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: item.category_color || '#94A3B8' }"></div>
                            <div class="overflow-hidden">
                                <div class="text-sm font-medium text-slate-200 truncate flex items-center gap-2">
                                    <span v-if="item.is_upcoming" class="text-xs" title="Forecasted Recurring Transaction">🕒</span>
                                    {{ item.description }}
                                </div>
                                <div class="text-xs text-slate-500 flex items-center gap-2">
                                    <span v-if="item.category_name" class="px-1.5 py-0.5 rounded bg-slate-800 text-[10px]">{{ item.category_name }}</span>
                                    <span class="truncate">{{ item.account_name || 'No Account' }}</span>
                                </div>
                            </div>
                        </div>
                        <div :class="['text-sm font-bold shrink-0 pl-3', getTransactionColor(item.type)]">
                            {{ item.type === 'expense' ? '-' : (item.type === 'income' ? '+' : '') }}{{ formatPeso(item.amount) }}
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-8 text-slate-500 flex flex-col items-center">
                    <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p>No transactions logged for this day.</p>
                </div>
            </div>

            <template #footer>
                <AppButton variant="secondary" @click="closeDayModal">Close</AppButton>
                <!-- Pre-fill Date into Add Transaction -->
                <Link :href="'/transactions?action=add&date=' + (selectedDay ? selectedDay.date : '')">
                    <AppButton variant="primary">Log Transaction for this Day</AppButton>
                </Link>
            </template>
        </AppModal>
    </div>
</template>

<style scoped>
/* Ensure grid items on the far right don't have a right border */
.grid > div:nth-child(7n) {
    border-right: none;
}
/* Custom scrollbar for modal list */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}
.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: #334155;
    border-radius: 10px;
}
</style>
