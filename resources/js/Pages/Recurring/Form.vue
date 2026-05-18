<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps({
    recurring: { type: Object, default: null },
    accounts: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
});

const isEdit = !!props.recurring;
const rec = props.recurring;

const form = useForm({
    type: rec?.type || 'expense',
    account_id: rec?.account_id || '',
    category_id: rec?.category_id || '',
    amount: rec?.amount || '',
    description: rec?.description || '',
    frequency: rec?.frequency || 'monthly',
    start_date: rec?.start_date?.split('T')[0] || new Date().toISOString().split('T')[0],
    end_date: rec?.end_date?.split('T')[0] || '',
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

const submit = () => {
    if (isEdit) form.put(`/recurring/${rec.id}`);
    else form.post('/recurring');
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Recurring' : 'Add Recurring'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
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
                        <a href="/recurring"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
