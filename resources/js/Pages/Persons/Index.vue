<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Persons');
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

const props = defineProps<{
    persons: Record<string, any>;
}>();

const { formatPeso } = useCurrency();
const items = computed(() => props.persons?.data || []);
const deleteTarget = ref<any>(null);
const showDeleteModal = ref(false);

const confirmDelete = (person: any) => { deleteTarget.value = person; showDeleteModal.value = true; };
const doDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/persons/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } });
    }
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
    form.clearErrors();
    form.id = null;
    form.name = '';
    form.color = '#6366F1';
    showFormModal.value = true;
};

const openEditModal = (person: any) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = person.id;
    form.name = person.name;
    form.color = person.color;
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/persons/${form.id}`, { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    } else {
        form.post('/persons', { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    }
};

// Dropdown state
const activeDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number, event: Event) => {
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
    <Head title="Persons" />
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-100">Manage Persons</h2>
            <AppButton @click="openAddModal" class="shrink-0">
                <AppIcon name="Plus" size="18" class="mr-2" />
                Add Person
            </AppButton>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div v-for="person in items" :key="person.id"
                class="group bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative flex flex-col justify-between"
                :style="{ boxShadow: `inset 0 2px 0 0 ${person.color}` }">
                
                <!-- Background Watermark Icon -->
                <AppIcon name="User" size="100" 
                    class="absolute -bottom-4 -right-4 text-slate-100 opacity-[0.02] pointer-events-none transform group-hover:scale-110 group-hover:opacity-[0.04] transition-all duration-500" />
                
                <!-- Subtle glow effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"
                     :style="{ background: `radial-gradient(circle at 50% 0%, ${person.color}, transparent 70%)` }">
                </div>

                <div class="p-5 flex flex-col h-full relative z-10">
                    <!-- Top Section: Avatar, Name & Dropdown -->
                    <div class="flex items-start justify-between mb-5">
                        <div class="flex items-center gap-3 max-w-[80%]">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0 shadow-sm"
                                :style="{ backgroundColor: person.color + '20', color: person.color, border: `1px solid ${person.color}40` }">
                                {{ person.name.charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-100 text-lg truncate leading-snug">{{ person.name }}</h3>
                                <p class="text-xs text-slate-400 truncate mt-0.5">{{ person.accounts_count }} active account{{ person.accounts_count !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                        
                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0 -mr-1">
                            <button @click="toggleDropdown(person.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === person.id" 
                                class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-20"
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

                    <!-- Stats Box -->
                    <div class="bg-sidebar/50 rounded-lg p-4 space-y-3 mt-auto border border-border/30 backdrop-blur-sm relative z-10">
                        <div>
                            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-1">Total Balance</p>
                            <p :class="['text-2xl font-black tracking-tight', person.total_balance >= 0 ? 'text-slate-50' : 'text-rose-400']">
                                {{ formatPeso(person.total_balance) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-border/40">
                            <div>
                                <p class="text-[9px] text-slate-500 font-medium uppercase tracking-wider mb-0.5">Income</p>
                                <p class="text-xs font-semibold text-emerald-400">{{ formatPeso(person.income_this_month) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] text-slate-500 font-medium uppercase tracking-wider mb-0.5">Expense</p>
                                <p class="text-xs font-semibold text-rose-400">{{ formatPeso(person.expense_this_month) }}</p>
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
    </div>
</template>
