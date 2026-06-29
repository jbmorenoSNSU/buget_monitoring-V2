<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Account Statement Report');
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    data: Record<string, any>;
    filters: Record<string, any>;
    accounts: any[];
}>();

const { formatPeso } = useCurrency();
const account_id = ref(props.filters.account_id || '');
const from = ref(props.filters.from || '');
const to = ref(props.filters.to || '');

const accountOptions = props.accounts.map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name,
}));

const filter = () => router.get('/reports/account-statement', { account_id: account_id.value, from: from.value, to: to.value }, { preserveState: true });

const columns = [
    { key: 'date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'category', label: 'Category' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right' },
    { key: 'balance', label: 'Balance', class: 'text-right', cellClass: 'text-right' },
];

const triggerExport = (type: string) => router.post(`/reports/export/${type}`, { account_id: account_id.value, from: from.value, to: to.value });
</script>

<template>
    <Head title="Account Statement Report" />
    <div>
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppSelect v-model="account_id" :options="accountOptions" label="Account" @change="filter" />
            <AppInput v-model="from" label="From" type="date" />
            <AppInput v-model="to" label="To" type="date" />
            <AppButton @click="filter">Apply</AppButton>
            <AppButton variant="secondary" class="gap-2" @click="triggerExport('account-statement-excel')">
                <AppIcon name="FileSpreadsheet" size="18" class="text-emerald-500" />
                Excel
            </AppButton>
            <AppButton variant="secondary" class="gap-2" @click="triggerExport('account-statement-pdf')">
                <AppIcon name="FileText" size="18" class="text-rose-500" />
                PDF
            </AppButton>
        </div>

        <template v-if="data">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <StatCard label="Opening Balance" :value="formatPeso(data.opening_balance)" accentColor="#6366F1" />
                <StatCard label="Closing Balance" :value="formatPeso(data.closing_balance)" accentColor="#10B981" />
            </div>

            <AppTable :columns="columns" :rows="data.transactions">
                <template #cell-type="{ value }"><span class="capitalize">{{ value }}</span></template>
                <template #cell-amount="{ row }">
                    <span :class="['font-medium', row.type === 'income' ? 'text-income' : 'text-expense']">{{ formatPeso(row.amount) }}</span>
                </template>
                <template #cell-balance="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
            </AppTable>
        </template>

        <p v-else class="text-center text-slate-400 py-12">Select an account and date range to view the statement.</p>
    </div>
</template>
