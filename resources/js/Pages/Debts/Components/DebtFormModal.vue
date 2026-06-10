<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

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
            form.person_id     = props.debt.person_id?.toString() || '';
            form.name          = props.debt.name;
            form.principal_amount = props.debt.principal_amount;
            form.interest_rate = props.debt.interest_rate;
            form.minimum_payment = props.debt.minimum_payment;
            form.due_date_day  = props.debt.due_date_day || '';
            form.status        = props.debt.status;
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

            <!-- Owner -->
            <AppSelect
                v-model="form.person_id"
                label="Owner"
                placeholder="None (Shared)"
                :options="[{value: '', label: 'None (Shared)'}, ...persons.map(p => ({value: p.id.toString(), label: p.name}))]"
                :error="form.errors.person_id"
            />

            <!-- Debt Name -->
            <AppInput
                v-model="form.name"
                label="Debt Name"
                placeholder="e.g. Car Loan, Credit Card, Mortgage"
                :error="form.errors.name"
                required
            />

            <!-- Balance + Interest Rate -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Current Balance with ₱ prefix -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        Current Balance <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-slate-500 text-sm font-medium select-none pointer-events-none z-10">₱</span>
                        <input
                            v-model="form.principal_amount"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            required
                            :class="[
                                'app-input w-full rounded-lg border text-sm transition-all duration-200 pl-7 pr-3 py-2.5',
                                'focus:outline-none focus:ring-1 placeholder-slate-600 text-slate-100',
                                form.errors.principal_amount
                                    ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-rose-400/30'
                                    : 'border-border bg-card-bg hover:border-slate-600 focus:border-primary/60 focus:ring-primary/20',
                            ]"
                        />
                    </div>
                    <p v-if="form.errors.principal_amount" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                        <AppIcon name="AlertCircle" size="11" />{{ form.errors.principal_amount }}
                    </p>
                </div>

                <!-- Interest Rate with % suffix -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        Interest Rate (APR) <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <input
                            v-model="form.interest_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            required
                            :class="[
                                'app-input w-full rounded-lg border text-sm transition-all duration-200 pl-3 pr-9 py-2.5',
                                'focus:outline-none focus:ring-1 placeholder-slate-600 text-slate-100',
                                form.errors.interest_rate
                                    ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-rose-400/30'
                                    : 'border-border bg-card-bg hover:border-slate-600 focus:border-primary/60 focus:ring-primary/20',
                            ]"
                        />
                        <span class="absolute right-3 text-slate-500 text-sm font-semibold select-none pointer-events-none">%</span>
                    </div>
                    <p v-if="form.errors.interest_rate" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                        <AppIcon name="AlertCircle" size="11" />{{ form.errors.interest_rate }}
                    </p>
                </div>
            </div>

            <!-- Monthly Payment + Due Date -->
            <div class="grid grid-cols-2 gap-4">
                <!-- Monthly Payment with ₱ prefix -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        Monthly Payment <span class="text-rose-400">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <span class="absolute left-3 text-slate-500 text-sm font-medium select-none pointer-events-none z-10">₱</span>
                        <input
                            v-model="form.minimum_payment"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0.00"
                            required
                            :class="[
                                'app-input w-full rounded-lg border text-sm transition-all duration-200 pl-7 pr-3 py-2.5',
                                'focus:outline-none focus:ring-1 placeholder-slate-600 text-slate-100',
                                form.errors.minimum_payment
                                    ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-rose-400/30'
                                    : 'border-border bg-card-bg hover:border-slate-600 focus:border-primary/60 focus:ring-primary/20',
                            ]"
                        />
                    </div>
                    <p v-if="form.errors.minimum_payment" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                        <AppIcon name="AlertCircle" size="11" />{{ form.errors.minimum_payment }}
                    </p>
                </div>

                <!-- Due Date Day -->
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">
                        Due Day <span class="text-slate-600 normal-case font-normal">(1–31)</span>
                    </label>
                    <div class="relative flex items-center">
                        <input
                            v-model="form.due_date_day"
                            type="number"
                            min="1"
                            max="31"
                            placeholder="e.g. 15"
                            :class="[
                                'app-input w-full rounded-lg border text-sm transition-all duration-200 px-3 py-2.5',
                                'focus:outline-none focus:ring-1 placeholder-slate-600 text-slate-100',
                                form.errors.due_date_day
                                    ? 'border-rose-500/60 bg-rose-950/20 focus:border-rose-400 focus:ring-rose-400/30'
                                    : 'border-border bg-card-bg hover:border-slate-600 focus:border-primary/60 focus:ring-primary/20',
                            ]"
                        />
                        <span class="absolute right-3 text-slate-600 text-xs select-none pointer-events-none">day</span>
                    </div>
                    <p v-if="form.errors.due_date_day" class="mt-1 text-xs text-rose-400 flex items-center gap-1">
                        <AppIcon name="AlertCircle" size="11" />{{ form.errors.due_date_day }}
                    </p>
                </div>
            </div>

            <!-- Status (edit mode only) -->
            <AppSelect
                v-if="debt"
                v-model="form.status"
                label="Status"
                :options="[{value: 'active', label: 'Active'}, {value: 'paid_off', label: 'Paid Off'}]"
                :error="form.errors.status"
            />

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2 border-t border-border mt-2">
                <AppButton type="button" variant="secondary" @click="emit('close')" :disabled="form.processing">
                    Cancel
                </AppButton>
                <AppButton type="submit" variant="primary" :loading="form.processing">
                    {{ debt ? 'Save Changes' : 'Add Debt' }}
                </AppButton>
            </div>
        </form>
    </AppModal>
</template>
