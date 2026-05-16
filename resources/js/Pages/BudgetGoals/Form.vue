<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';

const props = defineProps({
    goal: { type: Object, default: null },
    categories: { type: Array, default: () => [] },
});

const isEdit = !!props.goal;
const g = props.goal?.data || props.goal;

const form = useForm({
    category_id: g?.category_id || '',
    month: g?.month || new Date().getMonth() + 1,
    year: g?.year || new Date().getFullYear(),
    limit_amount: g?.limit_amount || '',
});

const catOptions = props.categories.map(c => ({ value: c.id, label: c.name }));
const monthOptions = Array.from({ length: 12 }, (_, i) => ({ value: i + 1, label: new Date(2000, i).toLocaleString('en', { month: 'long' }) }));
const yearOptions = Array.from({ length: 6 }, (_, i) => ({ value: 2023 + i, label: String(2023 + i) }));

const submit = () => {
    if (isEdit) form.put(`/budget-goals/${g.id}`);
    else form.post('/budget-goals');
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Budget Goal' : 'Add Budget Goal'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppSelect v-model="form.category_id" label="Category (Expense Only)" :options="catOptions" :error="form.errors.category_id" required />
                    <div class="grid grid-cols-2 gap-4">
                        <AppSelect v-model="form.month" label="Month" :options="monthOptions" :error="form.errors.month" required />
                        <AppSelect v-model="form.year" label="Year" :options="yearOptions" :error="form.errors.year" required />
                    </div>
                    <AppInput v-model="form.limit_amount" label="Limit Amount (₱)" type="number" step="0.01" :error="form.errors.limit_amount" required />
                    <div class="flex gap-3 pt-4">
                        <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                        <a href="/budget-goals"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
