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
    datasets: [{ data: props.data.map(d => d.amount), backgroundColor: props.data.map(d => d.category_color), borderWidth: 1, borderColor: '#0F111A' }],
}));

const totalExpense = computed(() => {
    return props.data.reduce((sum, item) => sum + Number(item.amount), 0);
});

const maxAmount = computed(() => Math.max(...props.data.map(d => Number(d.amount)), 1));

const getHeatColor = (amount: number) => {
    const heatLevel = Number(amount) / maxAmount.value; // 0.0 to 1.0
    
    let r1, g1, b1, r2, g2, b2, factor;
    
    if (heatLevel < 0.5) {
        // Emerald (#10B981) to Amber (#F59E0B)
        r1 = 16; g1 = 185; b1 = 129;
        r2 = 245; g2 = 158; b2 = 11;
        factor = heatLevel * 2;
    } else {
        // Amber (#F59E0B) to Rose (#F43F5E)
        r1 = 245; g1 = 158; b1 = 11;
        r2 = 244; g2 = 63; b2 = 94;
        factor = (heatLevel - 0.5) * 2;
    }
    
    const r = Math.round(r1 + factor * (r2 - r1));
    const g = Math.round(g1 + factor * (g2 - g1));
    const b = Math.round(b1 + factor * (b2 - b1));
    
    return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
};

const columns = [
    { key: 'category_name', label: 'Category' },
    { key: 'amount', label: 'Amount Spent', class: 'text-right', cellClass: 'text-right' },
    { key: 'percentage', label: '% of Total', class: 'text-right', cellClass: 'text-right' },
];

const triggerExport = (type: string) => router.post(`/reports/export/${type}`, { month: month.value, year: year.value, person_id: person_id.value || null });
</script>

<template>
    <div>
        <Head title="Expense by Category Report" />
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

            <AppButton variant="secondary" class="gap-2" @click="triggerExport('category-expense-excel')">
                <AppIcon name="FileSpreadsheet" size="18" class="text-emerald-500" />
                Excel
            </AppButton>
            <AppButton variant="secondary" class="gap-2" @click="triggerExport('category-expense-pdf')">
                <AppIcon name="FileText" size="18" class="text-rose-500" />
                PDF
            </AppButton>
        </div>

        <div v-if="viewMode === 'heatmap'" class="mb-6">
            <h3 class="text-sm font-semibold text-slate-100 mb-1">Intensity Heatmap</h3>
            <p class="text-xs text-slate-400 mb-4">Cards are colored based on their financial impact. Brighter, more vibrant blocks indicate higher spending.</p>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                <div v-for="cat in data" :key="cat.category_name"
                     class="relative flex flex-col justify-center items-center p-5 rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-lg cursor-default border"
                     :style="{
                         backgroundColor: getHeatColor(cat.amount) + '33', // 20% opacity for background
                         borderColor: getHeatColor(cat.amount) + '66',
                         boxShadow: `inset 0 0 20px ${getHeatColor(cat.amount)}20`
                     }">
                     <AppIcon :name="cat.category_icon" size="28" class="mb-2 drop-shadow-sm" :style="{ color: getHeatColor(cat.amount) }" />
                     <span class="font-bold text-slate-50 text-center drop-shadow-md leading-tight text-sm mb-1">{{ cat.category_name }}</span>
                     <span class="text-sm font-black drop-shadow-sm" :style="{ color: getHeatColor(cat.amount) }">{{ formatPeso(cat.amount) }}</span>
                     <span class="text-[10px] font-bold px-2 py-0.5 rounded-full mt-1.5" 
                           :style="{ backgroundColor: getHeatColor(cat.amount) + '20', color: getHeatColor(cat.amount) }">
                         {{ cat.percentage }}%
                     </span>
                </div>
            </div>
            <div v-if="data.length === 0" class="w-full flex items-center justify-center border border-dashed border-border rounded-xl text-slate-400 py-12">
                No expense data found for this period.
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
