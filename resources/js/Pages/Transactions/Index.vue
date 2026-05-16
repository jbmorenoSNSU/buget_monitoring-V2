<script setup>
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    transactions: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const { formatPeso } = useCurrency();
const { formatShortDate } = useDate();

const items = computed(() => props.transactions?.data || []);
const links = computed(() => props.transactions?.meta?.links || props.transactions?.links || []);
const deleteTarget = ref(null);
const showDeleteModal = ref(false);

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const accountId = ref(props.filters.account_id || '');
const categoryId = ref(props.filters.category_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

const applyFilters = () => {
    router.get('/transactions', {
        search: search.value || undefined,
        type: type.value || undefined,
        account_id: accountId.value || undefined,
        category_id: categoryId.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    search.value = ''; type.value = ''; accountId.value = '';
    categoryId.value = ''; dateFrom.value = ''; dateTo.value = '';
    router.get('/transactions');
};

const confirmDelete = (txn) => { deleteTarget.value = txn; showDeleteModal.value = true; };
const doDelete = () => { router.delete(`/transactions/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };

const columns = [
    { key: 'transaction_date', label: 'Date' },
    { key: 'account', label: 'Account' },
    { key: 'category', label: 'Category' },
    { key: 'description', label: 'Description' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right' },
    { key: 'actions', label: '' },
];

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'transfer', label: 'Transfer' },
];

const accountOptions = [{ value: '', label: 'All Accounts' }, ...props.accounts.map(a => ({ value: a.id, label: a.name }))];
const categoryOptions = [{ value: '', label: 'All Categories' }, ...props.categories.map(c => ({ value: c.id, label: c.name }))];
</script>

<template>
    <AppLayout title="Transactions">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">All Transactions</h2>
            <Link href="/transactions/create"><AppButton>+ Add Transaction</AppButton></Link>
        </div>

        <!-- Filters -->
        <div class="bg-[#161B26] border border-[#232936] rounded-xl p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 items-center">
            <AppInput v-model="search" placeholder="Search..." @keyup.enter="applyFilters" />
            <AppSelect v-model="type" :options="typeOptions" placeholder="Type" @change="applyFilters" />
            <AppSelect v-model="accountId" :options="accountOptions" placeholder="Account" @change="applyFilters" />
            <AppSelect v-model="categoryId" :options="categoryOptions" placeholder="Category" @change="applyFilters" />
            <AppInput v-model="dateFrom" type="date" @change="applyFilters" />
            <AppInput v-model="dateTo" type="date" @change="applyFilters" />
            <AppButton variant="secondary" size="sm" @click="clearFilters" class="w-full whitespace-nowrap shadow-sm">Clear Filters</AppButton>
        </div>

        <AppTable :columns="columns" :rows="items">
            <template #cell-transaction_date="{ row }">
                <span class="text-sm text-slate-400 whitespace-nowrap">{{ formatShortDate(row.transaction_date) }}</span>
            </template>
            <template #cell-account="{ row }">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: row.account?.color || '#94A3B8' }" />
                    <span class="text-sm">{{ row.account?.name }}</span>
                </div>
            </template>
            <template #cell-category="{ row }">
                <div class="flex items-center gap-2">
                    <AppIcon v-if="row.category?.icon" :name="row.category.icon" size="18" class="text-slate-400" />
                    <AppIcon v-else name="ArrowUpDown" size="18" class="text-slate-400" />
                    <span class="text-sm">{{ row.category?.name || 'Transfer' }}</span>
                </div>
            </template>
            <template #cell-type="{ row }">
                <AppBadge :type="row.type" :label="row.type" />
            </template>
            <template #cell-amount="{ row }">
                <span :class="['font-semibold', row.type === 'income' ? 'text-[#10B981]' : row.type === 'transfer' ? 'text-[#6366F1]' : 'text-[#F43F5E]']">
                    {{ row.type === 'income' ? '+' : row.type === 'transfer' ? '' : '-' }}{{ formatPeso(row.amount) }}
                </span>
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-1">
                    <Link :href="`/transactions/${row.id}/edit`"><AppButton variant="secondary" size="sm">Edit</AppButton></Link>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(row)">Delete</AppButton>
                </div>
            </template>
            <template #pagination>
                <AppPagination :links="links" />
            </template>
        </AppTable>

        <AppModal :show="showDeleteModal" title="Delete Transaction" @close="showDeleteModal = false">
            <p class="text-slate-400">Delete "<strong>{{ deleteTarget?.description }}</strong>"? This will reverse its effect on the account balance.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
