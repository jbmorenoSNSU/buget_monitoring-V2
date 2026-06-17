<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Categories');
import { computed, ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
import ColorPicker from '@/Components/UI/ColorPicker.vue';
import IconPicker from '@/Components/UI/IconPicker.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    categories: Record<string, any>;
}>();

const items = computed(() => props.categories?.data || []);
const activeTab = ref('expense');
// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    name: '',
    type: 'expense',
    icon: 'tag',
    color: '#6366F1',
});

const typeOptions = [
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' },
    { value: 'both', label: 'Both' },
];

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.id = null;
    form.name = '';
    form.type = activeTab.value === 'both' ? 'expense' : activeTab.value;
    form.icon = 'tag';
    form.color = '#6366F1';
    showFormModal.value = true;
};

const openEditModal = (cat: any) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = cat.id;
    form.name = cat.name;
    form.type = cat.type;
    form.icon = cat.icon;
    form.color = cat.color;
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/categories/${form.id}`, { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    } else {
        form.post('/categories', { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    }
};

// Client-Side Datatable States
const search = ref('');
const sortBy = ref('name');
const sortDirection = ref('asc');
const perPage = ref('10');
const currentPage = ref(1);

const handleSort = (columnKey: string) => {
    if (sortBy.value === columnKey) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = columnKey;
        sortDirection.value = 'asc';
    }
};

const filteredAndSortedItems = computed(() => {
    // 1. Filter by tab type (include 'both' in both tabs)
    let list = items.value.filter((cat: any) => cat.type === activeTab.value || cat.type === 'both');

    // 2. Filter by search string
    if (search.value.trim()) {
        const q = search.value.toLowerCase().trim();
        list = list.filter((cat: any) => {
            const name = (cat.name || '').toLowerCase();
            const type = (cat.type || '').toLowerCase();
            const statusLabel = cat.is_active ? 'active' : 'inactive';
            return name.includes(q) || type.includes(q) || statusLabel.includes(q);
        });
    }

    // 3. Sort list client-side
    return [...list].sort((a, b) => {
        let valA = a[sortBy.value];
        let valB = b[sortBy.value];

        // Handle boolean mapping for status sorting
        if (sortBy.value === 'is_active') {
            valA = a.is_active ? 1 : 0;
            valB = b.is_active ? 1 : 0;
        }

        if (valA === undefined || valA === null) return 1;
        if (valB === undefined || valB === null) return -1;

        let comparison = 0;
        if (typeof valA === 'string') {
            comparison = valA.localeCompare(valB);
        } else {
            comparison = valA - valB;
        }

        return sortDirection.value === 'asc' ? comparison : -comparison;
    });
});

// Client-Side Pagination Calculations
const totalItems = computed(() => filteredAndSortedItems.value.length);
const lastPage = computed(() => Math.ceil(totalItems.value / parseInt(perPage.value)) || 1);

const paginatedItems = computed(() => {
    const start = (currentPage.value - 1) * parseInt(perPage.value);
    const end = start + parseInt(perPage.value);
    return filteredAndSortedItems.value.slice(start, end);
});

// Reset current page when filters, sorting or page limit changes
watch([search, activeTab, sortBy, sortDirection, perPage], () => {
    currentPage.value = 1;
});

const paginationMeta = computed(() => {
    const total = totalItems.value;
    if (total === 0) return { from: 0, to: 0, total: 0 };
    const from = (currentPage.value - 1) * parseInt(perPage.value) + 1;
    const to = Math.min(from + parseInt(perPage.value) - 1, total);
    return { from, to, total };
});

const paginationLinks = computed(() => {
    const links = [];
    const current = currentPage.value;
    const last = lastPage.value;

    // Previous link
    links.push({
        label: '&laquo; Previous',
        url: current > 1 ? 'prev' : null,
        active: false
    });

    // Page links
    for (let i = 1; i <= last; i++) {
        links.push({
            label: i.toString(),
            url: i.toString(),
            active: i === current
        });
    }

    // Next link
    links.push({
        label: 'Next &raquo;',
        url: current < last ? 'next' : null,
        active: false
    });

    return links;
});

const handlePageNavigate = (pageStr: string) => {
    if (pageStr === 'prev') {
        currentPage.value = Math.max(1, currentPage.value - 1);
    } else if (pageStr === 'next') {
        currentPage.value = Math.min(lastPage.value, currentPage.value + 1);
    } else {
        currentPage.value = parseInt(pageStr);
    }
};

const deleteTarget = ref<any>(null);
const showDeleteModal = ref(false);

const columns = [
    { key: 'icon', label: 'Icon' },
    { key: 'name', label: 'Name', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'color', label: 'Color' },
    { key: 'is_active', label: 'Status', sortable: true },
    { key: 'actions', label: 'Actions' },
];

const confirmDelete = (cat: any) => { deleteTarget.value = cat; showDeleteModal.value = true; };
const doDelete = () => { if(deleteTarget.value) router.delete(`/categories/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };
const toggle = (cat: any) => router.patch(`/categories/${cat.id}/toggle`);

const perPageOptions = [
    { value: '5', label: 'Show 5 entries' },
    { value: '10', label: 'Show 10 entries' },
    { value: '25', label: 'Show 25 entries' },
    { value: '50', label: 'Show 50 entries' },
];
</script>

<template>
    <Head title="Categories" />
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">All Categories</h2>
            <AppButton @click="openAddModal" class="shrink-0">
                <AppIcon name="Plus" size="18" class="mr-2" />
                Add Category
            </AppButton>
        </div>

        <div class="flex gap-2 mb-6 border-b border-border">
            <button
                @click="activeTab = 'expense'"
                :class="['px-4 py-3 text-sm font-medium border-b-2 transition-colors -mb-px outline-none', activeTab === 'expense' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-border']"
            >
                Expense Categories
            </button>
            <button
                @click="activeTab = 'income'"
                :class="['px-4 py-3 text-sm font-medium border-b-2 transition-colors -mb-px outline-none', activeTab === 'income' ? 'border-primary text-primary' : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-border']"
            >
                Income Categories
            </button>
        </div>

        <div class="flex justify-between items-end mb-4 gap-4">
            <!-- Search -->
            <div class="w-full sm:w-72">
                <AppInput v-model="search" placeholder="Search categories..." />
            </div>

            <!-- Page Size -->
            <div class="hidden sm:flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Page Size:</span>
                <AppSelect v-model="perPage" :options="perPageOptions" class="w-40 select-none" />
            </div>
        </div>

        <div class="flex sm:hidden justify-end items-center mb-4 gap-2">
            <span class="text-xs text-slate-400 font-medium">Page Size:</span>
            <AppSelect v-model="perPage" :options="perPageOptions" class="w-32 select-none" />
        </div>

        <AppTable
            :columns="columns"
            :rows="paginatedItems"
            :sort-by="sortBy"
            :sort-direction="sortDirection"
            @sort="handleSort"
        >
            <template #cell-icon="{ row }">
                <AppIcon :name="row.icon || 'Package'" size="20" class="text-slate-400" />
            </template>
            <template #cell-type="{ row }">
                <AppBadge :type="row.type" :label="row.type" />
            </template>
            <template #cell-color="{ row }">
                <div class="w-6 h-6 rounded-full border border-border" :style="{ backgroundColor: row.color }" />
            </template>
            <template #cell-is_active="{ row }">
                <AppBadge :type="row.is_active ? 'active' : 'inactive'" :label="row.is_active ? 'Active' : 'Inactive'" />
            </template>
            <template #cell-actions="{ row }">
                <div class="flex gap-2">
                    <AppButton variant="secondary" size="sm" @click="openEditModal(row)">Edit</AppButton>
                    <AppButton variant="ghost" size="sm" @click="toggle(row)">{{ row.is_active ? 'Disable' : 'Enable' }}</AppButton>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(row)">Delete</AppButton>
                </div>
            </template>
            <template #pagination>
                <AppPagination
                    :links="paginationLinks"
                    :meta="paginationMeta"
                    :client-side="true"
                    @navigate="handlePageNavigate"
                />
            </template>
        </AppTable>

        <AppModal :show="showDeleteModal" title="Delete Category" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Category' : 'Add Category'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <AppInput v-model="form.name" label="Category Name" placeholder="e.g. Food & Dining" :error="form.errors.name" required />
                <AppSelect v-model="form.type" label="Type" :options="typeOptions" :error="form.errors.type" required />
                <ColorPicker v-model="form.color" label="Color" />
                <IconPicker v-model="form.icon" label="Icon" />
                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </div>
</template>
