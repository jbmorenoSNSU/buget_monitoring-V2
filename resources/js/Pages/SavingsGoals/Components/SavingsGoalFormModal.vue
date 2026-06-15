<script setup lang="ts">
import { computed } from 'vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps<{
    show: boolean;
    isEdit: boolean;
    form: any;
    accountOptions: any[];
    personFormOptions: any[];
}>();

const emit = defineEmits(['close', 'submit']);

const submitForm = () => {
    emit('submit');
};

const close = () => {
    emit('close');
};
</script>

<template>
    <AppModal :show="show" :title="isEdit ? 'Edit Savings Goal' : 'Add Savings Goal'" @close="close">
        <form @submit.prevent="submitForm" class="space-y-4">
            <AppInput v-model="form.name" label="Goal Name" placeholder="e.g. Emergency Fund" :error="form.errors.name" required />
            <div class="grid grid-cols-2 gap-4">
                <AppInput v-model="form.target_amount" label="Target Amount (₱)" type="number" step="0.01" min="1" :error="form.errors.target_amount" required />
                <AppInput v-model="form.current_amount" label="Current Saved (₱)" type="number" step="0.01" min="0" :error="form.errors.current_amount" required />
            </div>
            <AppSelect v-model="form.person_id" label="Linked Person" :options="personFormOptions" :error="form.errors.person_id" />
            <AppSelect v-model="form.account_id" label="Linked Bank Account" :options="accountOptions" :error="form.errors.account_id" />
            <AppInput v-model="form.target_date" label="Target Date (Optional)" type="date" :error="form.errors.target_date" />

            <div class="flex gap-3 pt-4">
                <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update Goal' : 'Save Goal' }}</AppButton>
                <AppButton type="button" variant="secondary" @click="close">Cancel</AppButton>
            </div>
        </form>
    </AppModal>
</template>
