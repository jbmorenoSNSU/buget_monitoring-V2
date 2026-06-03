<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import ColorPicker from '@/Components/UI/ColorPicker.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps({
    persons: { type: Object, default: () => ({ data: [] }) },
});

const { formatPeso } = useCurrency();
const items = computed(() => props.persons?.data || []);
const deleteTarget = ref(null);
const showDeleteModal = ref(false);

const confirmDelete = (person) => { deleteTarget.value = person; showDeleteModal.value = true; };
const doDelete = () => {
    router.delete(`/persons/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } });
};

// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    name: '',
    color: '#6366F1',
});

const openAddModal = () => {
    isEdit.value = false;
    form.reset();
    form.clearErrors();
    showFormModal.value = true;
};

const openEditModal = (person) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = person.id;
    form.name = person.name;
    form.color = person.color;
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/persons/${form.id}`, { onSuccess: () => { showFormModal.value = false; } });
    } else {
        form.post('/persons', { onSuccess: () => { showFormModal.value = false; } });
    }
};

// Dropdown state
const activeDropdownId = ref(null);
const toggleDropdown = (id, event) => {
    event.stopPropagation();
    activeDropdownId.value = activeDropdownId.value === id ? null : id;
};
const closeDropdown = () => {
    activeDropdownId.value = null;
};
onMounted(() => {
    window.addEventListener('click', closeDropdown);
});
onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <AppLayout title="Persons">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-slate-100">Manage Persons</h2>
            <AppButton @click="openAddModal">+ Add Person</AppButton>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="person in items" :key="person.id"
                class="bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow relative">
                <div class="h-1" :style="{ backgroundColor: person.color }" />
                
                <div class="p-4">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2 max-w-[80%]">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs shrink-0 shadow-md"
                                :style="{ backgroundColor: person.color + '30', color: person.color }">
                                {{ person.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-100 text-sm truncate leading-snug">{{ person.name }}</h3>
                                <p class="text-[11px] text-slate-400 truncate leading-none mt-0.5">{{ person.accounts_count }} active account{{ person.accounts_count !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        
                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0">
                            <button @click="toggleDropdown(person.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === person.id" 
                                class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-10"
                                @click.stop>
                                <button @click="openEditModal(person); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Edit2" size="12" /> Edit
                                </button>
                                <div class="border-t border-border my-1"></div>
                                <button @click="confirmDelete(person); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Trash2" size="12" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-page-bg/50 rounded-lg p-2.5 space-y-2">
                        <div>
                            <p class="text-[10px] text-slate-400 mb-0.5">Total Balance</p>
                            <p :class="['text-lg font-bold', person.total_balance >= 0 ? 'text-slate-50' : 'text-expense']">
                                {{ formatPeso(person.total_balance) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-border/50">
                            <div>
                                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-wider mb-0.5">Income</p>
                                <p class="text-xs font-semibold text-income">{{ formatPeso(person.income_this_month) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-400 font-medium uppercase tracking-wider mb-0.5">Expense</p>
                                <p class="text-xs font-semibold text-expense">{{ formatPeso(person.expense_this_month) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="!items.length" class="text-center text-slate-400 py-12">No persons added yet. Create your first person to start tracking account ownership!</p>

        <AppModal :show="showDeleteModal" title="Delete Person" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? You must reassign their accounts first.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Person' : 'Add Person'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <AppInput v-model="form.name" label="Person Name" placeholder="e.g. Andrew" :error="form.errors.name" required />
                <ColorPicker v-model="form.color" label="Color" />
                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update Person' : 'Create Person' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </AppLayout>
</template>
