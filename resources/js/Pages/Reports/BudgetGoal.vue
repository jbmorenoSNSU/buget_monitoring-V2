<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
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

const filter = () => router.get('/reports/budget-goal', { month: month.value, year: year.value }, { preserveState: true });

const columns = [
    { key: 'category_name', label: 'Category' },
    { key: 'limit_amount', label: 'Budget Limit', class: 'text-right', cellClass: 'text-right' },
    { key: 'actual_spent', label: 'Actual Spent', class: 'text-right', cellClass: 'text-right' },
    { key: 'variance', label: 'Variance', class: 'text-right', cellClass: 'text-right' },
    { key: 'percent', label: '% Used', class: 'text-right', cellClass: 'text-right' },
];

const exportUrl = (type) => `/reports/export/${type}?month=${month.value}&year=${year.value}`;
</script>

<template>
    <AppLayout title="Budget Goals vs Actual Report">
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppSelect v-model="month" :options="monthOptions" label="Month" @change="filter" />
            <AppSelect v-model="year" :options="yearOptions" label="Year" @change="filter" />
            <a :href="exportUrl('budget-goal-excel')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileSpreadsheet" size="18" class="text-emerald-500" />
                    Excel
                </AppButton>
            </a>
            <a :href="exportUrl('budget-goal-pdf')" class="inline-block">
                <AppButton variant="secondary" class="gap-2">
                    <AppIcon name="FileText" size="18" class="text-rose-500" />
                    PDF
                </AppButton>
            </a>
        </div>

        <AppTable :columns="columns" :rows="data">
            <template #cell-limit_amount="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-actual_spent="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-variance="{ row }">
                <span :class="['font-semibold', row.variance >= 0 ? 'text-[#10B981]' : 'text-[#F43F5E]']">{{ formatPeso(row.variance) }}</span>
            </template>
            <template #cell-percent="{ row }">
                <span :class="['font-semibold', row.status === 'safe' ? 'text-[#10B981]' : row.status === 'warning' ? 'text-[#F59E0B]' : 'text-[#F43F5E]']">
                    {{ row.percent }}%
                </span>
            </template>
        </AppTable>
    </AppLayout>
</template>
