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
    split_bill: false,
    split_with_person_id: '',
    split_amount: '',
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
    form.split_bill = false;
    form.split_with_person_id = '';
    form.split_amount = '';
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
    form.split_bill = !!txn.split_with_person_id;
    form.split_with_person_id = txn.split_with_person_id || '';
    form.split_amount = txn.split_amount || '';
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/transactions/${form.id}`, { onSuccess: () => { showFormModal.value = false; } });
    } else {
        form.post('/transactions', { onSuccess: () => { showFormModal.value = false; } });
    }
};

// OCR Logic
const isScanning = ref(false);
const scanStatus = ref('');
const scanProgress = ref(0);

const handleReceiptScan = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    isScanning.value = true;
    scanStatus.value = 'Loading AI Engine...';
    scanProgress.value = 10;
    
    try {
        const Tesseract = (await import('tesseract.js')).default;
        const worker = await Tesseract.createWorker('eng', 1, {
            logger: m => {
                if (m.status === 'recognizing text') {
                    scanStatus.value = 'Scanning receipt text...';
                    scanProgress.value = Math.round(m.progress * 100);
                } else if (m.status.includes('loading')) {
                    scanStatus.value = 'Loading AI models...';
                }
            }
        });
        
        const { data: { text } } = await worker.recognize(file);
        await worker.terminate();

        // Extract amount (find the largest currency-like number)
        const allNumbersRegex = /((?:\d{1,3}(?:,\d{3})*|\d+)\.\d{2})/g;
        const matches = [...text.matchAll(allNumbersRegex)];
        if (matches.length > 0) {
            const numbers = matches.map(m => parseFloat(m[1].replace(/,/g, '')));
            const maxAmount = Math.max(...numbers);
            if (maxAmount > 0) {
                form.amount = maxAmount;
                form.type = 'expense';
            }
        }
        
        // Extract date
        const dateRegex = /(\d{4}-\d{2}-\d{2})|(\d{1,2}\/\d{1,2}\/\d{2,4})/;
        const dateMatch = text.match(dateRegex);
        if (dateMatch) {
            let parsedDate = new Date(dateMatch[0]);
            if (!isNaN(parsedDate) && parsedDate.getFullYear() > 2000) {
                form.transaction_date = parsedDate.toISOString().split('T')[0];
            }
        }
        
        if (!form.description) {
            form.description = "Scanned Receipt";
        }
        
    } catch (e) {
        console.error("OCR Failed:", e);
        alert("Failed to read receipt. Please try a clearer image.");
    } finally {
        isScanning.value = false;
        scanStatus.value = '';
        scanProgress.value = 0;
        event.target.value = null; // reset input
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
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-slate-100">{{ row.description }}</span>
                        <AppBadge v-if="row.split_with_person_id" type="info" label="Split" class="text-[10px] px-1.5 py-0.5" />
                    </div>
                    <div v-if="row.notes" class="text-[11px] text-slate-400 mt-0.5 leading-snug">
                        {{ row.notes }}
                    </div>
                    <div v-if="row.split_with_person_id && row.split_amount" class="text-[11px] text-primary mt-0.5 leading-snug">
                        Split amount: {{ formatPeso(row.split_amount) }}
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
            
            <!-- OCR Receipt Scanner (only for Add) -->
            <div v-if="!isEdit" class="mb-5 bg-indigo-900/20 border border-indigo-500/30 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-indigo-300 font-medium">
                        <AppIcon name="Camera" size="18" />
                        <span>AI Receipt Scanner</span>
                    </div>
                    <AppBadge type="income" label="Beta" class="text-[9px] px-1.5 py-0.5" />
                </div>
                <p class="text-xs text-indigo-200/70 mb-3">Upload a photo of your receipt to automatically extract the amount and date.</p>
                
                <div v-if="isScanning" class="space-y-2">
                    <div class="flex justify-between text-xs font-medium text-indigo-300">
                        <span>{{ scanStatus }}</span>
                        <span>{{ scanProgress }}%</span>
                    </div>
                    <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: scanProgress + '%' }"></div>
                    </div>
                </div>
                <div v-else>
                    <input type="file" accept="image/*" class="hidden" ref="receiptInput" @change="handleReceiptScan" />
                    <AppButton type="button" variant="secondary" size="sm" class="w-full bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-300 border-indigo-500/30" @click="$refs.receiptInput.click()">
                        <AppIcon name="Upload" size="14" class="mr-2" /> Select Image
                    </AppButton>
                </div>
            </div>

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

                <!-- Split Bill Feature -->
                <div v-if="form.type === 'expense'" class="bg-slate-800/50 p-4 rounded-lg border border-border">
                    <label class="flex items-center gap-2 mb-3 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.split_bill" class="w-4 h-4 rounded bg-page-bg border-border text-primary focus:ring-primary focus:ring-offset-slate-900" />
                        <span class="text-sm font-medium text-slate-300">Split this expense?</span>
                    </label>

                    <div v-if="form.split_bill" class="space-y-4 pt-2 border-t border-border/50">
                        <AppSelect v-model="form.split_with_person_id" label="Who owes you?" :options="personOptions.filter(p => p.value !== '')" :error="form.errors.split_with_person_id" required />
                        <AppInput v-model="form.split_amount" label="Amount they owe you (₱)" type="number" step="0.01" min="0.01" :error="form.errors.split_amount" required />
                    </div>
                </div>

                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </AppLayout>
</template>
