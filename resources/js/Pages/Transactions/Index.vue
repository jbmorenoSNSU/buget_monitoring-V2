<script setup>
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
import jsQR from 'jsqr';

const props = defineProps({
    transactions: { type: Object, default: () => ({}) },
    filters: { type: Object, default: () => ({}) },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    persons: { type: Array, default: () => [] },
    debts: { type: Array, default: () => [] },
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
    debt_id: '',
});

const formAccountOptions = computed(() => props.accounts.map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
})));

const debtOptions = computed(() => [{ value: '', label: 'None' }, ...props.debts.map(d => ({ value: d.id, label: d.name }))]);

const filteredCategories = computed(() => {
    if (form.type === 'transfer') return [];
    return props.categories
        .filter(c => c.type === form.type || c.type === 'both')
        .map(c => ({ value: c.id, label: c.name }));
});

const isTransfer = computed(() => form.type === 'transfer');

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.id = null;
    form.type = 'expense';
    form.account_id = '';
    form.category_id = '';
    form.amount = '';
    form.description = '';
    form.notes = '';
    form.reference_number = '';
    form.transfer_to_account_id = '';
    form.transaction_date = new Date().toISOString().split('T')[0];
    form.split_bill = false;
    form.split_with_person_id = '';
    form.split_amount = '';
    form.debt_id = '';
    showFormModal.value = true;
};

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('action') === 'add') {
        openAddModal();
        if (params.get('date')) {
            form.transaction_date = params.get('date');
        }
        
        // Remove action from URL to prevent reopening on reload
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.delete('action');
        newUrl.searchParams.delete('date');
        window.history.replaceState({}, '', newUrl);
    }
});

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

// OCR & QR Logic
const isScanning = ref(false);
const scanStatus = ref('');
const scanProgress = ref(0);
const qrCanvas = ref(null);

const resetScanUI = (event) => {
    isScanning.value = false;
    scanStatus.value = '';
    scanProgress.value = 0;
    if (event.target) event.target.value = null; // reset input
};

const scanQR = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = qrCanvas.value;
                if (!canvas) return resolve(null);
                
                const ctx = canvas.getContext('2d', { willReadFrequently: true });
                // Scale down slightly if image is massive to make QR scanning fast
                const MAX_WIDTH = 1200;
                let width = img.width;
                let height = img.height;
                if (width > MAX_WIDTH) {
                    height = height * (MAX_WIDTH / width);
                    width = MAX_WIDTH;
                }
                
                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);
                
                const imageData = ctx.getImageData(0, 0, width, height);
                const code = jsQR(imageData.data, imageData.width, imageData.height, {
                    inversionAttempts: "dontInvert",
                });
                resolve(code);
            };
            img.onerror = reject;
            img.src = e.target.result;
        };
        reader.onerror = reject;
        reader.readAsDataURL(file);
    });
};

const handleReceiptScan = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    isScanning.value = true;
    scanStatus.value = 'Analyzing Image...';
    scanProgress.value = 5;
    
    // 1. Try QR Code Scanning First (Lightning Fast & 100% Accurate)
    try {
        const qrResult = await scanQR(file);
        if (qrResult) {
            try {
                const payload = JSON.parse(qrResult.data);
                if (payload.app === 'BudgetMonitor') {
                    // Success! Pre-fill form
                    form.type = payload.type || 'expense';
                    if (payload.amount) form.amount = payload.amount;
                    if (payload.description) form.description = payload.description;
                    
                    if (payload.type === 'transfer' && payload.transfer_to_account_id) {
                        form.transfer_to_account_id = payload.transfer_to_account_id;
                    }
                    if (payload.type === 'expense' && payload.split_with_person_id) {
                        form.split_bill = true;
                        form.split_with_person_id = payload.split_with_person_id;
                        form.split_amount = payload.amount;
                    }
                    
                    scanStatus.value = 'QR Scanned Successfully!';
                    scanProgress.value = 100;
                    setTimeout(() => resetScanUI(event), 800);
                    return; // EXIT EARLY
                }
            } catch (e) {
                console.log("Found QR but not a valid BudgetMonitor payload", qrResult.data);
            }
        }
    } catch(e) {
         console.warn("QR Scan failed, falling back to OCR", e);
    }

    // 2. Fallback to Tesseract AI OCR
    scanStatus.value = 'Loading AI Engine...';
    try {
        // More robust import for Vite ES modules
        const tesseractModule = await import('tesseract.js');
        const createWorker = tesseractModule.createWorker || tesseractModule.default?.createWorker;
        
        if (!createWorker) {
            throw new Error("Failed to load Tesseract.js createWorker function.");
        }

        const worker = await createWorker('eng', 1, {
            logger: m => {
                if (m.status === 'recognizing text') {
                    scanStatus.value = 'Scanning receipt text...';
                    scanProgress.value = Math.max(15, Math.round(m.progress * 100));
                } else if (m.status.includes('loading')) {
                    scanStatus.value = 'Loading AI models...';
                    scanProgress.value = 10;
                }
            }
        });
        
        const { data: { text } } = await worker.recognize(file);
        await worker.terminate();

        scanStatus.value = 'Parsing data...';
        scanProgress.value = 100;

        let foundAmount = false;
        let foundDate = false;

        // Extract amount (find the largest currency-like number, or look for total)
        const textLines = text.split('\n').map(l => l.trim().toLowerCase());
        let maxAmount = 0;
        
        // Strategy 1: Look for lines with "total" or "amount due"
        for (let i = 0; i < textLines.length; i++) {
            const line = textLines[i];
            if (line.includes('total') || line.includes('amount') || line.includes('due') || line.includes('amount due')) {
                // Check this line and the next 2 lines (handwriting often gets pushed to the next line)
                const linesToCheck = [line];
                if (i + 1 < textLines.length) linesToCheck.push(textLines[i + 1]);
                if (i + 2 < textLines.length) linesToCheck.push(textLines[i + 2]);
                
                const combined = linesToCheck.join(' ');
                // Look for standard decimals or numbers ending with a dash like "400-"
                const numbers = [...combined.matchAll(/(\d{1,3}(?:,\d{3})*|\d+)(?:\.\d{2}|\-)\b/g)].map(m => parseFloat(m[1].replace(/,/g, '')));
                if (numbers.length > 0) {
                    const lineMax = Math.max(...numbers);
                    if (lineMax > maxAmount) maxAmount = lineMax;
                }
            }
        }

        // Strategy 2: If no "total" line had a valid number, just grab the absolute largest valid amount
        if (maxAmount === 0) {
            // STRICT FALLBACK: Look ONLY for decimals to avoid catching serial numbers or ZIP codes
            const allNumbersRegex = /((?:\d{1,3}(?:,\d{3})*|\d+)\.\d{2})\b/g;
            const matches = [...text.matchAll(allNumbersRegex)];
            if (matches.length > 0) {
                const numbers = matches.map(m => parseFloat(m[1].replace(/,/g, '')));
                // Filter out massive numbers just in case
                const validAmounts = numbers.filter(n => n < 1000000);
                if (validAmounts.length > 0) {
                    maxAmount = Math.max(...validAmounts);
                }
            }
        }

        if (maxAmount > 0 && maxAmount < 1000000) {
            form.amount = maxAmount;
            form.type = 'expense';
            foundAmount = true;
        }
        
        // Extract date (support various slash, dash formats)
        const dateRegex = /(\d{1,4}[\/\-]\d{1,2}[\/\-]\d{1,4})|([A-Za-z]{3,9}\s+\d{1,2},?\s+\d{4})/;
        const dateMatch = text.match(dateRegex);
        if (dateMatch) {
            let parsedDate = new Date(dateMatch[0]);
            if (!isNaN(parsedDate) && parsedDate.getFullYear() > 2000 && parsedDate.getFullYear() <= new Date().getFullYear() + 1) {
                form.transaction_date = parsedDate.toISOString().split('T')[0];
                foundDate = true;
            }
        }
        
        if (!form.description) {
            form.description = "Scanned Receipt";
        }

        if (!foundAmount && !foundDate) {
            alert("Scanner finished but could not clearly identify an amount or date. Please check the receipt clarity.");
        }
        
    } catch (e) {
        console.error("OCR Failed:", e);
        alert("Failed to initialize or read receipt. Check browser console for details.");
    } finally {
        setTimeout(() => resetScanUI(event), 800);
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
            
            <canvas ref="qrCanvas" class="hidden"></canvas>

            <!-- OCR / QR Receipt Scanner (only for Add) -->
            <div v-if="!isEdit" class="mb-5 bg-indigo-900/20 border border-indigo-500/30 rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-2 text-indigo-300 font-medium">
                        <AppIcon name="QrCode" size="18" />
                        <span>QR / AI Scanner</span>
                    </div>
                    <AppBadge type="income" label="Beta" class="text-[9px] px-1.5 py-0.5" />
                </div>
                <p class="text-xs text-indigo-200/70 mb-3">Upload a photo of a BudgetMonitor QR code or a receipt to automatically extract data.</p>
                
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
                <AppSelect v-if="form.type === 'expense'" v-model="form.debt_id" label="Debt (Optional)" :options="debtOptions" :error="form.errors.debt_id" />
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
