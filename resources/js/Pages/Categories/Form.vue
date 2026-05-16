<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import ColorPicker from '@/Components/UI/ColorPicker.vue';
import IconPicker from '@/Components/UI/IconPicker.vue';

const props = defineProps({
    category: { type: Object, default: null },
});

const isEdit = !!props.category;
const cat = props.category?.data || props.category;

const form = useForm({
    name: cat?.name || '',
    type: cat?.type || 'expense',
    icon: cat?.icon || 'tag',
    color: cat?.color || '#6366F1',
});

const typeOptions = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'both', label: 'Both' },
];

const submit = () => {
    if (isEdit) form.put(`/categories/${cat.id}`);
    else form.post('/categories');
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Category' : 'Add Category'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppInput v-model="form.name" label="Category Name" placeholder="e.g. Food & Dining" :error="form.errors.name" required />
                    <AppSelect v-model="form.type" label="Type" :options="typeOptions" :error="form.errors.type" required />
                    <ColorPicker v-model="form.color" label="Color" />
                    <IconPicker v-model="form.icon" label="Icon" />
                    <div class="flex gap-3 pt-4">
                        <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                        <a href="/categories"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
