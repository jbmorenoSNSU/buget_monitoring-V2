<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppButton from '@/Components/UI/AppButton.vue';

const props = defineProps<{
    show: boolean;
    debt?: any;
    persons: any[];
}>();

const emit = defineEmits(['close']);

const form = useForm({
    person_id: '',
    name: '',
    principal_amount: '',
    interest_rate: '0',
    minimum_payment: '',
    due_date_day: '',
    status: 'active',
});

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.debt) {
            form.person_id = props.debt.person_id?.toString() || '';
            form.name = props.debt.name;
            form.principal_amount = props.debt.principal_amount;
            form.interest_rate = props.debt.interest_rate;
            form.minimum_payment = props.debt.minimum_payment;
            form.due_date_day = props.debt.due_date_day || '';
            form.status = props.debt.status;
        } else {
            form.reset();
        }
        form.clearErrors();
    }
});

const submit = () => {
    if (props.debt) {
        form.put(`/debts/${props.debt.id}`, {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post('/debts', {
            onSuccess: () => emit('close'),
        });
    }
};
</script>

<template>
    <AppModal :show="show" :title="debt ? 'Edit Debt/Loan' : 'Add Debt/Loan'" @close="emit('close')">
        <form @submit.prevent="submit" class="space-y-4">
            
            <AppSelect
                v-model="form.person_id"
                label="Owner (Optional)"
                :options="[{value: '', label: 'None'}, ...persons.map(p => ({value: p.id.toString(), label: p.name}))]"
                :error="form.errors.person_id"
            />

            <AppInput
                v-model="form.name"
                label="Debt Name (e.g. Car Loan, Credit Card)"
                :error="form.errors.name"
                required
            />

            <div class="grid grid-cols-2 gap-4">
                <AppInput
                    v-model="form.principal_amount"
                    label="Current Balance (₱)"
                    type="number"
                    step="0.01"
                    :error="form.errors.principal_amount"
                    required
                />
                <AppInput
                    v-model="form.interest_rate"
                    label="Interest Rate (% APR)"
                    type="number"
                    step="0.01"
                    :error="form.errors.interest_rate"
                    required
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <AppInput
                    v-model="form.minimum_payment"
                    label="Monthly Payment (₱)"
                    type="number"
                    step="0.01"
                    :error="form.errors.minimum_payment"
                    required
                />
                <AppInput
                    v-model="form.due_date_day"
                    label="Due Date (Day of Month 1-31)"
                    type="number"
                    min="1"
                    max="31"
                    :error="form.errors.due_date_day"
                />
            </div>

            <AppSelect
                v-if="debt"
                v-model="form.status"
                label="Status"
                :options="[{value: 'active', label: 'Active'}, {value: 'paid_off', label: 'Paid Off'}]"
                :error="form.errors.status"
            />

            <div class="flex justify-end gap-3 mt-6">
                <AppButton type="button" variant="secondary" @click="emit('close')" :disabled="form.processing">
                    Cancel
                </AppButton>
                <AppButton type="submit" variant="primary" :disabled="form.processing">
                    {{ debt ? 'Save Changes' : 'Add Debt' }}
                </AppButton>
            </div>
        </form>
    </AppModal>
</template>
