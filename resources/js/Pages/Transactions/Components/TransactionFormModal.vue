<script setup lang="ts">
import { computed, watch } from 'vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps<{
    show: boolean;
    isEdit: boolean;
    form: any;
    accounts?: any[];
    categories?: any[];
    debts?: any[];
    persons?: any[];
}>();

const emit = defineEmits(['close', 'submit']);

const formAccountOptions = computed(() => (Array.isArray(props.accounts) ? props.accounts : []).map(a => ({
    value: a.id,
    label: a.person ? `${a.name} (${a.person.name})` : a.name
})));

const debtOptions = computed(() => [{ value: '', label: 'None' }, ...(Array.isArray(props.debts) ? props.debts : []).map(d => ({ value: d.id, label: d.name }))]);

const filteredCategories = computed(() => {
    if (props.form.type === 'transfer') return [];
    return (Array.isArray(props.categories) ? props.categories : [])
        .filter(c => c.type === props.form.type || c.type === 'both')
        .map(c => ({ value: c.id, label: c.name }));
});

const personOptions = computed(() => (Array.isArray(props.persons) ? props.persons : []).map(p => ({ value: p.id, label: p.name })));

const isTransfer = computed(() => props.form.type === 'transfer');

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'transfer', label: 'Transfer' },
];

watch(() => props.form.type, (newType) => {
    if (newType === 'transfer') {
        props.form.category_id = '';
        props.form.debt_id = '';
        if (props.form.errors) {
            props.form.errors.category_id = '';
            props.form.errors.debt_id = '';
        }
    } else {
        props.form.transfer_to_account_id = '';
        if (props.form.errors) props.form.errors.transfer_to_account_id = '';
    }
    
    if (newType !== 'expense') {
        props.form.debt_id = '';
        if (props.form.errors) props.form.errors.debt_id = '';
    }
});

const submitForm = () => {
    emit('submit');
};

const close = () => {
    emit('close');
};
</script>

<template>
    <AppModal :show="show" :title="isEdit ? 'Edit Transaction' : 'Add Transaction'" @close="close">
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

            <div class="flex gap-3 pt-4">
                <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                <AppButton type="button" variant="secondary" @click="close">Cancel</AppButton>
            </div>
        </form>
    </AppModal>
</template>
