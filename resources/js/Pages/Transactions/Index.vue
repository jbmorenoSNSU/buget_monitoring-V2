<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Transactions');
import { computed, ref, watch, onMounted } from 'vue';
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
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';
import TransactionFormModal from './Components/TransactionFormModal.vue';

const props = defineProps<{
    transactions: Record<string, any>;
    filters: Record<string, any>;
    accounts?: any[];
    categories?: any[];
    persons?: any[];
    debts?: any[];
}>();

const { formatPeso } = useCurrency();
const { formatShortDate } = useDate();

const items = computed(() => props.transactions?.data || []);
const totals = computed(() => {
    return items.value.reduce((acc: { income: number, expense: number, transfer: number }, txn: any) => {
        const amount = Number(txn.amount) || 0;
        if (txn.type === 'income') acc.income += amount;
        else if (txn.type === 'expense') acc.expense += amount;
        else if (txn.type === 'transfer') acc.transfer += amount;
        return acc;
    }, { income: 0, expense: 0, transfer: 0 });
});
const links = computed(() => props.transactions?.meta?.links || props.transactions?.links || []);
const deleteTarget = ref<any>(null);
const showDeleteModal = ref(false);

// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    type: 'expense',
    account_id: '',
    category_id: '',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    notes: '',
    reference_number: '',
    transfer_to_account_id: '',
    debt_id: '',
});

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.reset();
    form.id = null;
    form.type = 'expense';
    form.transaction_date = new Date().toISOString().split('T')[0];
    showFormModal.value = true;
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('action') === 'add') {
        openAddModal();
        if (params.get('date')) {
            form.transaction_date = params.get('date') as string;
        }
        
        // Remove action from URL to prevent reopening on reload
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.delete('action');
        newUrl.searchParams.delete('date');
        window.history.replaceState({}, '', newUrl);
    }

    // Trigger deferred data load
    router.reload({ only: ['accounts', 'categories', 'persons', 'debts'] });
});

const openEditModal = (txn: any) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = txn.id;
    form.type = txn.type;
    form.account_id = txn.account_id || (txn.account?.id || '');
    form.category_id = txn.category_id || (txn.category?.id || '');
    form.amount = txn.amount;
    form.transaction_date = txn.transaction_date;
    form.description = txn.description;
    form.notes = txn.notes;
    form.reference_number = txn.reference_number;
    form.transfer_to_account_id = txn.transfer_to_account_id || (txn.transferToAccount?.id || '');
    form.debt_id = txn.debt_id || '';
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/transactions/${form.id}`, { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    } else {
        form.post('/transactions', { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    }
};

// Removed Scanner State

const search = ref(props.filters.search || '');
const type = ref(props.filters.type || '');
const personId = ref(props.filters.person_id || '');
const accountId = ref(props.filters.account_id || '');
const categoryId = ref(props.filters.category_id || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');

// DataTable States
const sortBy = ref(props.filters.sort_by || 'transaction_date');
const sortDirection = ref(props.filters.sort_direction || 'desc');
const perPage = ref(props.filters.per_page || '15');

const applyFilters = () => {
    router.get('/transactions', {
        search: search.value || undefined,
        type: type.value || undefined,
        person_id: personId.value || undefined,
        account_id: accountId.value || undefined,
        category_id: categoryId.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        sort_by: sortBy.value || undefined,
        sort_direction: sortDirection.value || undefined,
        per_page: perPage.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const clearFilters = () => {
    search.value = ''; type.value = ''; personId.value = ''; accountId.value = '';
    categoryId.value = ''; dateFrom.value = ''; dateTo.value = '';
    sortBy.value = 'transaction_date';
    sortDirection.value = 'desc';
    perPage.value = '15';
    router.get('/transactions');
};

const handleSort = (columnKey: string) => {
    if (sortBy.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = columnKey;
        sortDirection.value = 'desc';
    }
    applyFilters();
};

const confirmDelete = (txn: any) => { deleteTarget.value = txn; showDeleteModal.value = true; };
const doDelete = () => { router.delete(`/transactions/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };

const columns = [
    { key: 'transaction_date', label: 'Date', sortable: true },
    { key: 'account', label: 'Account', sortable: true },
    { key: 'category', label: 'Category', sortable: true },
    { key: 'description', label: 'Description', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right', sortable: true },
    { key: 'actions', label: '' },
];

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'transfer', label: 'Transfer' },
];

const personOptions = computed(() => [{ value: '', label: 'All Persons' }, ...(Array.isArray(props.persons) ? props.persons : []).map(p => ({ value: p.id, label: p.name }))]);
const accountOptions = computed(() => [{ value: '', label: 'All Accounts' }, ...(Array.isArray(props.accounts) ? props.accounts : []).map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
}))]);
const categoryOptions = computed(() => [{ value: '', label: 'All Categories' }, ...(Array.isArray(props.categories) ? props.categories : []).map(c => ({ value: c.id, label: c.name }))]);

const perPageOptions = [
    { value: '10', label: 'Show 10 entries' },
    { value: '15', label: 'Show 15 entries' },
    { value: '25', label: 'Show 25 entries' },
    { value: '50', label: 'Show 50 entries' },
    { value: '100', label: 'Show 100 entries' },
];
</script>

<template>
    <Head title="Transactions" />
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">All Transactions</h2>
        </div>

        <!-- Filters -->
        <div class="bg-card-bg border border-border rounded-xl p-4 mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-8 gap-3 items-center">
            <AppInput v-model="search" placeholder="Search..." @keyup.enter="applyFilters" />
            <AppSelect v-model="type" :options="typeOptions" placeholder="Type" @change="applyFilters" />
            <AppSelect v-model="personId" :options="personOptions" placeholder="Person" @change="applyFilters" />
            <AppSelect v-model="accountId" :options="accountOptions" placeholder="Account" @change="applyFilters" />
            <AppSelect v-model="categoryId" :options="categoryOptions" placeholder="Category" @change="applyFilters" />
            <AppInput v-model="dateFrom" type="date" @change="applyFilters" />
            <AppInput v-model="dateTo" type="date" @change="applyFilters" />
            <AppButton variant="secondary" size="sm" @click="clearFilters" class="w-full whitespace-nowrap shadow-sm">Clear Filters</AppButton>
        </div>

        <!-- Totals & Controls -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">
            <div class="flex gap-4">
                <div class="flex items-center gap-1.5 bg-card-bg border border-border px-3 py-1.5 rounded-lg">
                    <span class="text-xs text-slate-400 font-medium">Income:</span>
                    <span class="text-sm font-bold text-income">+{{ formatPeso(totals.income) }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-card-bg border border-border px-3 py-1.5 rounded-lg">
                    <span class="text-xs text-slate-400 font-medium">Expense:</span>
                    <span class="text-sm font-bold text-expense">-{{ formatPeso(totals.expense) }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-card-bg border border-border px-3 py-1.5 rounded-lg">
                    <span class="text-xs text-slate-400 font-medium">Transfer:</span>
                    <span class="text-sm font-bold text-transfer">{{ formatPeso(totals.transfer) }}</span>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">Page Size:</span>
                <AppSelect v-model="perPage" :options="perPageOptions" class="w-40 select-none" @change="applyFilters" />
            </div>
        </div>

        <AppTable
            :columns="columns"
            :rows="items"
            :sort-by="sortBy"
            :sort-direction="sortDirection"
            @sort="handleSort"
        >
            <template #cell-transaction_date="{ row }">
                <span class="text-sm text-slate-400 whitespace-nowrap">{{ formatShortDate(row.transaction_date) }}</span>
            </template>
            <template #cell-account="{ row }">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: row.account?.color || '#94A3B8' }" />
                    <div>
                        <span class="text-sm">{{ row.account?.name }}</span>
                        <div v-if="row.account?.person" class="flex items-center mt-1">
                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none" 
                                :style="{ backgroundColor: (row.account.person.color || '#94A3B8') + '30', color: row.account.person.color || '#94A3B8', filter: 'brightness(1.4)' }">
                                {{ row.account.person.name }}
                            </span>
                        </div>
                    </div>
                </div>
            </template>
            <template #cell-category="{ row }">
                <div class="flex items-center gap-2">
                    <AppIcon v-if="row.category?.icon" :name="row.category.icon" size="18" class="text-slate-400" />
                    <AppIcon v-else name="ArrowUpDown" size="18" class="text-slate-400" />
                    <span class="text-sm">{{ row.category?.name || 'Transfer' }}</span>
                </div>
            </template>
            <template #cell-description="{ row }">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-slate-100">{{ row.description }}</span>
                    </div>
                    <div v-if="row.notes" class="text-[11px] text-slate-400 mt-0.5 leading-snug">
                        {{ row.notes }}
                    </div>
                </div>
            </template>
            <template #cell-type="{ row }">
                <AppBadge :type="row.type" :label="row.type" />
            </template>
            <template #cell-amount="{ row }">
                <span :class="['font-semibold', row.type === 'income' ? 'text-income' : row.type === 'transfer' ? 'text-transfer' : 'text-expense']">
                    {{ row.type === 'income' ? '+' : row.type === 'transfer' ? '' : '-' }}{{ formatPeso(row.amount) }}
                </span>
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-1">
                    <AppButton variant="secondary" size="sm" @click="openEditModal(row)">Edit</AppButton>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(row)">Delete</AppButton>
                </div>
            </template>
            <template #pagination>
                <AppPagination :links="links" :meta="transactions.meta" />
            </template>
        </AppTable>

        <AppModal :show="showDeleteModal" title="Delete Transaction" @close="showDeleteModal = false">
            <p class="text-slate-400">Delete "<strong>{{ deleteTarget?.description }}</strong>"? This will reverse its effect on the account balance.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <TransactionFormModal
            :show="showFormModal"
            :isEdit="isEdit"
            :form="form"
            :accounts="accounts"
            :categories="categories"
            :debts="debts"
            :persons="persons"
            @close="showFormModal = false"
            @submit="submitForm"
        />

        <!-- Form Modal -->
    </div>
</template>
