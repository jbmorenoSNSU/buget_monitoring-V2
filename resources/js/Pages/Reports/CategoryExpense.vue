<script setup>
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

const props = defineProps({
    data: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { formatPeso } = useCurrency();
const month = ref(props.filters.month || new Date().getMonth() + 1);
const year = ref(props.filters.year || new Date().getFullYear());

const monthOptions = Array.from({ length: 12 }, (_, i) => ({ value: i + 1, label: new Date(2000, i).toLocaleString('en', { month: 'long' }) }));
const yearOptions = Array.from({ length: 6 }, (_, i) => ({ value: 2023 + i, label: String(2023 + i) }));

const filter = () => router.get('/reports/category-expense', { month: month.value, year: year.value }, { preserveState: true });

const chartData = computed(() => ({
    labels: props.data.map(d => d.category_name),
    datasets: [{ data: props.data.map(d => d.amount), backgroundColor: props.data.map(d => d.category_color), borderWidth: 2, borderColor: '#161B26' }],
}));

const columns = [
    { key: 'category_name', label: 'Category' },
    { key: 'amount', label: 'Amount Spent', class: 'text-right', cellClass: 'text-right' },
    { key: 'percentage', label: '% of Total', class: 'text-right', cellClass: 'text-right' },
];

const exportUrl = (type) => `/reports/export/${type}?month=${month.value}&year=${year.value}`;
</script>

<template>
    <AppLayout title="Expense by Category Report">
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppSelect v-model="month" :options="monthOptions" label="Month" @change="filter" />
            <AppSelect v-model="year" :options="yearOptions" label="Year" @change="filter" />
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <AppCard>
                <DoughnutChart :chartData="chartData" :height="320" />
            </AppCard>
            <AppTable :columns="columns" :rows="data">
                <template #cell-amount="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
                <template #cell-percentage="{ value }"><span>{{ value }}%</span></template>
            </AppTable>
        </div>
    </AppLayout>
</template>
