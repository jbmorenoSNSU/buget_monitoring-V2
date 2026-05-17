<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import ColorPicker from '@/Components/UI/ColorPicker.vue';

const props = defineProps({
    person: { type: Object, default: null },
});

const isEdit = !!props.person;
const p = props.person?.data || props.person;

const form = useForm({
    name: p?.name || '',
    color: p?.color || '#6366F1',
});

const submit = () => {
    if (isEdit) {
        form.put(`/persons/${p.id}`);
    } else {
        form.post('/persons');
    }
};
</script>

<template>
    <AppLayout :title="isEdit ? 'Edit Person' : 'Add Person'">
        <div class="max-w-2xl mx-auto">
            <AppCard>
                <form @submit.prevent="submit" class="space-y-5">
                    <AppInput v-model="form.name" label="Person Name" placeholder="e.g. Andrew" :error="form.errors.name" required />
                    <ColorPicker v-model="form.color" label="Color" />
                    <div class="flex gap-3 pt-4">
                        <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update Person' : 'Create Person' }}</AppButton>
                        <a href="/persons"><AppButton type="button" variant="secondary">Cancel</AppButton></a>
                    </div>
                </form>
            </AppCard>
        </div>
    </AppLayout>
</template>
