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
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    goals: { type: Object, default: () => ({ data: [] }) },
    month: { type: Number, default: new Date().getMonth() + 1 },
    year: { type: Number, default: new Date().getFullYear() },
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
            <Link href="/budget-goals/create"><AppButton>+ Add Goal</AppButton></Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="goal in items" :key="goal.id"
                class="bg-[#161B26] border border-[#232936] rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow relative p-4">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2 max-w-[80%]">
                        <AppIcon :name="goal.category?.icon || 'Target'" size="20" class="text-slate-400 shrink-0" />
                        <div class="min-w-0">
                            <h3 class="font-semibold text-slate-100 text-sm truncate leading-snug">{{ goal.category?.name }}</h3>
                            <p class="text-[11px] text-slate-400 truncate leading-none mt-0.5">{{ formatMonthYear(goal.month, goal.year) }}</p>
                        </div>
                    </div>
                    
                    <!-- Dropdown Menu trigger -->
                    <div class="relative shrink-0">
                        <button @click="toggleDropdown(goal.id, $event)" 
                            class="p-1 rounded hover:bg-[#232936] text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                            <AppIcon name="MoreVertical" size="16" />
                        </button>
                        
                        <!-- Dropdown List -->
                        <div v-if="activeDropdownId === goal.id" 
                            class="absolute right-0 top-7 w-32 bg-[#1A202C] border border-[#232936] rounded-lg shadow-xl py-1 z-10"
                            @click.stop>
                            <Link :href="`/budget-goals/${goal.id}/edit`" 
                                class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-[#232936] hover:text-slate-100 transition-colors w-full text-left">
                                <AppIcon name="Edit2" size="12" /> Edit
                            </Link>
                            <div class="border-t border-[#232936] my-1"></div>
                            <button @click="confirmDelete(goal); activeDropdownId = null" 
                                class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-[#232936] hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                <AppIcon name="Trash2" size="12" /> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <div class="space-y-1.5 mb-3 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400">Limit</span>
                        <span class="font-medium text-[#F8FAFC]">{{ formatPeso(goal.limit_amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Spent</span>
                        <span class="font-medium" :class="goal.percent >= 90 ? 'text-[#F43F5E]' : 'text-[#F8FAFC]'">{{ formatPeso(goal.spent) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-400">Remaining</span>
                        <span :class="['font-medium', goal.remaining >= 0 ? 'text-[#10B981]' : 'text-[#F43F5E]']">{{ formatPeso(goal.remaining) }}</span>
                    </div>
                </div>
                
                <ProgressBar :percent="goal.percent" class="scale-y-75 origin-bottom mt-1" />
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
    </AppLayout>
</template>
