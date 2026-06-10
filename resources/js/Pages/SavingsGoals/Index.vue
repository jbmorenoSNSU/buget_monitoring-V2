<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
    goals: any[];
    accounts: any[];
    persons: any[];
}>();

const { formatPeso } = useCurrency();

// Filter state
const selectedPerson = ref('');

const personFilterOptions = computed(() => [
    { value: '', label: 'Everyone' },
    ...props.persons.map(p => ({ value: p.id.toString(), label: p.name })),
]);

const filteredGoals = computed(() => {
    if (!selectedPerson.value) return props.goals;
    return props.goals.filter(g => g.person_id?.toString() === selectedPerson.value);
});

// Modals State
const isFormModalOpen = ref(false);
const isContributionModalOpen = ref(false);
const showDeleteModal = ref(false);
const editingGoal = ref<any>(null);
const contributionGoal = ref<any>(null);
const deleteTarget = ref<any>(null);

// Forms
const goalForm = useForm({
    id: null as number | null,
    name: '',
    target_amount: '',
    current_amount: '0',
    account_id: '' as string | number,
    person_id: '' as string | number,
    target_date: '',
});

const contributionForm = useForm({
    amount: '',
    is_withdrawal: false,
});

// Dropdowns
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

// Computed Stats over Filtered Goals
const totalTarget = computed(() => filteredGoals.value.reduce((sum, g) => sum + parseFloat(g.target_amount), 0));
const totalSaved = computed(() => filteredGoals.value.reduce((sum, g) => sum + parseFloat(g.current_amount), 0));
const totalRemaining = computed(() => filteredGoals.value.reduce((sum, g) => sum + parseFloat(g.remaining_amount), 0));
const completedCount = computed(() => filteredGoals.value.filter(g => g.is_completed).length);

// Check for Over-Allocated Accounts
const overAllocatedAccounts = computed(() => {
    const allocationMap: Record<number, number> = {};
    props.goals.forEach(g => {
        if (g.account_id) {
            const accId = parseInt(g.account_id);
            allocationMap[accId] = (allocationMap[accId] || 0) + parseFloat(g.current_amount);
        }
    });

    const list: any[] = [];
    props.accounts.forEach(acc => {
        const allocated = allocationMap[acc.id] || 0;
        const balance = parseFloat(acc.current_balance);
        if (allocated > balance) {
            list.push({
                name: acc.name,
                balance,
                allocated,
                excess: allocated - balance,
            });
        }
    });
    return list;
});

// Account Options
const accountOptions = computed(() => [
    { value: '', label: 'No linked account' },
    ...props.accounts.map(acc => ({
        value: acc.id.toString(),
        label: `${acc.name} (Bal: ${formatPeso(acc.current_balance)})`,
    })),
]);

// Person Options for Selection Form
const personFormOptions = computed(() => [
    { value: '', label: 'Shared / No Owner' },
    ...props.persons.map(p => ({
        value: p.id.toString(),
        label: p.name,
    })),
]);

// CRUD Handlers
const openCreateModal = () => {
    editingGoal.value = null;
    goalForm.reset();
    goalForm.id = null;
    isFormModalOpen.value = true;
};

const openEditModal = (goal: any) => {
    editingGoal.value = goal;
    goalForm.id = goal.id;
    goalForm.name = goal.name;
    goalForm.target_amount = goal.target_amount.toString();
    goalForm.current_amount = goal.current_amount.toString();
    goalForm.account_id = goal.account_id ? goal.account_id.toString() : '';
    goalForm.person_id = goal.person_id ? goal.person_id.toString() : '';
    goalForm.target_date = goal.target_date ? goal.target_date.split('T')[0] : '';
    isFormModalOpen.value = true;
};

const openContributionModal = (goal: any) => {
    contributionGoal.value = goal;
    contributionForm.reset();
    isContributionModalOpen.value = true;
};

const submitGoalForm = () => {
    if (goalForm.id) {
        goalForm.put(route('savings-goals.update', goalForm.id), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                goalForm.reset();
            },
        });
    } else {
        goalForm.post(route('savings-goals.store'), {
            onSuccess: () => {
                isFormModalOpen.value = false;
                goalForm.reset();
            },
        });
    }
};

const submitContributionForm = () => {
    if (!contributionGoal.value) return;

    const amount = parseFloat(contributionForm.amount);
    if (isNaN(amount) || amount <= 0) return;

    const currentAmt = parseFloat(contributionGoal.value.current_amount);
    let nextAmt = currentAmt;
    
    if (contributionForm.is_withdrawal) {
        nextAmt = Math.max(0, currentAmt - amount);
    } else {
        nextAmt = currentAmt + amount;
    }

    const payload = {
        name: contributionGoal.value.name,
        target_amount: contributionGoal.value.target_amount,
        current_amount: nextAmt,
        account_id: contributionGoal.value.account_id || '',
        person_id: contributionGoal.value.person_id || '',
        target_date: contributionGoal.value.target_date ? contributionGoal.value.target_date.split('T')[0] : '',
    };

    router.put(route('savings-goals.update', contributionGoal.value.id), payload, {
        onSuccess: () => {
            isContributionModalOpen.value = false;
            contributionGoal.value = null;
        },
    });
};

const handleDelete = (goal: any) => {
    deleteTarget.value = goal;
    showDeleteModal.value = true;
};

const doDelete = () => {
    if (deleteTarget.value) {
        router.delete(route('savings-goals.destroy', deleteTarget.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                deleteTarget.value = null;
            },
        });
    }
};

const getDaysRemainingText = (goal: any) => {
    if (goal.days_remaining === null) return null;
    if (goal.days_remaining < 0) {
        return `Overdue by ${Math.abs(goal.days_remaining)} days`;
    }
    if (goal.days_remaining === 0) {
        return 'Target due today';
    }
    return `${goal.days_remaining} days left`;
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
};
</script>

<template>
    <AppLayout title="Savings Goals Tracker">
        <!-- Filter and Top Actions Bar -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-400 font-medium">View as:</span>
                <AppSelect v-model="selectedPerson" :options="personFilterOptions" class="w-44" />
            </div>

            <AppButton @click="openCreateModal" class="shrink-0">
                <AppIcon name="Plus" size="18" class="mr-2" />
                Add Savings Goal
            </AppButton>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard
                title="Total Saved"
                :value="formatPeso(totalSaved)"
                icon="Compass"
                color="text-income"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Total Target"
                :value="formatPeso(totalTarget)"
                icon="Target"
                color="text-indigo-400"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Remaining Needed"
                :value="formatPeso(totalRemaining)"
                icon="ArrowUpDown"
                color="text-warning"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Completed Goals"
                :value="`${completedCount} / ${filteredGoals.length}`"
                icon="CheckCircle"
                color="text-emerald-400"
                class="hover:-translate-y-1 transition-all duration-300"
            />
        </div>

        <!-- Over-Allocation Warnings -->
        <div v-if="overAllocatedAccounts.length > 0" class="mb-6 space-y-2">
            <div v-for="warning in overAllocatedAccounts" :key="warning.name" 
                class="bg-rose-500/10 border border-rose-500/20 text-rose-300 p-4 rounded-xl flex items-start gap-3 shadow-md">
                <AppIcon name="AlertTriangle" class="shrink-0 mt-0.5 text-rose-400" size="18" />
                <div>
                    <h4 class="font-bold text-sm">Account Over-Allocated: {{ warning.name }}</h4>
                    <p class="text-xs text-rose-300/80 mt-1">
                        Total savings goals allocated to this account is **{{ formatPeso(warning.allocated) }}**, which exceeds its actual balance of **{{ formatPeso(warning.balance) }}** by **{{ formatPeso(warning.excess) }}**. Please adjust goal distributions or deposit funds.
                    </p>
                </div>
            </div>
        </div>

        <!-- Goals Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div v-for="goal in filteredGoals" :key="goal.id"
                class="group bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative p-5 flex flex-col justify-between"
                :style="{ boxShadow: goal.is_completed ? `inset 0 2px 0 0 #10b981` : `inset 0 2px 0 0 #6366F1` }">
                
                <!-- Watermark Background Icon -->
                <AppIcon name="Compass" size="100"
                    class="absolute -bottom-4 -right-4 text-slate-100 opacity-[0.01] pointer-events-none transform group-hover:scale-110 group-hover:opacity-[0.03] transition-all duration-500" />
                
                <!-- Subtle glow effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"
                     :style="{ background: goal.is_completed ? `radial-gradient(circle at 50% 0%, #10b981, transparent 70%)` : `radial-gradient(circle at 50% 0%, #6366F1, transparent 70%)` }">
                </div>

                <div class="relative z-10 flex flex-col h-full">
                    <!-- Top section -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start gap-3 max-w-[85%]">
                            <!-- Owner Avatar Circle -->
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm shrink-0 border"
                                :style="{ 
                                    backgroundColor: goal.person ? goal.person.color + '20' : '#6366F120', 
                                    color: goal.person ? goal.person.color : '#6366F1', 
                                    borderColor: `${goal.person ? goal.person.color : '#6366F1'}40` 
                                }">
                                <AppIcon name="Compass" size="20" v-if="!goal.person" />
                                <span v-else class="font-bold text-sm">{{ goal.person.name.charAt(0).toUpperCase() }}</span>
                            </div>

                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-100 text-base truncate leading-snug" :title="goal.name">{{ goal.name }}</h3>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <!-- Owner Badge -->
                                    <span v-if="goal.person" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none" 
                                        :style="{ 
                                            backgroundColor: (goal.person.color || '#94A3B8') + '30', 
                                            color: goal.person.color || '#94A3B8', 
                                            filter: 'brightness(1.4)' 
                                        }">
                                        {{ goal.person.name }}
                                    </span>
                                    <span v-else class="text-[9px] font-medium text-indigo-400">Shared Goal</span>
                                    
                                    <span class="text-[10px] text-slate-600 px-0.5" v-if="goal.account">•</span>
                                    
                                    <!-- Linked Account Badge -->
                                    <span v-if="goal.account" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none"
                                        :style="{ backgroundColor: (goal.account.color || '#6366F1') + '20', color: goal.account.color || '#6366F1' }">
                                        {{ goal.account.name }}
                                    </span>

                                    <span v-if="goal.target_date" class="text-[10px] text-slate-400">
                                        • by {{ formatDate(goal.target_date) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0">
                            <button @click="toggleDropdown(goal.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === goal.id" 
                                class="absolute right-0 top-7 w-40 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-20"
                                @click.stop>
                                <button @click="openContributionModal(goal); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="TrendingUp" size="12" /> Add Contribution
                                </button>
                                <button @click="openEditModal(goal); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Edit2" size="12" /> Edit
                                </button>
                                <div class="border-t border-border my-1"></div>
                                <button @click="handleDelete(goal); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Trash2" size="12" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Stats -->
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-medium tracking-wider">Saved</p>
                            <p class="text-lg font-bold text-slate-100">{{ formatPeso(goal.current_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-medium tracking-wider">Target</p>
                            <p class="text-lg font-bold text-slate-300">{{ formatPeso(goal.target_amount) }}</p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-auto">
                        <ProgressBar 
                            :percent="goal.percent" 
                            :color-class="goal.is_completed ? 'bg-emerald-500' : 'bg-indigo-500'" 
                            class="mb-3" 
                        />
                        
                        <div class="flex justify-between items-center text-[10px] text-slate-400 font-medium">
                            <span v-if="getDaysRemainingText(goal)" 
                                :class="goal.days_remaining !== null && goal.days_remaining < 0 ? 'text-rose-400 font-bold' : 'text-slate-400'">
                                {{ getDaysRemainingText(goal) }}
                            </span>
                            <span v-else>No target date</span>
                            
                            <span v-if="!goal.is_completed" class="text-indigo-400 font-bold">
                                {{ formatPeso(goal.remaining_amount) }} left
                            </span>
                            <span v-else class="text-emerald-400 font-bold flex items-center gap-1">
                                <AppIcon name="Check" size="10" /> Goal Achieved!
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="!filteredGoals.length" class="col-span-full py-12 text-center border-2 border-dashed border-border rounded-xl">
                <AppIcon name="Compass" size="48" class="mx-auto text-slate-500 mb-4" />
                <h3 class="text-lg font-medium text-slate-300">No savings goals found</h3>
                <p class="text-slate-500 mt-1">There are no savings goals matching the active view criteria.</p>
            </div>
        </div>

        <!-- Add/Edit Savings Goal Modal -->
        <AppModal :show="isFormModalOpen" :title="editingGoal ? 'Edit Savings Goal' : 'Add Savings Goal'" @close="isFormModalOpen = false">
            <form @submit.prevent="submitGoalForm" class="space-y-4">
                <AppInput
                    v-model="goalForm.name"
                    label="Goal Name"
                    placeholder="e.g. Emergency Fund"
                    required
                    :error="goalForm.errors.name"
                />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AppInput
                        v-model="goalForm.target_amount"
                        type="number"
                        step="0.01"
                        label="Target Amount"
                        placeholder="0.00"
                        required
                        :error="goalForm.errors.target_amount"
                    />
                    <AppInput
                        v-model="goalForm.current_amount"
                        type="number"
                        step="0.01"
                        label="Current Saved Amount"
                        placeholder="0.00"
                        :disabled="!!editingGoal"
                        :error="goalForm.errors.current_amount"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AppSelect
                        v-model="goalForm.account_id"
                        :options="accountOptions"
                        label="Link to Account"
                        :error="goalForm.errors.account_id"
                    />
                    <AppSelect
                        v-model="goalForm.person_id"
                        :options="personFormOptions"
                        label="Owner"
                        :error="goalForm.errors.person_id"
                    />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AppInput
                        v-model="goalForm.target_date"
                        type="date"
                        label="Target Completion Date"
                        :error="goalForm.errors.target_date"
                    />
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-border">
                    <AppButton variant="secondary" type="button" @click="isFormModalOpen = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="goalForm.processing">
                        {{ editingGoal ? 'Save Changes' : 'Create Goal' }}
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Quick Contribution Modal -->
        <AppModal :show="isContributionModalOpen" title="Update Savings Allocation" @close="isContributionModalOpen = false">
            <form @submit.prevent="submitContributionForm" class="space-y-4">
                <p class="text-xs text-slate-400">
                    Allocating funds towards: <strong class="text-slate-100">{{ contributionGoal?.name }}</strong>
                </p>

                <div class="flex items-center gap-4 bg-sidebar/40 p-3 rounded-lg border border-border/50">
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="radio" :value="false" v-model="contributionForm.is_withdrawal" class="accent-indigo-500" />
                        <span>Deposit / Contribution</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer">
                        <input type="radio" :value="true" v-model="contributionForm.is_withdrawal" class="accent-indigo-500" />
                        <span>Withdrawal / Reallocation</span>
                    </label>
                </div>

                <AppInput
                    v-model="contributionForm.amount"
                    type="number"
                    step="0.01"
                    label="Amount"
                    placeholder="0.00"
                    required
                />

                <div class="flex justify-end gap-2 pt-4 border-t border-border">
                    <AppButton variant="secondary" type="button" @click="isContributionModalOpen = false">Cancel</AppButton>
                    <AppButton type="submit" :loading="contributionForm.processing">
                        Submit
                    </AppButton>
                </div>
            </form>
        </AppModal>

        <!-- Delete Confirmation Modal -->
        <AppModal :show="showDeleteModal" title="Delete Savings Goal" @close="showDeleteModal = false">
            <p class="text-slate-400">
                Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? This action cannot be undone.
            </p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
