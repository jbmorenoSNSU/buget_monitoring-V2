<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps({
    transaction: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const isEdit = !!props.transaction;
const txn = props.transaction?.data || props.transaction;

const form = useForm({
    type: txn?.type || 'expense',
    account_id: txn?.account_id || '',
    category_id: txn?.category_id || '',
    amount: txn?.amount || '',
    transaction_date: txn?.transaction_date || new Date().toISOString().split('T')[0],
    description: txn?.description || '',
    notes: txn?.notes || '',
    reference_number: txn?.reference_number || '',
    transfer_to_account_id: txn?.transfer_to_account_id || '',
});

const typeOptions = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'transfer', label: 'Transfer' },
];

const accountOptions = computed(() => props.accounts.map(a => ({
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

const submit = () => {
    if (isEdit) form.put(`/transactions/${txn.id}`);
    else form.post('/transactions');
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Transaction' : 'Add Transaction'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Type Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-slate-300 mb-2">Transaction Type</label>
                        <div class="flex gap-2">
                            <button v-for="opt in typeOptions" :key="opt.value" type="button"
                                @click="form.type = opt.value"
                                :class="[
                                    'flex-1 py-2.5 rounded-lg text-sm font-medium transition-all cursor-pointer',
                                    form.type === opt.value
                                        ? opt.value === 'income' ? 'bg-[#10B981] text-white' : opt.value === 'expense' ? 'bg-[#F43F5E] text-white' : 'bg-[#6366F1] text-white'
                                        : 'bg-[#0F111A] text-slate-400 hover:bg-[#232936]',
                                ]"
                            >{{ opt.label }}</button>
                        </div>
                    </div>

                    <AppSelect v-model="form.account_id" :label="isTransfer ? 'From Account' : 'Account'" :options="accountOptions" :error="form.errors.account_id" required />
                    <AppSelect v-if="isTransfer" v-model="form.transfer_to_account_id" label="To Account" :options="accountOptions" :error="form.errors.transfer_to_account_id" required />
                    <AppSelect v-if="!isTransfer" v-model="form.category_id" label="Category" :options="filteredCategories" :error="form.errors.category_id" required />
                    <AppInput v-model="form.amount" label="Amount (₱)" type="number" step="0.01" min="0.01" :error="form.errors.amount" required />
                    <AppInput v-model="form.transaction_date" label="Date" type="date" :error="form.errors.transaction_date" required />
                    <AppInput v-model="form.description" label="Description" placeholder="e.g. Grocery shopping" :error="form.errors.description" required />
                    <AppInput v-model="form.notes" label="Notes (Optional)" placeholder="Additional notes" />
                    <AppInput v-model="form.reference_number" label="Reference Number (Optional)" placeholder="e.g. INV-001" />

                    <div class="flex gap-3 pt-4">
                        <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                        <a href="/transactions"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
