<script setup>
import { computed, ref } from 'vue';
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

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <AppCard v-for="goal in items" :key="goal.id">
                <div class="flex items-center gap-3 mb-4">
                    <AppIcon :name="goal.category?.icon || 'Target'" size="24" class="text-slate-400" />
                    <div>
                        <h3 class="font-semibold text-[#F8FAFC]">{{ goal.category?.name }}</h3>
                        <p class="text-xs text-slate-400">{{ formatMonthYear(goal.month, goal.year) }}</p>
                    </div>
                </div>
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Limit</span>
                        <span class="font-medium text-[#F8FAFC]">{{ formatPeso(goal.limit_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Spent</span>
                        <span class="font-medium" :class="goal.percent >= 90 ? 'text-[#F43F5E]' : 'text-[#F8FAFC]'">{{ formatPeso(goal.spent) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">Remaining</span>
                        <span :class="['font-medium', goal.remaining >= 0 ? 'text-[#10B981]' : 'text-[#F43F5E]']">{{ formatPeso(goal.remaining) }}</span>
                    </div>
                </div>
                <ProgressBar :percent="goal.percent" />
                <div class="flex gap-2 mt-4 pt-3 border-t border-[#232936]">
                    <Link :href="`/budget-goals/${goal.id}/edit`"><AppButton variant="secondary" size="sm">Edit</AppButton></Link>
                    <AppButton variant="danger" size="sm" @click="confirmDelete(goal)">Delete</AppButton>
                </div>
            </AppCard>
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
