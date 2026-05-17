<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps({
    account: { type: Object, default: null },
    accountTypes: { type: Array, default: () => [] },
    persons: { type: Array, default: () => [] },
});

const isEdit = !!props.account;
const acc = props.account?.data || props.account;

const form = useForm({
    account_type_id: acc?.account_type_id || '',
    person_id: acc?.person_id || '',
    name: acc?.name || '',
    description: acc?.description || '',
    initial_balance: acc?.initial_balance || 0,
});

const typeOptions = props.accountTypes.map(t => ({ value: t.id, label: t.name }));
const personOptions = [
    { value: '', label: 'No Owner (Shared)' },
    ...props.persons.map(p => ({ value: p.id, label: p.name })),
];

const submit = () => {
    if (isEdit) {
        form.put(`/accounts/${acc.id}`);
    } else {
        form.post('/accounts');
    }
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Account' : 'Add Account'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppSelect v-model="form.account_type_id" label="Account Type" :options="typeOptions" :error="form.errors.account_type_id" required />
                    <AppSelect v-model="form.person_id" label="Owner" :options="personOptions" :error="form.errors.person_id" />
                    <AppInput v-model="form.name" label="Account Name" placeholder="e.g. BDO Savings" :error="form.errors.name" required />
                    <AppInput v-model="form.description" label="Description" placeholder="Optional description" :error="form.errors.description" />
                    <AppInput v-if="!isEdit" v-model="form.initial_balance" label="Initial Balance" type="number" step="0.01" :error="form.errors.initial_balance" required />
                    <div class="flex gap-3 pt-4">
                        <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update Account' : 'Create Account' }}</AppButton>
                        <a href="/accounts"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
