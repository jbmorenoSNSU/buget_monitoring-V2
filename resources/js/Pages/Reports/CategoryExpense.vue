<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Expense by Category Report');
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    data: any[];
    filters: Record<string, any>;
    persons: any[];
}>();

const { formatPeso } = useCurrency();
const month = ref(props.filters.month || new Date().getMonth() + 1);
const year = ref(props.filters.year || new Date().getFullYear());

const monthOptions = Array.from({ length: 12 }, (_, i) => ({ value: i + 1, label: new Date(2000, i).toLocaleString('en', { month: 'long' }) }));
const yearOptions = Array.from({ length: 6 }, (_, i) => ({ value: 2023 + i, label: String(2023 + i) }));

const viewMode = ref('chart'); // 'chart' or 'heatmap'

const person_id = ref(props.filters.person_id || '');

const personOptions = computed(() => {
    return [{ value: '', label: 'Everyone' }, ...props.persons.map(p => ({ value: p.id, label: p.name }))];
});

const filter = () => router.get('/reports/category-expense', { month: month.value, year: year.value, person_id: person_id.value }, { preserveState: true });

const chartData = computed(() => ({
    labels: props.data.map(d => d.category_name),
    datasets: [{ data: props.data.map(d => d.amount), backgroundColor: props.data.map(d => d.category_color), borderWidth: 2, borderColor: '#161B26' }],
}));

const totalExpense = computed(() => {
    return props.data.reduce((sum, item) => sum + Number(item.amount), 0);
});

const columns = [
    { key: 'category_name', label: 'Category' },
    { key: 'amount', label: 'Amount Spent', class: 'text-right', cellClass: 'text-right' },
    { key: 'percentage', label: '% of Total', class: 'text-right', cellClass: 'text-right' },
];

const exportUrl = (type: string) => `/reports/export/${type}?month=${month.value}&year=${year.value}&person_id=${person_id.value || ''}`;
</script>

<template>
    <Head title="Expense by Category Report" />
    <div>
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppSelect v-model="month" :options="monthOptions" label="Month" @change="filter" />
            <AppSelect v-model="year" :options="yearOptions" label="Year" @change="filter" />
            <AppSelect v-model="person_id" :options="personOptions" label="Person" @change="filter" />
            
            <div class="flex items-center bg-slate-800 rounded-lg p-1 ml-auto shrink-0">
                <button @click="viewMode = 'chart'" :class="['px-3 py-1.5 text-xs font-medium rounded-md transition-colors', viewMode === 'chart' ? 'bg-slate-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']">
                    <AppIcon name="PieChart" size="14" class="inline mr-1" /> Chart
                </button>
                <button @click="viewMode = 'heatmap'" :class="['px-3 py-1.5 text-xs font-medium rounded-md transition-colors', viewMode === 'heatmap' ? 'bg-slate-600 text-white shadow-sm' : 'text-slate-400 hover:text-slate-200']">
                    <AppIcon name="LayoutGrid" size="14" class="inline mr-1" /> Heatmap
                </button>
            </div>

            <a :href="exportUrl('category-expense-excel')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileSpreadsheet" size="18" class="text-emerald-500" />
                    Excel
                </AppButton>
            </a>
            <a :href="exportUrl('category-expense-pdf')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileText" size="18" class="text-rose-500" />
                    PDF
                </AppButton>
            </a>
        </div>

        <div v-if="viewMode === 'heatmap'" class="mb-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-3">Spending Heatmap</h3>
            <div class="flex flex-wrap gap-2 min-h-[320px]">
                <div v-for="cat in data" :key="cat.category_name"
                     class="relative flex flex-col justify-center items-center p-4 rounded-xl shadow-inner transition-all hover:-translate-y-1 cursor-default"
                     :style="{
                         width: `calc(${Math.max(15, cat.percentage)}% - 8px)`,
                         flexGrow: cat.percentage,
                         backgroundColor: cat.category_color + 'E6'
                     }">
                     <AppIcon :name="cat.category_icon" size="24" class="text-white/80 mb-1 drop-shadow-sm" />
                     <span class="font-bold text-white text-center drop-shadow-md line-clamp-1 leading-tight text-sm md:text-base">{{ cat.category_name }}</span>
                     <span class="text-xs font-semibold text-white/90 mt-1">{{ formatPeso(cat.amount) }} ({{ cat.percentage }}%)</span>
                </div>
                <div v-if="data.length === 0" class="w-full flex items-center justify-center border border-dashed border-border rounded-xl text-slate-400 py-12">
                    No expense data found for this period.
                </div>
            </div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <AppCard>
                <DoughnutChart :chartData="chartData" :height="320" :centerText="formatPeso(totalExpense)" />
            </AppCard>
            <AppTable :columns="columns" :rows="data">
                <template #cell-amount="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
                <template #cell-percentage="{ value }"><span>{{ value }}%</span></template>
            </AppTable>
        </div>
    </div>
</template>
