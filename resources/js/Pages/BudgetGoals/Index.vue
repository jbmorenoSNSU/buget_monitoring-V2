<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import { useForm } from '@inertiajs/vue3';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    goals: { type: Object, default: () => ({ data: [] }) },
    month: { type: Number, default: new Date().getMonth() + 1 },
    year: { type: Number, default: new Date().getFullYear() },
    categories: { type: Array, default: () => [] },
    persons: { type: Array, default: () => [] },
});

const { formatPeso } = useCurrency();
const { formatMonthYear } = useDate();

const items = computed(() => props.goals?.data || []);
const selectedMonth = ref(props.month);
const selectedYear = ref(props.year);
const deleteTarget = ref(null);
const showDeleteModal = ref(false);

const monthOptions = Array.from({ length: 12 }, (_, i) => ({ value: i + 1, label: new Date(2000, i).toLocaleString('en', { month: 'long' }) }));
const yearOptions = Array.from({ length: 6 }, (_, i) => ({ value: 2023 + i, label: String(2023 + i) }));

const catOptions = computed(() => props.categories.map(c => ({ value: c.id, label: c.name })));

// Form state
const showFormModal = ref(false);
const isEdit = ref(false);
const form = useForm({
    id: null,
    category_id: '',
    person_id: '',
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    limit_amount: '',
    is_rollover_enabled: true,
});

const personOptions = computed(() => {
    const opts = props.persons.map(p => ({ value: p.id, label: p.name }));
    return [{ value: '', label: 'Household (All)' }, ...opts];
});

const openAddModal = () => {
    isEdit.value = false;
    form.clearErrors();
    form.id = null;
    form.category_id = '';
    form.person_id = '';
    form.month = selectedMonth.value;
    form.year = selectedYear.value;
    form.limit_amount = '';
    form.is_rollover_enabled = true;
    showFormModal.value = true;
};

const openEditModal = (goal) => {
    isEdit.value = true;
    form.clearErrors();
    form.id = goal.id;
    form.category_id = goal.category_id || (goal.category?.id || '');
    form.person_id = goal.person_id || '';
    form.month = goal.month;
    form.year = goal.year;
    form.limit_amount = goal.limit_amount;
    form.is_rollover_enabled = goal.is_rollover_enabled !== false;
    showFormModal.value = true;
};

const submitForm = () => {
    if (isEdit.value) {
        form.put(`/budget-goals/${form.id}`, { onSuccess: () => { showFormModal.value = false; filter(); form.reset(); } });
    } else {
        form.post('/budget-goals', { onSuccess: () => { showFormModal.value = false; filter(); form.reset(); } });
    }
};

const filter = () => {
    router.get('/budget-goals', { month: selectedMonth.value, year: selectedYear.value }, { preserveState: true });
};

const confirmDelete = (g) => { deleteTarget.value = g; showDeleteModal.value = true; };
const doDelete = () => { router.delete(`/budget-goals/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } }); };

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
    <AppLayout title="Budget Goals">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex items-center gap-3">
                <AppSelect v-model="selectedMonth" :options="monthOptions" @change="filter" />
                <AppSelect v-model="selectedYear" :options="yearOptions" @change="filter" />
            </div>
            <AppButton @click="openAddModal">+ Add Goal</AppButton>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div v-for="goal in items" :key="goal.id"
                class="group bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300 relative p-5 flex flex-col justify-between"
                :style="{ boxShadow: goal.category?.color ? `inset 0 2px 0 0 ${goal.category.color}` : 'inset 0 2px 0 0 #6366F1' }">
                
                <!-- Background Watermark Icon -->
                <AppIcon :name="goal.category?.icon || 'Target'" size="100" 
                    class="absolute -bottom-4 -right-4 text-slate-100 opacity-[0.02] pointer-events-none transform group-hover:scale-110 group-hover:opacity-[0.04] transition-all duration-500" />
                
                <!-- Subtle glow effect on hover -->
                <div class="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity duration-300 pointer-events-none"
                     :style="{ background: `radial-gradient(circle at 50% 0%, ${goal.category?.color || '#6366F1'}, transparent 70%)` }">
                </div>

                <div class="relative z-10 flex flex-col h-full">
                    <!-- Top Section: Icon, Name & Dropdown -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 max-w-[85%]">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center shadow-sm shrink-0"
                                :style="{ backgroundColor: (goal.category?.color || '#6366F1') + '20', color: goal.category?.color || '#6366F1', border: `1px solid ${goal.category?.color || '#6366F1'}40` }">
                                <AppIcon :name="goal.category?.icon || 'Target'" size="20" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-100 text-base truncate leading-snug">{{ goal.category?.name || 'Unknown Category' }}</h3>
                                <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                                    <span class="text-[10px] text-slate-400 leading-none">{{ formatMonthYear(goal.month, goal.year) }}</span>
                                    <span class="text-[10px] text-slate-600 px-0.5">•</span>
                                    <span v-if="goal.person" class="text-[9px] font-bold px-1.5 py-0.5 rounded-md leading-none" 
                                        :style="{ backgroundColor: (goal.person.color || '#94A3B8') + '30', color: goal.person.color || '#94A3B8', filter: 'brightness(1.4)' }">
                                        {{ goal.person.name }}
                                    </span>
                                    <span v-else class="text-[10px] font-medium text-indigo-400">
                                        Household
                                    </span>
                                    <span v-if="goal.is_rollover_enabled !== false" class="text-[10px] text-emerald-400 flex items-center" title="Rollover Enabled">
                                        <AppIcon name="RefreshCw" size="10" class="mr-0.5" />
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0 -mr-1">
                            <button @click="toggleDropdown(goal.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === goal.id" 
                                class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-20"
                                @click.stop>
                                <button @click="openEditModal(goal); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Edit2" size="12" /> Edit
                                </button>
                                <div class="border-t border-border my-1"></div>
                                <button @click="confirmDelete(goal); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Trash2" size="12" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="bg-sidebar/50 rounded-lg p-3 space-y-2 mb-4 border border-border/30 backdrop-blur-sm mt-auto">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Limit</span>
                            <span class="font-bold text-slate-100">{{ formatPeso(goal.limit_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Spent</span>
                            <span class="font-semibold" :class="goal.percent >= 90 ? 'text-rose-400' : 'text-slate-300'">{{ formatPeso(goal.spent) }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-border/40">
                            <span class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Remaining</span>
                            <span :class="['font-black text-sm tracking-tight', goal.remaining >= 0 ? 'text-emerald-400' : 'text-rose-400']">{{ formatPeso(goal.remaining) }}</span>
                        </div>
                    </div>
                    
                    <ProgressBar :percent="goal.percent" class="scale-y-75 origin-bottom" />
                </div>
            </div>
        </div>

        <p v-if="!items.length" class="text-center text-slate-400 py-12">No budget goals set for {{ formatMonthYear(selectedMonth, selectedYear) }}.</p>

        <AppModal :show="showDeleteModal" title="Delete Budget Goal" @close="showDeleteModal = false">
            <p class="text-slate-400">Delete budget goal for <strong>{{ deleteTarget?.category?.name }}</strong>?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>

        <AppModal :show="showFormModal" :title="isEdit ? 'Edit Budget Goal' : 'Add Budget Goal'" @close="showFormModal = false">
            <form @submit.prevent="submitForm" class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <AppSelect v-model="form.category_id" label="Category" :options="catOptions" :error="form.errors.category_id" required />
                    <AppSelect v-model="form.person_id" label="Person (Optional)" :options="personOptions" :error="form.errors.person_id" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <AppSelect v-model="form.month" label="Month" :options="monthOptions" :error="form.errors.month" required />
                    <AppSelect v-model="form.year" label="Year" :options="yearOptions" :error="form.errors.year" required />
                </div>
                <AppInput v-model="form.limit_amount" label="Limit Amount (₱)" type="number" step="0.01" :error="form.errors.limit_amount" required />
                
                <div class="bg-slate-800/50 p-4 rounded-lg border border-border">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" v-model="form.is_rollover_enabled" class="w-4 h-4 rounded bg-page-bg border-border text-primary focus:ring-primary focus:ring-offset-slate-900" />
                        <span class="text-sm font-medium text-slate-300">Rollover leftover funds to next month</span>
                    </label>
                    <p class="text-[11px] text-slate-400 mt-1 ml-6">Any unspent amount at the end of the month will automatically increase next month's limit for this category.</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <AppButton type="submit" :loading="form.processing">{{ isEdit ? 'Update' : 'Create' }}</AppButton>
                    <AppButton type="button" variant="secondary" @click="showFormModal = false">Cancel</AppButton>
                </div>
            </form>
        </AppModal>
    </AppLayout>
</template>
