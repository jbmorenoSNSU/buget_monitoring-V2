<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    recurring: { type: Array, default: () => [] },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const { formatPeso } = useCurrency();
const { formatShortDate, formatRelative } = useDate();

const items = computed(() => props.recurring || []);
const deleteTarget = ref(null);
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

const openAddModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    form.start_date = new Date().toISOString().split('T')[0];
    showFormModal.value = true;
};

const openEditModal = (rec) => {
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
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/recurring/${form.id}`, { onSuccess: () => { showFormModal.value = false; } });
    } else {
        form.post('/recurring', { onSuccess: () => { showFormModal.value = false; } });
    }
};

const columns = [
    { key: 'description', label: 'Description' },
    { key: 'account', label: 'Account' },
    { key: 'type', label: 'Type' },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right' },
    { key: 'frequency', label: 'Frequency' },
    { key: 'next_due_date', label: 'Next Due' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: '' },
];

const confirmDelete = (r) => { deleteTarget.value = r; showDeleteModal.value = true; };
const doDelete = () => { router.delete(`/recurring/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };
const toggle = (r) => router.patch(`/recurring/${r.id}/toggle`);

const confirmGenerate = () => { showGenerateModal.value = true; };
const doGenerateNow = () => { 
    router.post('/recurring/generate-now', {}, {
        onStart: () => { isGenerating.value = true; },
        onFinish: () => { isGenerating.value = false; showGenerateModal.value = false; },
    }); 
};
</script>

<template>
    <AppLayout title="Recurring Transactions">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-50">Recurring Transactions</h2>
            <div class="flex gap-2">
                <AppButton variant="secondary" @click="confirmGenerate">⚡ Generate Now</AppButton>
                <AppButton @click="openAddModal">+ Add Recurring</AppButton>
            </div>
        </div>

        <AppTable :columns="columns" :rows="items">
            <template #cell-account="{ row }">
                <div v-if="row.account" class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: row.account.color || '#94A3B8' }" />
                    <span class="text-sm font-medium text-slate-200">
                        {{ row.account.name }}
                        <span v-if="row.account.person" :style="{ color: row.account.person.color }" class="font-bold opacity-90 text-xs"> ({{ row.account.person.name }})</span>
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
    </AppLayout>
</template>
