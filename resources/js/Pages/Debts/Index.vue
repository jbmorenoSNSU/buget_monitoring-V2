<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import DebtFormModal from './Components/DebtFormModal.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
    debts: any;
    filters: any;
}>();

const { formatPeso } = useCurrency();

const selectedPerson = ref(props.filters?.selectedPersonId ? props.filters.selectedPersonId.toString() : '');
const isFormModalOpen = ref(false);
const editingDebt = ref<any>(null);

const personOptions = computed(() => [
    { value: '', label: 'Everyone' },
    ...(props.filters?.persons || []).map((p: any) => ({ value: p.id.toString(), label: p.name })),
]);

const onPersonChange = () => {
    const params = selectedPerson.value ? { person_id: selectedPerson.value } : {};
    router.get('/debts', params, { preserveState: false });
};

const openCreateModal = () => {
    editingDebt.value = null;
    isFormModalOpen.value = true;
};

const openEditModal = (debt: any) => {
    editingDebt.value = debt;
    isFormModalOpen.value = true;
};

const showDeleteModal = ref(false);
const deleteTarget = ref<any>(null);

const handleDelete = (debt: any) => {
    deleteTarget.value = debt;
    showDeleteModal.value = true;
};

const doDelete = () => {
    if (deleteTarget.value) {
        router.delete(`/debts/${deleteTarget.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            }
        });
    }
};

const calculateProgress = (debt: any) => {
    if (debt.principal_amount <= 0 || debt.status === 'paid_off') return 100;
    return 0;
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
    <AppLayout title="Debt Payoff Planner">
        <!-- Top Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-400 font-medium">View as:</span>
                <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" @change="onPersonChange" />
            </div>

            <AppButton @click="openCreateModal" class="shrink-0">
                <AppIcon name="Plus" size="18" class="mr-2" />
                Add Debt / Loan
            </AppButton>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div v-for="debt in debts.data" :key="debt.id" 
                class="group bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative p-5 flex flex-col justify-between"
                :style="{ boxShadow: debt.status === 'active' ? `inset 0 2px 0 0 #f43f5e` : `inset 0 2px 0 0 #10b981` }">
                
                <!-- Background Watermark Icon -->
                <AppIcon name="CreditCard" size="100" 
                    class="absolute -bottom-4 -right-4 text-slate-100 opacity-[0.02] pointer-events-none transform group-hover:scale-110 group-hover:opacity-[0.04] transition-all duration-500" />
                
                <!-- Subtle glow effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"
                     :style="{ background: debt.status === 'active' ? `radial-gradient(circle at 50% 0%, #f43f5e, transparent 70%)` : `radial-gradient(circle at 50% 0%, #10b981, transparent 70%)` }">
                </div>

                <div class="relative z-10 flex flex-col h-full">
                    <!-- Top Section: Icon, Name & Dropdown -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 max-w-[85%]">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm shrink-0"
                                :style="{ backgroundColor: debt.person ? debt.person.color + '20' : '#6366F120', color: debt.person ? debt.person.color : '#6366F1', border: `1px solid ${debt.person ? debt.person.color : '#6366F1'}40` }">
                                <AppIcon name="Briefcase" size="20" v-if="!debt.person" />
                                <span v-else class="font-bold text-sm">{{ debt.person.name.charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-100 text-base truncate leading-snug" :title="debt.name">{{ debt.name }}</h3>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span v-if="debt.person" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none" 
                                        :style="{ backgroundColor: (debt.person.color || '#94A3B8') + '30', color: debt.person.color || '#94A3B8', filter: 'brightness(1.4)' }">
                                        {{ debt.person.name }}
                                    </span>
                                    <span v-else class="text-[10px] font-medium text-indigo-400">Shared Debt</span>
                                    <span class="text-[10px] text-slate-600 px-0.5">•</span>
                                    <span class="text-[10px] text-slate-400 leading-none">
                                        {{ debt.status === 'active' ? `${debt.interest_rate}% APR` : 'Paid Off' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0 -mr-1">
                            <button @click="toggleDropdown(debt.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === debt.id" 
                                class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-20"
                                @click.stop>
                                <button @click="openEditModal(debt); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Edit2" size="12" /> Edit
                                </button>
                                <div class="border-t border-border my-1"></div>
                                <button @click="handleDelete(debt); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Trash2" size="12" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Main Balance -->
                    <div class="mb-4">
                        <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Current Balance</p>
                        <p :class="['text-2xl font-black tracking-tight', debt.status === 'active' ? 'text-expense' : 'text-emerald-400']">{{ formatPeso(debt.principal_amount) }}</p>
                    </div>

                    <!-- Stats Box -->
                    <div class="bg-sidebar/50 rounded-lg p-3 space-y-2 mb-1 border border-border/30 backdrop-blur-sm mt-auto">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Min. Payment</span>
                            <span class="font-medium text-slate-200">{{ formatPeso(debt.minimum_payment) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm" v-if="debt.due_date_day">
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Due Date</span>
                            <span class="font-medium text-slate-200">{{ debt.due_date_day }}th</span>
                        </div>
                        
                        <div class="pt-2 border-t border-border/40">
                            <div v-if="debt.status === 'active'">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Est. Payoff</span>
                                    <span class="text-emerald-400 font-semibold text-xs" v-if="debt.payoff_projection.is_possible">{{ debt.payoff_projection.payoff_date }}</span>
                                    <span class="text-expense font-semibold text-xs" v-else>Never</span>
                                </div>
                                <div class="flex justify-between items-center" v-if="debt.payoff_projection.is_possible">
                                    <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Time Left</span>
                                    <span class="text-xs text-slate-300">{{ debt.payoff_projection.months }} months</span>
                                </div>
                                <div class="text-[10px] text-expense mt-1 leading-tight" v-if="!debt.payoff_projection.is_possible">
                                    Min payment is less than interest!
                                </div>
                            </div>
                            <div v-else class="text-center py-1 text-emerald-500 font-bold flex items-center justify-center gap-1.5 text-xs">
                                <AppIcon name="CheckCircle" size="14" /> Debt Free!
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!debts.data.length" class="col-span-full py-12 text-center border-2 border-dashed border-border rounded-xl">
                <AppIcon name="CheckCircle" size="48" class="mx-auto text-slate-500 mb-4" />
                <h3 class="text-lg font-medium text-slate-300">No debts tracked</h3>
                <p class="text-slate-500 mt-1">You're completely debt free, or haven't added any loans yet.</p>
            </div>
        </div>

        <AppPagination :links="{ prev: debts.prev_page_url, next: debts.next_page_url }" :meta="debts" class="mt-6" />

        <DebtFormModal
            :show="isFormModalOpen"
            :debt="editingDebt"
            :persons="filters.persons"
            @close="isFormModalOpen = false"
        />

        <AppModal :show="showDeleteModal" title="Delete Debt" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? This action cannot be undone and will permanently remove this debt record.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
