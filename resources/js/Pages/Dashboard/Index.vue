<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency.js';
import { useDate } from '@/composables/useDate.js';

const props = defineProps({
    totalBalance: { type: Number, default: 0 },
    monthlyIncome: { type: Number, default: 0 },
    monthlyExpense: { type: Number, default: 0 },
    accounts: { type: Object, default: () => ({ data: [] }) },
    recentTransactions: { type: Object, default: () => ({ data: [] }) },
    budgetGoals: { type: Object, default: () => ({ data: [] }) },
    upcomingRecurring: { type: Array, default: () => [] },
    chartData: { type: Object, default: () => ({}) },
    persons: { type: Array, default: () => [] },
    selectedPersonId: { type: Number, default: null },
});

const { formatPeso } = useCurrency();
const { formatShortDate, formatRelative } = useDate();

const netSavings = computed(() => props.monthlyIncome - props.monthlyExpense);

const groupedAccounts = computed(() => {
    const accs = props.accounts?.data || props.accounts || [];
    const groups = {};
    accs.forEach(acc => {
        const personName = acc.person?.name || 'Shared / Unassigned';
        if (!groups[personName]) {
            groups[personName] = {
                name: personName,
                color: acc.person?.color || '#94A3B8',
                accounts: []
            };
        }
        groups[personName].accounts.push(acc);
    });
    return Object.values(groups).sort((a, b) => a.name.localeCompare(b.name));
});
const recentTxns = computed(() => props.recentTransactions?.data || props.recentTransactions || []);
const goals = computed(() => props.budgetGoals?.data || props.budgetGoals || []);

// Person filter
const selectedPerson = ref(props.selectedPersonId ? props.selectedPersonId.toString() : '');
const personOptions = computed(() => [
    { value: '', label: 'Everyone' },
    ...props.persons.map(p => ({ value: p.id.toString(), label: p.name })),
]);

const onPersonChange = () => {
    const params = selectedPerson.value ? { person_id: selectedPerson.value } : {};
    router.get('/dashboard', params, { preserveState: false });
};

const barChartData = computed(() => {
    const data = props.chartData?.sixMonths || [];
    return {
        labels: data.map(d => d.label),
        datasets: [
            { label: 'Income', data: data.map(d => d.income), backgroundColor: '#10B981', borderRadius: 6 },
            { label: 'Expense', data: data.map(d => d.expense), backgroundColor: '#F43F5E', borderRadius: 6 },
        ],
    };
});

const doughnutData = computed(() => {
    const data = props.chartData?.categoryExpense || [];
    return {
        labels: data.map(d => d.category_name),
        datasets: [{
            data: data.map(d => d.amount),
            backgroundColor: data.map(d => d.category_color),
            borderWidth: 2,
            borderColor: '#161B26',
        }],
    };
});

const lineChartData = computed(() => {
    const data = props.chartData?.dailySpending || [];
    return {
        labels: data.map(d => `Day ${d.day}`),
        datasets: [{
            label: 'Daily Spending',
            data: data.map(d => d.amount),
            borderColor: '#6366F1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            fill: true,
            tension: 0.4,
            pointRadius: 3,
            pointBackgroundColor: '#6366F1',
        }],
    };
});
</script>

<template>
    <AppLayout title="Dashboard">
        <!-- Person Filter -->
        <div class="flex items-center gap-3 mb-6">
            <span class="text-sm text-slate-400 font-medium">View as:</span>
            <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" @change="onPersonChange" />
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <StatCard label="Total Balance" :value="formatPeso(totalBalance)" accentColor="#6366F1" />
            <StatCard label="Income This Month" :value="formatPeso(monthlyIncome)" accentColor="#10B981" />
            <StatCard label="Expenses This Month" :value="formatPeso(monthlyExpense)" accentColor="#F43F5E" />
            <StatCard label="Net Savings" :value="formatPeso(netSavings)" :accentColor="netSavings >= 0 ? '#10B981' : '#F43F5E'" />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Income vs Expenses (Last 6 Months)</h3>
                <BarChart :chartData="barChartData" :height="280" />
            </AppCard>
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Expenses by Category (This Month)</h3>
                <DoughnutChart :chartData="doughnutData" :height="280" />
            </AppCard>
        </div>

        <!-- Accounts -->
        <div class="mb-6">
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Account Balances</h3>
                <div v-for="group in groupedAccounts" :key="group.name" class="mb-6 last:mb-0">
                    <h4 class="text-xs font-semibold uppercase tracking-wider mb-3 flex items-center gap-2" :style="{ color: group.color }">
                        {{ group.name }}
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="acc in group.accounts" :key="acc.id" class="flex items-center justify-between p-4 rounded-lg bg-[#0F111A]">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: acc.color }" />
                                <div>
                                    <p class="text-sm font-medium text-slate-100">{{ acc.name }}</p>
                                    <p class="text-xs text-slate-400">{{ acc.account_type?.name }}</p>
                                </div>
                            </div>
                            <span :class="['text-sm font-semibold', acc.current_balance >= 0 ? 'text-slate-100' : 'text-[#F43F5E]']">
                                {{ formatPeso(acc.current_balance) }}
                            </span>
                        </div>
                    </div>
                </div>
                <p v-if="!groupedAccounts.length" class="text-sm text-slate-400 text-center py-4">No accounts yet</p>
            </AppCard>
        </div>

        <!-- Daily Spending Trend -->
        <div class="mb-6">
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Daily Spending Trend</h3>
                <LineChart :chartData="lineChartData" :height="240" />
            </AppCard>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Recent Transactions -->
            <AppCard class="lg:col-span-2">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Recent Transactions</h3>
                <div class="space-y-2">
                    <div v-for="txn in recentTxns" :key="txn.id" class="flex items-center justify-between p-3 rounded-lg hover:bg-[#0F111A] transition-colors">
                        <div class="flex items-center gap-3">
                            <AppIcon :name="txn.category?.icon || 'Package'" size="20" class="text-slate-400" />
                            <div>
                                <p class="text-sm font-medium text-slate-100">{{ txn.description }}</p>
                                <p class="text-xs text-slate-400">{{ txn.account?.name }}<span v-if="txn.account?.person" :style="{ color: txn.account.person.color }"> ({{ txn.account.person.name }})</span> · {{ formatShortDate(txn.transaction_date) }}</p>
                            </div>
                        </div>
                        <span :class="['text-sm font-semibold', txn.type === 'income' ? 'text-[#10B981]' : txn.type === 'transfer' ? 'text-[#6366F1]' : 'text-[#F43F5E]']">
                            {{ txn.type === 'income' ? '+' : txn.type === 'transfer' ? '' : '-' }}{{ formatPeso(txn.amount) }}
                        </span>
                    </div>
                    <p v-if="!recentTxns.length" class="text-sm text-slate-400 text-center py-4">No transactions yet</p>
                </div>
            </AppCard>

            <div class="space-y-4">
                <!-- Budget Goals -->
                <AppCard>
                    <h3 class="text-sm font-semibold text-slate-100 mb-4">Budget Goals</h3>
                    <div class="space-y-4">
                        <div v-for="goal in goals" :key="goal.id">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm text-slate-400">{{ goal.category?.name }}</span>
                                <span class="text-xs text-slate-400">{{ formatPeso(goal.spent) }} / {{ formatPeso(goal.limit_amount) }}</span>
                            </div>
                            <ProgressBar :percent="goal.percent" :showPercent="false" />
                        </div>
                        <p v-if="!goals.length" class="text-sm text-slate-400 text-center py-4">No goals set</p>
                    </div>
                </AppCard>

                <!-- Upcoming Recurring -->
                <AppCard>
                    <h3 class="text-sm font-semibold text-slate-100 mb-4">Upcoming Recurring</h3>
                    <div class="space-y-3">
                        <div v-for="rec in upcomingRecurring" :key="rec.id" class="flex justify-between items-center p-2 rounded-lg bg-[#0F111A]">
                            <div>
                                <p class="text-sm font-medium text-slate-100">{{ rec.description }}</p>
                                <p class="text-xs text-slate-400">{{ formatRelative(rec.next_due_date) }}</p>
                            </div>
                            <span :class="['text-sm font-semibold', rec.type === 'income' ? 'text-[#10B981]' : rec.type === 'transfer' ? 'text-[#6366F1]' : 'text-[#F43F5E]']">
                                {{ rec.type === 'income' ? '+' : rec.type === 'transfer' ? '' : '-' }}{{ formatPeso(rec.amount) }}
                            </span>
                        </div>
                        <p v-if="!upcomingRecurring.length" class="text-sm text-slate-400 text-center py-4">Nothing upcoming</p>
                    </div>
                </AppCard>
            </div>
        </div>
    </AppLayout>
</template>
