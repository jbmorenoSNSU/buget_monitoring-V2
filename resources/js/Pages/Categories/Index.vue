<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    categories: { type: Object, default: () => ({ data: [] }) },
});

const items = computed(() => props.categories?.data || []);
const activeTab = ref('expense');

const filteredItems = computed(() => {
    return items.value.filter(cat => cat.type === activeTab.value);
});
const deleteTarget = ref(null);
const showDeleteModal = ref(false);

const columns = [
    { key: 'icon', label: 'Icon' },
    { key: 'name', label: 'Name' },
    { key: 'type', label: 'Type' },
    { key: 'color', label: 'Color' },
    { key: 'is_active', label: 'Status' },
    { key: 'actions', label: 'Actions' },
];

const confirmDelete = (cat) => { deleteTarget.value = cat; showDeleteModal.value = true; };
const doDelete = () => { router.delete(`/categories/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };
const toggle = (cat) => router.patch(`/categories/${cat.id}/toggle`);
</script>

<template>
    <AppLayout title="Categories">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-slate-100">All Categories</h2>
            <Link href="/categories/create"><AppButton>+ Add Category</AppButton></Link>
        </div>

        <div class="flex border-b border-[#232936] mb-6">
            <button
                @click="activeTab = 'expense'"
                :class="['px-4 py-3 text-sm font-medium border-b-2 transition-colors', activeTab === 'expense' ? 'border-[#6366F1] text-[#6366F1]' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-[#232936]']"
            >
                Expense Categories
            </button>
            <button
                @click="activeTab = 'income'"
                :class="['px-4 py-3 text-sm font-medium border-b-2 transition-colors', activeTab === 'income' ? 'border-[#6366F1] text-[#6366F1]' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-[#232936]']"
            >
                Income Categories
            </button>
        </div>

        <AppTable :columns="columns" :rows="filteredItems">
            <template #cell-icon="{ row }">
                <AppIcon :name="row.icon || 'Package'" size="20" class="text-slate-400" />
            </template>
            <template #cell-type="{ row }">
                <AppBadge :type="row.type" :label="row.type" />
            </template>
            <template #cell-color="{ row }">
                <div class="w-6 h-6 rounded-full border border-[#232936]" :style="{ backgroundColor: row.color }" />
            </template>
            <template #cell-is_active="{ row }">
                <AppBadge :type="row.is_active ? 'active' : 'inactive'" :label="row.is_active ? 'Active' : 'Inactive'" />
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-2">
                    <Link :href="`/categories/${row.id}/edit`"><AppButton variant="secondary" size="sm">Edit</AppButton></Link>
                    <AppButton variant="ghost" size="sm" @click="toggle(row)">{{ row.is_active ? 'Disable' : 'Enable' }}</AppButton>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(row)">Delete</AppButton>
                </div>
            </template>
        </AppTable>

        <AppModal :show="showDeleteModal" title="Delete Category" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
