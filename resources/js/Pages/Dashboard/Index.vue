<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Dashboard');
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import DashboardStats from './Components/DashboardStats.vue';
import DashboardAccounts from './Components/DashboardAccounts.vue';
import DashboardCharts from './Components/DashboardCharts.vue';
import DashboardActivity from './Components/DashboardActivity.vue';
import DashboardHealthScore from './Components/DashboardHealthScore.vue';

interface Person {
    id: number;
    name: string;
}

interface Filters {
    selectedPersonId?: number;
    persons: Person[];
}

/**
 * Dashboard Index Component
 * Acts as the primary page entry point and visual orchestrator for the dashboard panels.
 */
const props = defineProps<{
    stats: any;
    filters: Filters;
    accounts: any;
    recentTransactions: any;
    chartsAndGoals: any;
}>();

const selectedPerson = ref(props.filters?.selectedPersonId ? props.filters.selectedPersonId.toString() : '');

const personOptions = computed(() => [
    { value: '', label: 'Everyone' },
    ...(props.filters?.persons || []).map(p => ({ value: p.id.toString(), label: p.name })),
]);

const onPersonChange = () => {
    const params = selectedPerson.value ? { person_id: selectedPerson.value } : {};
    router.get('/dashboard', params, { preserveState: false });
};
</script>

<template>
    <Head title="Dashboard" />
    <div>
        <!-- Person Filter Selector -->
        <div class="flex items-center gap-3 mb-6">
            <span class="text-sm text-slate-400 font-medium">View as:</span>
            <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" @change="onPersonChange" />
        </div>

        <!-- summary numeric cards section -->
        <DashboardStats :stats="stats" />

        <!-- financial health score -->
        <DashboardHealthScore :score="stats?.healthScore || 0" :badges="stats?.badges || []" />

        <!-- primary chart visualization panels -->
        <DashboardCharts :chartsAndGoals="chartsAndGoals" :monthlyExpense="stats?.monthlyExpense || 0" />

        <!-- financial accounts layout section -->
        <DashboardAccounts :accounts="accounts" />

        <!-- bottom activity / goals progress lists -->
        <DashboardActivity :recentTransactions="recentTransactions" :chartsAndGoals="chartsAndGoals" />
    </div>
</template>
