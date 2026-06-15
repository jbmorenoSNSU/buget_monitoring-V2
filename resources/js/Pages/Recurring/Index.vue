<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Recurring Transactions');
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps<{
    recurring: any[];
    accounts: any[];
    categories: any[];
    debts: any[];
}>();

const { formatPeso } = useCurrency();
const { formatShortDate, formatRelative } = useDate();

const items = computed(() => props.recurring || []);
const deleteTarget = ref<any>(null);
const showDeleteModal = ref(false);
const showGenerateModal = ref(false);
const isGenerating = ref(false);

// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    type: 'expense',
    account_id: '',
    category_id: '',
    amount: '',
    description: '',
    frequency: 'monthly',
    start_date: new Date().toISOString().split('T')[0],
    end_date: '',
    debt_id: '',
});

const typeOptions = [{ value: 'income', label: 'Income' }, { value: 'expense', label: 'Expense' }];
const freqOptions = [
    { value: 'daily', label: 'Daily' }, { value: 'weekly', label: 'Weekly' },
    { value: 'monthly', label: 'Monthly' }, { value: 'yearly', label: 'Yearly' },
];

const accountOptions = computed(() => props.accounts.map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
})));

const categoryOptions = computed(() =>
    props.categories.filter(c => c.type === form.type || c.type === 'both').map(c => ({ value: c.id, label: c.name }))
);

const debtOptions = computed(() => [{ value: '', label: 'None' }, ...props.debts.map(d => ({ value: d.id, label: d.name }))]);

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.id = null;
    form.type = 'expense';
    form.account_id = '';
    form.category_id = '';
    form.amount = '';
    form.description = '';
    form.frequency = 'monthly';
    form.start_date = new Date().toISOString().split('T')[0];
    form.end_date = '';
    form.debt_id = '';
    showFormModal.value = true;
};

const openEditModal = (rec: any) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = rec.id;
    form.type = rec.type;
    form.account_id = rec.account_id || (rec.account?.id || '');
    form.category_id = rec.category_id || (rec.category?.id || '');
    form.amount = rec.amount;
    form.description = rec.description;
    form.frequency = rec.frequency;
    form.start_date = rec.start_date?.split('T')[0];
    form.end_date = rec.end_date?.split('T')[0] || '';
    form.debt_id = rec.debt_id || '';
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/recurring/${form.id}`, { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    } else {
        form.post('/recurring', { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    }
};

const columns = [
    { key: 'description', label: 'Description', sortable: true },
    { key: 'account', label: 'Account', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right', sortable: true },
    { key: 'frequency', label: 'Frequency', sortable: true },
    { key: 'next_due_date', label: 'Next Due', sortable: true },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: '' },
];

const confirmDelete = (r: any) => { deleteTarget.value = r; showDeleteModal.value = true; };
const doDelete = () => { if(deleteTarget.value) router.delete(`/recurring/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };
const toggle = (r: any) => router.patch(`/recurring/${r.id}/toggle`);

const confirmGenerate = () => { showGenerateModal.value = true; };
const doGenerateNow = () => { 
    router.post('/recurring/generate-now', {}, {
        onStart: () => { isGenerating.value = true; },
        onFinish: () => { isGenerating.value = false; showGenerateModal.value = false; },
    }); 
};

// Client-Side Datatable States
const search = ref('');
const sortBy = ref('next_due_date');
const sortDirection = ref('asc');
const perPage = ref('10');
const currentPage = ref(1);

const perPageOptions = [
    { value: '5', label: 'Show 5 entries' },
    { value: '10', label: 'Show 10 entries' },
    { value: '25', label: 'Show 25 entries' },
    { value: '50', label: 'Show 50 entries' },
];

const handleSort = (columnKey: string) => {
    if (sortBy.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = columnKey;
        sortDirection.value = 'asc';
    }
};

const filteredAndSortedItems = computed(() => {
    let list = items.value;

    if (search.value.trim()) {
        const q = search.value.toLowerCase().trim();
        list = list.filter(r => {
            const desc = (r.description || '').toLowerCase();
            const type = (r.type || '').toLowerCase();
            const freq = (r.frequency || '').toLowerCase();
            const status = r.is_active ? 'active' : 'inactive';
            return desc.includes(q) || type.includes(q) || freq.includes(q) || status.includes(q);
        });
    }

    return [...list].sort((a, b) => {
        let valA = a[sortBy.value];
        let valB = b[sortBy.value];
        
        if (sortBy.value === 'is_active') {
            valA = a.is_active ? 1 : 0;
            valB = b.is_active ? 1 : 0;
        } else if (sortBy.value === 'account') {
            valA = a.account?.name || '';
            valB = b.account?.name || '';
        }

        if (valA === undefined || valA === null) return 1;
        if (valB === undefined || valB === null) return -1;

        let comparison = 0;
        if (typeof valA === 'string') {
            comparison = valA.localeCompare(valB);
        } else {
            comparison = valA - valB;
        }

        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

const totalItems = computed(() => filteredAndSortedItems.value.length);
const lastPage = computed(() => Math.ceil(totalItems.value / parseInt(perPage.value)) || 1);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * parseInt(perPage.value);
    const end = start + parseInt(perPage.value);
    return filteredAndSortedItems.value.slice(start, end);
});

watch([search, sortBy, sortDirection, perPage], () => {
    currentPage.value = 1;
});

const paginationMeta = computed(() => {
    const total = totalItems.value;
    if (total === 0) return { from: 0, to: 0, total: 0 };
    const from = (currentPage.value - 1) * parseInt(perPage.value) + 1;
    const to = Math.min(from + parseInt(perPage.value) - 1, total);
    return { from, to, total };
});

const paginationLinks = computed(() => {
    const links = [];
    const current = currentPage.value;
    const last = lastPage.value;

    links.push({ label: '&laquo; Previous', url: current > 1 ? 'prev' : null, active: false });
    for (let i = 1; i <= last; i++) {
        links.push({ label: i.toString(), url: i.toString(), active: i === current });
    }
    links.push({ label: 'Next &raquo;', url: current < last ? 'next' : null, active: false });
    return links;
});

const handlePageNavigate = (pageStr: string) => {
    if (pageStr === 'prev') {
        currentPage.value = Math.max(1, currentPage.value - 1);
    } else if (pageStr === 'next') {
        currentPage.value = Math.min(lastPage.value, currentPage.value + 1);
    } else {
        currentPage.value = parseInt(pageStr);
    }
};
</script>

<template>
    <Head title="Recurring Transactions" />
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">Recurring Transactions</h2>
            <div class="flex gap-2">
                <AppButton variant="secondary" @click="confirmGenerate">⚡ Generate Now</AppButton>
                <AppButton @click="openAddModal">+ Add Recurring</AppButton>
            </div>
        </div>

        <div class="bg-indigo-900/30 border border-indigo-500/30 rounded-xl p-4 mb-6 flex items-start gap-3 shadow-inner">
            <AppIcon name="Info" size="20" class="text-indigo-400 shrink-0 mt-0.5" />
            <div class="text-sm text-indigo-100/90 leading-relaxed">
                <p class="font-semibold text-indigo-100 mb-1">How Recurring Transactions Work</p>
                <p>
                    Because this app runs locally without a background server, recurring transactions are <strong>not generated automatically</strong>. 
                    You must click the <span class="font-medium text-white bg-indigo-500/20 px-1.5 py-0.5 rounded border border-indigo-500/30">⚡ Generate Now</span> button to process any transactions that are due. 
                    We recommend clicking this button periodically to keep your ledger up to date.
                </p>
            </div>
        </div>

        <div class="flex justify-between items-end mb-4 gap-4">
            <!-- Search -->
            <div class="w-full sm:w-72">
                <AppInput v-model="search" placeholder="Search recurring..." />
            </div>

            <!-- Page Size -->
            <div class="hidden sm:flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Page Size:</span>
                <AppSelect v-model="perPage" :options="perPageOptions" class="w-40 select-none" />
            </div>
        </div>

        <div class="flex sm:hidden justify-end items-center mb-4 gap-2">
            <span class="text-xs text-slate-400 font-medium">Page Size:</span>
            <AppSelect v-model="perPage" :options="perPageOptions" class="w-32 select-none" />
        </div>

        <AppTable 
            :columns="columns" 
            :rows="paginatedItems"
            :sort-by="sortBy"
            :sort-direction="sortDirection"
            @sort="handleSort"
        >
            <template #cell-account="{ row }">
                <div v-if="row.account" class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: row.account.color || '#94A3B8' }" />
                    <span class="text-sm font-medium text-slate-200 flex items-center gap-2">
                        {{ row.account.name }}
                        <span v-if="row.account.person" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none" 
                            :style="{ backgroundColor: (row.account.person.color || '#94A3B8') + '30', color: row.account.person.color || '#94A3B8', filter: 'brightness(1.4)' }">
                            {{ row.account.person.name }}
                        </span>
                    </span>
                </div>
                <span v-else class="text-sm text-slate-500">-</span>
            </template>
            <template #cell-type="{ row }">
                <AppBadge :type="row.type" :label="row.type" />
            </template>
            <template #cell-amount="{ row }">
                <span :class="['font-semibold', row.type === 'income' ? 'text-income' : row.type === 'transfer' ? 'text-transfer' : 'text-expense']">
                    {{ row.type === 'income' ? '+' : row.type === 'transfer' ? '' : '-' }}{{ formatPeso(row.amount) }}
                </span>
            </template>
            <template #cell-frequency="{ row }">
                <span class="capitalize text-sm text-slate-400">{{ row.frequency }}</span>
            </template>
            <template #cell-next_due_date="{ row }">
                <span class="text-sm">{{ formatRelative(row.next_due_date) }}</span>
            </template>
            <template #cell-is_active="{ row }">
                <AppBadge :type="row.is_active ? 'active' : 'inactive'" :label="row.is_active ? 'Active' : 'Paused'" />
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-1">
                    <AppButton variant="secondary" size="sm" @click="openEditModal(row)">Edit</AppButton>
                    <AppButton variant="ghost" size="sm" @click="toggle(row)">{{ row.is_active ? 'Pause' : 'Resume' }}</AppButton>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(row)">Delete</AppButton>
                </div>
            </template>
            <template #pagination>
                <AppPagination
                    :links="paginationLinks"
                    :meta="paginationMeta"
                    :client-side="true"
                    @navigate="handlePageNavigate"
                />
            </template>
        </AppTable>

        <AppModal :show="showDeleteModal" title="Delete Recurring" @close="showDeleteModal = false">
            <p class="text-slate-400">Delete "<strong>{{ deleteTarget?.description }}</strong>"?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <AppModal :show="showGenerateModal" title="Generate Due Transactions" @close="!isGenerating && (showGenerateModal = false)">
            <p class="text-slate-400 mb-2">Are you sure you want to manually generate all recurring transactions that are currently due?</p>
            <p class="text-sm text-expense">This action will instantly create ledger entries for any transactions due today or past due.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showGenerateModal = false" :disabled="isGenerating">Cancel</AppButton>
                <AppButton variant="primary" @click="doGenerateNow" :disabled="isGenerating">
                    {{ isGenerating ? 'Generating...' : 'Yes, Generate Now' }}
                </AppButton>
            </template>
        </AppModal>

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Recurring' : 'Add Recurring'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <AppSelect v-model="form.type" label="Type" :options="typeOptions" :error="form.errors.type" required />
                <AppSelect v-model="form.account_id" label="Account" :options="accountOptions" :error="form.errors.account_id" required />
                <AppSelect v-model="form.category_id" label="Category" :options="categoryOptions" :error="form.errors.category_id" required />
                <AppSelect v-if="form.type === 'expense'" v-model="form.debt_id" label="Debt (Optional)" :options="debtOptions" :error="form.errors.debt_id" />
                <AppInput v-model="form.amount" label="Amount (₱)" type="number" step="0.01" :error="form.errors.amount" required />
                <AppInput v-model="form.description" label="Description" :error="form.errors.description" required />
                <AppSelect v-model="form.frequency" label="Frequency" :options="freqOptions" :error="form.errors.frequency" required />
                <AppInput v-model="form.start_date" label="Start Date" type="date" :error="form.errors.start_date" required />
                <AppInput v-model="form.end_date" label="End Date (Optional)" type="date" :error="form.errors.end_date" />
                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </div>
</template>
