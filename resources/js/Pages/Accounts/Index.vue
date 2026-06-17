<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Accounts');
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    accounts: Record<string, any>;
    totalBalance: number;
    accountTypes: any[];
    persons: any[];
}>();

const { formatPeso } = useCurrency();
const items = computed(() => props.accounts?.data || []);
const deleteTarget = ref<any>(null);
const showDeleteModal = ref(false);
const selectedPerson = ref('');

// Extract unique persons from accounts for the filter dropdown
const personOptions = computed(() => {
    const persons = [];
    const seen = new Set();
    for (const acc of items.value) {
        if (acc.person && !seen.has(acc.person.id)) {
            seen.add(acc.person.id);
            persons.push({ value: acc.person.id.toString(), label: acc.person.name });
        }
    }
    persons.sort((a, b) => a.label.localeCompare(b.label));
    return [{ value: '', label: 'All Owners' }, ...persons];
});

const filteredItems = computed(() => {
    if (!selectedPerson.value) return items.value;
    return items.value.filter((acc: any) => acc.person?.id?.toString() === selectedPerson.value);
});

const displayBalance = computed(() => {
    if (!selectedPerson.value) return props.totalBalance;
    return filteredItems.value
        .filter((acc: any) => acc.is_active)
        .reduce((sum: number, acc: any) => sum + acc.current_balance, 0);
});

const confirmDelete = (acc: any) => { deleteTarget.value = acc; showDeleteModal.value = true; };
const doDelete = () => {
    if(deleteTarget.value) router.delete(`/accounts/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } });
};
const toggle = (acc: any) => router.patch(`/accounts/${acc.id}/toggle`);

const typeOptions = computed(() => props.accountTypes.map(t => ({ value: t.id, label: t.name })));
const formPersonOptions = computed(() => [
    { value: '', label: 'No Owner (Shared)' },
    ...props.persons.map(p => ({ value: p.id, label: p.name }))
]);

// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    account_type_id: '',
    person_id: '',
    name: '',
    description: '',
    initial_balance: 0,
});

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.id = null;
    form.account_type_id = '';
    form.person_id = '';
    form.name = '';
    form.description = '';
    form.initial_balance = 0;
    showFormModal.value = true;
};

const openEditModal = (acc: any) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = acc.id;
    form.account_type_id = acc.account_type_id || acc.accountType?.id || acc.account_type?.id || '';
    form.person_id = acc.person_id || acc.person?.id || '';
    form.name = acc.name;
    form.description = acc.description;
    form.initial_balance = acc.initial_balance;
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/accounts/${form.id}`, { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    } else {
        form.post('/accounts', { onSuccess: () => { showFormModal.value = false; form.reset(); } });
    }
};

// Dropdown state
const activeDropdownId = ref<number | string | null>(null);
const toggleDropdown = (id: number | string, event: Event) => {
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
    <Head title="Accounts" />
    <div>
        <!-- Top Metrics Row -->
        <div class="mb-8">
            <StatCard label="Total Balance" :value="formatPeso(displayBalance)" accentColor="#6366F1" class="max-w-sm" />
        </div>

        <!-- Action Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <h2 class="text-lg font-semibold text-slate-200">Your Accounts</h2>
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 w-full sm:w-auto">
                <div class="flex items-center gap-3">
                    <span class="text-sm text-slate-400 font-medium whitespace-nowrap">View as:</span>
                    <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" />
                </div>
                <AppButton @click="openAddModal" class="shrink-0">
                    <AppIcon name="Plus" size="18" class="mr-2" />
                    Add Account
                </AppButton>
            </div>
        </div>

        <!-- Accounts Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div v-for="acc in filteredItems" :key="acc.id"
                class="group bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative flex flex-col justify-between h-44"
                :style="{ boxShadow: `inset 0 2px 0 0 ${acc.color}` }">
                
                <!-- Background Watermark Icon -->
                <AppIcon :name="acc.account_type?.icon || 'Wallet'" size="80" 
                    class="absolute -bottom-4 -right-4 text-slate-100 opacity-[0.02] pointer-events-none transform group-hover:scale-110 group-hover:opacity-[0.04] transition-all duration-500" />
                
                <!-- Subtle glow effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"
                     :style="{ background: `radial-gradient(circle at 50% 0%, ${acc.color}, transparent 70%)` }">
                </div>

                <div class="p-5 flex flex-col h-full relative z-10">
                    <!-- Top Section: Owner & Actions -->
                    <div class="flex items-start justify-between mb-auto">
                        <div v-if="acc.person" class="flex items-center gap-2 min-w-0" :title="acc.person.name">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
                                :style="{ backgroundColor: acc.person.color + '20', color: acc.person.color, border: `1px solid ${acc.person.color}40` }">
                                {{ acc.person.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-xs font-medium truncate" :style="{ color: acc.person.color }">{{ acc.person.name }}</span>
                        </div>
                        <div v-else class="h-6 flex items-center"><span class="text-xs text-slate-500 font-medium italic">Shared</span></div>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <AppBadge :type="acc.is_active ? 'active' : 'inactive'" :label="acc.is_active ? 'Active' : 'Inactive'" class="scale-75 origin-right" />
                            
                            <!-- Dropdown Menu trigger -->
                            <div class="relative shrink-0 -mr-1">
                                <button @click="toggleDropdown(acc.id, $event)" 
                                    class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                    <AppIcon name="MoreVertical" size="16" />
                                </button>
                                
                                <!-- Dropdown List -->
                                <div v-if="activeDropdownId === acc.id" 
                                    class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-20"
                                    @click.stop>
                                    <button @click="openEditModal(acc); activeDropdownId = null" 
                                        class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                        <AppIcon name="Edit2" size="12" /> Edit
                                    </button>
                                    <button @click="toggle(acc); activeDropdownId = null" 
                                        class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                        <AppIcon :name="acc.is_active ? 'EyeOff' : 'Eye'" size="12" />
                                        {{ acc.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <div class="border-t border-border my-1"></div>
                                    <button @click="confirmDelete(acc); activeDropdownId = null" 
                                        class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                        <AppIcon name="Trash2" size="12" /> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Section: Title & Type -->
                    <div class="mb-3">
                        <h3 class="font-bold text-slate-100 text-base truncate" :title="acc.name">{{ acc.name }}</h3>
                        <p class="text-[11px] text-slate-400 truncate flex items-center gap-1 mt-0.5" :title="acc.description || acc.account_type?.name">
                            <AppIcon :name="acc.account_type?.icon || 'Wallet'" size="12" class="opacity-70" />
                            {{ acc.account_type?.name }}
                        </p>
                    </div>

                    <!-- Bottom Section: Balance -->
                    <div class="mt-auto">
                        <p class="text-xs text-slate-500 mb-0.5">Current Balance</p>
                        <p :class="['text-2xl font-black tracking-tight', acc.current_balance >= 0 ? 'text-slate-50' : 'text-rose-400']">
                            {{ formatPeso(acc.current_balance) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="!filteredItems.length" class="text-center text-slate-400 py-12">
            {{ selectedPerson ? 'No accounts found for this person.' : 'No accounts yet. Create your first account!' }}
        </p>

        <AppModal :show="showDeleteModal" title="Delete Account" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Account' : 'Add Account'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <AppSelect v-model="form.account_type_id" label="Account Type" :options="typeOptions" :error="form.errors.account_type_id" required />
                <AppSelect v-model="form.person_id" label="Owner" :options="formPersonOptions" :error="form.errors.person_id" />
                <AppInput v-model="form.name" label="Account Name" placeholder="e.g. BDO Savings" :error="form.errors.name" required />
                <AppInput v-model="form.description" label="Description" placeholder="Optional description" :error="form.errors.description" />
                <AppInput v-if="!isEdit" v-model="form.initial_balance" label="Initial Balance" type="number" step="0.01" :error="form.errors.initial_balance" required />
                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update Account' : 'Create Account' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </div>
</template>
