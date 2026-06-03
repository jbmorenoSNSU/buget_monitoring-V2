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
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    transactions: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    persons: { type: Array, default: () => [] },
});

const { formatPeso } = useCurrency();
const { formatShortDate } = useDate();

const items = computed(() => props.transactions?.data || []);
const links = computed(() => props.transactions?.meta?.links || props.transactions?.links || []);
const deleteTarget = ref(null);
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
});

const formAccountOptions = computed(() => props.accounts.map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
})));

const filteredCategories = computed(() => {
    if (form.type === 'transfer') return [];
    return props.categories
        .filter(c => c.type === form.type || c.type === 'both')
        .map(c => ({ value: c.id, label: c.name }));
});

const isTransfer = computed(() => form.type === 'transfer');

const openAddModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    form.transaction_date = new Date().toISOString().split('T')[0];
    showFormModal.value = true;
};

watch(() => props.filters.action, (newAction) => {
    if (newAction === 'add') {
        openAddModal();
        // Remove action from URL to prevent reopening on reload
        router.get('/transactions', { ...props.filters, action: undefined }, { preserveState: true, replace: true });
    }
}, { immediate: true });

const openEditModal = (txn) => {
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
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/transactions/${form.id}`, { onSuccess: () => { showFormModal.value = false; } });
    } else {
        form.post('/transactions', { onSuccess: () => { showFormModal.value = false; } });
    }
};

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

const handleSort = (columnKey) => {
    if (sortBy.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = columnKey;
        sortDirection.value = 'desc';
    }
    applyFilters();
};

const confirmDelete = (txn) => { deleteTarget.value = txn; showDeleteModal.value = true; };
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

const personOptions = [{ value: '', label: 'All Persons' }, ...props.persons.map(p => ({ value: p.id, label: p.name }))];
const accountOptions = [{ value: '', label: 'All Accounts' }, ...props.accounts.map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
}))];
const categoryOptions = [{ value: '', label: 'All Categories' }, ...props.categories.map(c => ({ value: c.id, label: c.name }))];

const perPageOptions = [
    { value: '10', label: 'Show 10 entries' },
    { value: '15', label: 'Show 15 entries' },
    { value: '25', label: 'Show 25 entries' },
    { value: '50', label: 'Show 50 entries' },
    { value: '100', label: 'Show 100 entries' },
];
</script>

<template>
    <AppLayout title="Transactions">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">All Transactions</h2>
            <AppButton @click="openAddModal">+ Add Transaction</AppButton>
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

        <!-- Controls: Show entries -->
        <div class="flex justify-end items-center mb-4">
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
                        <div v-if="row.account?.person" class="flex items-center gap-1 mt-0.5">
                            <span class="text-[10px] font-medium" :style="{ color: row.account.person.color }">{{ row.account.person.name }}</span>
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
                    <span class="text-sm font-medium text-slate-100">{{ row.description }}</span>
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

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Transaction' : 'Add Transaction'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <!-- Type Toggle -->
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-2">Transaction Type</label>
                    <div class="flex gap-2">
                        <button v-for="opt in typeOptions" :key="opt.value" type="button" v-show="opt.value !== ''"
                            @click="form.type = opt.value"
                            :class="[
                                'flex-1 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer',
                                form.type === opt.value
                                    ? opt.value === 'income' ? 'bg-income text-white' : opt.value === 'expense' ? 'bg-expense text-white' : 'bg-primary text-white'
                                    : 'bg-page-bg text-slate-400 hover:bg-border',
                            ]"
                        >{{ opt.label }}</button>
                    </div>
                </div>

                <AppSelect v-model="form.account_id" :label="isTransfer ? 'From Account' : 'Account'" :options="formAccountOptions" :error="form.errors.account_id" required />
                <AppSelect v-if="isTransfer" v-model="form.transfer_to_account_id" label="To Account" :options="formAccountOptions" :error="form.errors.transfer_to_account_id" required />
                <AppSelect v-if="!isTransfer" v-model="form.category_id" label="Category" :options="filteredCategories" :error="form.errors.category_id" required />
                <AppInput v-model="form.amount" label="Amount (₱)" type="number" step="0.01" min="0.01" :error="form.errors.amount" required />
                <AppInput v-model="form.transaction_date" label="Date" type="date" :error="form.errors.transaction_date" required />
                <AppInput v-model="form.description" label="Description" placeholder="e.g. Grocery shopping" :error="form.errors.description" required />
                <AppInput v-model="form.notes" label="Notes (Optional)" placeholder="Additional notes" />
                <AppInput v-model="form.reference_number" label="Reference Number (Optional)" placeholder="e.g. INV-001" />

                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </AppLayout>
</template>
