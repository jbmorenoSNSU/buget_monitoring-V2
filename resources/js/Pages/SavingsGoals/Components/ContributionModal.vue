<script setup lang="ts">
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
    show: boolean;
    form: any;
    goal: any;
}>();

const emit = defineEmits(['close', 'submit']);

const { formatPeso } = useCurrency();

const submitForm = () => {
    emit('submit');
};

const close = () => {
    emit('close');
};
</script>

<template>
    <AppModal :show="show" title="Add Contribution or Withdraw" @close="close">
        <div v-if="goal" class="mb-4">
            <h3 class="font-semibold text-slate-200">{{ goal.name }}</h3>
            <p class="text-sm text-slate-400">Current: {{ formatPeso(goal.current_amount) }} / Target: {{ formatPeso(goal.target_amount) }}</p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
            <div class="flex items-center gap-3 bg-page-bg p-3 rounded-lg border border-border">
                <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-300">
                    <input type="radio" v-model="form.is_withdrawal" :value="false" class="bg-sidebar border-border text-primary focus:ring-primary" />
                    <span>Deposit / Add Funds</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-sm font-medium text-slate-300 ml-4">
                    <input type="radio" v-model="form.is_withdrawal" :value="true" class="bg-sidebar border-border text-rose-500 focus:ring-rose-500" />
                    <span>Withdraw Funds</span>
                </label>
            </div>
            
            <AppInput v-model="form.amount" label="Amount (₱)" type="number" step="0.01" min="0.01" :error="form.errors.amount" required />

            <div class="flex gap-3 pt-4">
                <AppButton type="submit" :loading="form.processing">{{ form.is_withdrawal ? 'Withdraw' : 'Add Contribution' }}</AppButton>
                <AppButton type="button" variant="secondary" @click="close">Cancel</AppButton>
            </div>
        </form>
    </AppModal>
</template>
