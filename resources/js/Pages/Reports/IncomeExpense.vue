<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Income vs Expense Report');
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    data: any[];
    filters: Record<string, any>;
    persons: any[];
}>();

const { formatPeso } = useCurrency();
const from = ref(props.filters.from || '');
const to = ref(props.filters.to || '');
const person_id = ref(props.filters.person_id || '');

const personOptions = computed(() => {
    return [{ value: '', label: 'Everyone' }, ...props.persons.map(p => ({ value: p.id, label: p.name }))];
});

const filter = () => router.get('/reports/income-expense', { from: from.value, to: to.value, person_id: person_id.value }, { preserveState: true });

const chartData = computed(() => ({
    labels: props.data.map(d => d.label),
    datasets: [
        { label: 'Income', data: props.data.map(d => d.income), backgroundColor: '#10B981', borderRadius: 6 },
        { label: 'Expense', data: props.data.map(d => d.expense), backgroundColor: '#F43F5E', borderRadius: 6 },
    ],
}));

const columns = [
    { key: 'label', label: 'Month' },
    { key: 'income', label: 'Income', class: 'text-right', cellClass: 'text-right' },
    { key: 'expense', label: 'Expense', class: 'text-right', cellClass: 'text-right' },
    { key: 'net', label: 'Net Savings', class: 'text-right', cellClass: 'text-right' },
];

const exportUrl = (type: string) => `/reports/export/${type}?from=${from.value}&to=${to.value}&person_id=${person_id.value || ''}`;
</script>

<template>
    <Head title="Income vs Expense Report" />
    <div>
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppInput v-model="from" label="From" type="date" />
            <AppInput v-model="to" label="To" type="date" />
            <AppSelect v-model="person_id" :options="personOptions" label="Person" />
            <AppButton @click="filter">Apply</AppButton>
            <a :href="exportUrl('income-expense-excel')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileSpreadsheet" size="18" class="text-emerald-500" />
                    Excel
                </AppButton>
            </a>
            <a :href="exportUrl('income-expense-pdf')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileText" size="18" class="text-rose-500" />
                    PDF
                </AppButton>
            </a>
        </div>

        <AppCard class="mb-6">
            <BarChart :chartData="chartData" :height="320" />
        </AppCard>

        <AppTable :columns="columns" :rows="data">
            <template #cell-income="{ value }"><span class="text-income font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-expense="{ value }"><span class="text-expense font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-net="{ value }"><span :class="['font-semibold', value >= 0 ? 'text-income' : 'text-expense']">{{ formatPeso(value) }}</span></template>
        </AppTable>
    </div>
</template>
