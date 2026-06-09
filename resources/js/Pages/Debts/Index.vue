<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppPagination from '@/Components/UI/AppPagination.vue';
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

const handleDelete = (debt: any) => {
    if (confirm(`Are you sure you want to delete ${debt.name}?`)) {
        router.delete(`/debts/${debt.id}`);
    }
};

const calculateProgress = (debt: any) => {
    // If we wanted to track original balance vs current balance, we'd calculate a %.
    // Since we only track current balance, progress is simply "Paid off" or not.
    // However, if it's paid off, balance is 0.
    if (debt.principal_amount <= 0 || debt.status === 'paid_off') return 100;
    return 0; // We don't have starting balance, so we can't show a true progress bar easily without another field.
};

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
            <AppCard v-for="debt in debts.data" :key="debt.id" class="flex flex-col relative overflow-hidden group">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-100 flex items-center gap-2">
                            {{ debt.name }}
                        </h3>
                        <p class="text-xs text-slate-400 mt-1" v-if="debt.person">Owner: {{ debt.person.name }}</p>
                    </div>
                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button @click="openEditModal(debt)" class="p-1.5 text-slate-400 hover:text-white bg-slate-800 rounded hover:bg-slate-700 transition">
                            <AppIcon name="Edit2" size="14" />
                        </button>
                        <button @click="handleDelete(debt)" class="p-1.5 text-slate-400 hover:text-expense bg-slate-800 rounded hover:bg-expense/20 transition">
                            <AppIcon name="Trash2" size="14" />
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-end mb-4">
                    <div>
                        <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Current Balance</p>
                        <p class="text-2xl font-bold text-expense">{{ formatPeso(debt.principal_amount) }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-slate-800 text-slate-300" v-if="debt.status === 'active'">
                            {{ debt.interest_rate }}% APR
                        </span>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-400" v-else>
                            Paid Off
                        </span>
                    </div>
                </div>

                <div class="bg-slate-800/50 rounded-lg p-3 mb-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Monthly Payment:</span>
                        <span class="text-slate-200 font-medium">{{ formatPeso(debt.minimum_payment) }}</span>
                    </div>
                    <div class="flex justify-between text-sm" v-if="debt.due_date_day">
                        <span class="text-slate-400">Due Date:</span>
                        <span class="text-slate-200 font-medium">{{ debt.due_date_day }}th</span>
                    </div>
                </div>

                <div class="mt-auto border-t border-border pt-4">
                    <div v-if="debt.status === 'active'">
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Estimated Payoff</span>
                            <span class="text-emerald-400 font-semibold" v-if="debt.payoff_projection.is_possible">{{ debt.payoff_projection.payoff_date }}</span>
                            <span class="text-expense font-semibold" v-else>Never</span>
                        </div>
                        <div class="flex justify-between text-xs text-slate-500 mt-1" v-if="debt.payoff_projection.is_possible">
                            <span>Time to payoff:</span>
                            <span>{{ debt.payoff_projection.months }} months</span>
                        </div>
                        <div class="text-xs text-expense mt-1" v-if="!debt.payoff_projection.is_possible">
                            Minimum payment is less than accumulated interest!
                        </div>
                    </div>
                    <div v-else class="text-center py-2 text-emerald-500 font-bold flex items-center justify-center gap-2">
                        <AppIcon name="CheckCircle" size="20" /> Debt Free!
                    </div>
                </div>
            </AppCard>

            <div v-if="!debts.data.length" class="col-span-full py-12 text-center border-2 border-dashed border-border rounded-xl">
                <AppIcon name="CheckCircle" size="48" class="mx-auto text-slate-500 mb-4" />
                <h3 class="text-lg font-medium text-slate-300">No debts tracked</h3>
                <p class="text-slate-500 mt-1">You're completely debt free, or haven't added any loans yet.</p>
            </div>
        </div>

        <AppPagination :links="debts.links" :meta="debts" class="mt-6" />

        <DebtFormModal
            :show="isFormModalOpen"
            :debt="editingDebt"
            :persons="filters.persons"
            @close="isFormModalOpen = false"
        />
    </AppLayout>
</template>
