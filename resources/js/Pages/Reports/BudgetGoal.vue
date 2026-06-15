<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Budget Goals vs Actual Report');
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
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

const person_id = ref(props.filters.person_id || '');

const personOptions = computed(() => {
    return [{ value: '', label: 'Everyone' }, ...props.persons.map(p => ({ value: p.id, label: p.name }))];
});

const filter = () => router.get('/reports/budget-goal', { month: month.value, year: year.value, person_id: person_id.value }, { preserveState: true });

const columns = [
    { key: 'category_name', label: 'Category' },
    { key: 'owner', label: 'Owner' },
    { key: 'limit_amount', label: 'Budget Limit', class: 'text-right', cellClass: 'text-right' },
    { key: 'actual_spent', label: 'Actual Spent', class: 'text-right', cellClass: 'text-right' },
    { key: 'variance', label: 'Variance', class: 'text-right', cellClass: 'text-right' },
    { key: 'percent', label: '% Used', class: 'text-right', cellClass: 'text-right' },
];

const exportUrl = (type: string) => `/reports/export/${type}?month=${month.value}&year=${year.value}&person_id=${person_id.value || ''}`;
</script>

<template>
    <Head title="Budget Goals vs Actual Report" />
    <div>
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppSelect v-model="month" :options="monthOptions" label="Month" @change="filter" />
            <AppSelect v-model="year" :options="yearOptions" label="Year" @change="filter" />
            <AppSelect v-model="person_id" :options="personOptions" label="Person" @change="filter" />
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
            <template #cell-owner="{ row }">
                <span v-if="row.person_name" class="font-medium" :style="{ color: row.person_color || '#94A3B8' }">{{ row.person_name }}</span>
                <span v-else class="text-slate-500 italic">Shared</span>
            </template>
            <template #cell-limit_amount="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-actual_spent="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
            <template #cell-variance="{ row }">
                <span :class="['font-semibold', row.variance >= 0 ? 'text-success' : 'text-danger']">{{ formatPeso(row.variance) }}</span>
            </template>
            <template #cell-percent="{ row }">
                <span :class="['font-semibold', row.status === 'safe' ? 'text-success' : row.status === 'warning' ? 'text-warning' : 'text-danger']">
                    {{ row.percent }}%
                </span>
            </template>
        </AppTable>
    </div>
</template>
