<script setup>
import { computed, ref, onMounted } from 'vue';
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
    if (!accs.length) return [];

    const groups = new Map();
    for (let i = 0; i < accs.length; i++) {
        const acc = accs[i];
        const personName = acc.person?.name || 'Shared / Unassigned';

        if (!groups.has(personName)) {
            groups.set(personName, {
                name: personName,
                color: acc.person?.color || '#94A3B8',
                accounts: [],
                total_balance: 0
            });
        }

        const group = groups.get(personName);
        group.accounts.push(acc);
        group.total_balance += Number(acc.current_balance) || 0;
    }

    return Array.from(groups.values()).sort((a, b) => a.name.localeCompare(b.name));
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
    if (!data.length) return { labels: [], datasets: [] };

    const labels = new Array(data.length);
    const incomeData = new Array(data.length);
    const expenseData = new Array(data.length);
    const netData = new Array(data.length);

    for (let i = 0; i < data.length; i++) {
        const d = data[i];
        labels[i] = d.label;
        incomeData[i] = d.income;
        expenseData[i] = d.expense;
        netData[i] = d.net;
    }

    return {
        labels,
        datasets: [
            {
                type: 'line',
                label: 'Net Savings',
                data: netData,
                borderColor: '#3B82F6',
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                order: 1
            },
            { label: 'Income', data: incomeData, backgroundColor: '#10B981', borderRadius: 6, order: 2 },
            { label: 'Expense', data: expenseData, backgroundColor: '#F43F5E', borderRadius: 6, order: 3 },
        ],
    };
});

const doughnutData = computed(() => {
    const data = props.chartData?.categoryExpense || [];
    if (!data.length) return { labels: [], datasets: [] };

    const labels = new Array(data.length);
    const amounts = new Array(data.length);
    const colors = new Array(data.length);

    for (let i = 0; i < data.length; i++) {
        labels[i] = data[i].category_name;
        amounts[i] = data[i].amount;
        colors[i] = data[i].category_color;
    }

    return {
        labels,
        datasets: [{
            data: amounts,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#161B26',
        }],
    };
});

const selectedTrendInterval = ref('daily');

const lineChartData = computed(() => {
    const trendType = selectedTrendInterval.value;
    const trendData = props.chartData?.spendingTrend || {};
    const data = trendData[trendType] || [];

    if (!data.length) return { labels: [], datasets: [] };

    const labels = new Array(data.length);
    const currentData = new Array(data.length);
    const previousData = new Array(data.length);

    for (let i = 0; i < data.length; i++) {
        const d = data[i];
        labels[i] = trendType === 'daily' && d.day !== undefined ? `Day ${d.day}` : d.label;
        currentData[i] = d.current_amount;
        previousData[i] = d.previous_amount;
    }

    return {
        labels,
        datasets: [
            {
                label: 'Current Period',
                data: currentData,
                borderColor: '#6366F1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#6366F1',
                order: 1,
            },
            {
                label: 'Previous Period',
                data: previousData,
                borderColor: '#475569',
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                fill: false,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 4,
                order: 2,
            }
        ],
    };
});

const chartsVisible = ref({
    bar: false,
    doughnut: false,
    line: false,
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.dataset.chartId;
                if (id) chartsVisible.value[id] = true;
                observer.unobserve(entry.target);
            }
        });
    }, { rootMargin: '100px' });

    setTimeout(() => {
        ['bar', 'doughnut', 'line'].forEach(id => {
            const el = document.querySelector(`[data-chart-id="${id}"]`);
            if (el) observer.observe(el);
        });
    }, 0);
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
            <StatCard label="Total Balance" :value="formatPeso(totalBalance)" accentColor="#6366F1" icon="Wallet" />
            <StatCard label="Income This Month" :value="formatPeso(monthlyIncome)" accentColor="#10B981" icon="TrendingUp" />
            <StatCard label="Expenses This Month" :value="formatPeso(monthlyExpense)" accentColor="#F43F5E" icon="TrendingDown" />
            <StatCard label="Net Savings" :value="formatPeso(netSavings)" :accentColor="netSavings >= 0 ? '#10B981' : '#F43F5E'" icon="PiggyBank" />
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <AppCard data-chart-id="bar">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Income vs Expenses (Last 6 Months)</h3>
                <BarChart v-if="chartsVisible.bar" :chartData="barChartData" :height="280" />
                <div v-else class="h-[280px] bg-[#0F111A] rounded-lg animate-pulse" />
            </AppCard>
            <AppCard data-chart-id="doughnut">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Expenses by Category (This Month)</h3>
                <DoughnutChart v-if="chartsVisible.doughnut" :chartData="doughnutData" :height="280" :centerText="formatPeso(monthlyExpense)" />
                <div v-else class="h-[280px] bg-[#0F111A] rounded-lg animate-pulse" />
            </AppCard>
        </div>

        <!-- Accounts -->
        <div class="mb-6">
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-5">Account Balances</h3>
                <div v-for="group in groupedAccounts" :key="group.name" class="mb-6 last:mb-0">
                    <!-- Premium Group Header -->
                    <div class="flex items-center justify-between mb-4 bg-[#0F111A]/50 rounded-xl p-3 border border-[#232936]/50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold shadow-sm"
                                 :style="{ backgroundColor: group.color + '20', color: group.color, border: `1px solid ${group.color}40` }">
                                {{ group.name.substring(0, 1).toUpperCase() }}
                            </div>
                            <h4 class="text-sm font-semibold uppercase tracking-wider" :style="{ color: group.color }">
                                {{ group.name }}
                            </h4>
                        </div>
                        <div class="flex flex-col items-end px-2">
                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-0.5">Total Wealth</span>
                            <span class="text-sm font-bold text-slate-100">{{ formatPeso(group.total_balance) }}</span>
                        </div>
                    </div>

                    <!-- Glassmorphic Cards Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="acc in group.accounts" :key="acc.id"
                             class="account-card group relative flex items-center justify-between p-4 rounded-xl bg-[#0F111A] border border-[#232936] hover:border-transparent transition-all duration-300 hover:-translate-y-1 overflow-hidden">

                            <!-- Subtle Glow Background on Hover -->
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-[0.03] transition-opacity duration-300 pointer-events-none"
                                 :style="{ backgroundImage: `radial-gradient(circle at right top, ${acc.color || '#fff'}, transparent 70%)` }">
                            </div>

                            <!-- Subtle Glow Border on Hover -->
                            <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none border border-solid"
                                 :style="{ borderColor: (acc.color || '#94A3B8') + '80' }">
                            </div>

                            <div class="flex items-center gap-3 relative z-10">
                                <div class="w-3 h-3 rounded-full shrink-0" :style="{ backgroundColor: acc.color || '#94A3B8' }" />
                                <div>
                                    <p class="text-sm font-semibold text-slate-100 group-hover:text-white transition-colors">{{ acc.name }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ acc.account_type?.name }}</p>
                                </div>
                            </div>

                            <!-- Accentuated Balance -->
                            <div class="relative z-10 text-right shrink-0 pl-3">
                                <span :class="['text-[15px] font-bold tracking-tight', acc.current_balance >= 0 ? 'text-slate-100' : 'text-[#F43F5E]']">
                                    <span class="text-slate-500 font-medium mr-0.5 text-sm">₱</span>{{ formatPeso(acc.current_balance).replace('₱', '').trim() }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <p v-if="!groupedAccounts.length" class="text-sm text-slate-400 text-center py-4">No accounts yet</p>
            </AppCard>
        </div>

        <!-- Daily Spending Trend -->
        <div class="mb-6">
            <AppCard data-chart-id="line">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-slate-100">
                        {{ selectedTrendInterval === 'weekly' ? 'Weekly Spending Trend' : selectedTrendInterval === 'monthly' ? 'Monthly Spending Trend' : 'Daily Spending Trend' }}
                    </h3>
                    <div class="flex bg-[#0F111A] rounded-lg p-0.5 border border-[#232936]">
                        <button
                            v-for="interval in ['daily', 'weekly', 'monthly']"
                            :key="interval"
                            @click="selectedTrendInterval = interval"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-md transition-all cursor-pointer capitalize border border-transparent',
                                selectedTrendInterval === interval
                                    ? 'bg-[#1E293B] text-slate-100 shadow-sm border-[#232936]/40'
                                    : 'text-slate-400 hover:text-slate-200'
                            ]"
                        >
                            {{ interval }}
                        </button>
                    </div>
                </div>
                <LineChart v-if="chartsVisible.line" :chartData="lineChartData" :height="240" />
                <div v-else class="h-[240px] bg-[#0F111A] rounded-lg animate-pulse" />
            </AppCard>
        </div>

        <!-- Bottom Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <!-- Recent Transactions -->
            <AppCard class="lg:col-span-2">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Recent Transactions</h3>
                <div class="divide-y divide-[#232936]/40">
                    <div v-for="txn in recentTxns" :key="txn.id" class="flex items-center justify-between py-2.5 hover:bg-[#0F111A]/30 transition-colors px-1 first:pt-0 last:pb-0">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <!-- Category Color Dot -->
                            <div class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: txn.category?.color || '#6366F1' }" />

                            <div class="min-w-0">
                                <!-- First Line: Category Badge + Description -->
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="text-[9px] font-extrabold px-1 py-0.5 rounded uppercase tracking-wider shrink-0"
                                        :style="{ backgroundColor: (txn.category?.color || '#6366F1') + '15', color: txn.category?.color || '#6366F1' }">
                                        {{ txn.category?.name || 'Transfer' }}
                                    </span>
                                    <span class="text-sm font-medium text-slate-200 truncate" :title="txn.description">{{ txn.description }}</span>
                                </div>

                                <!-- Second Line: Account & Owner · Date · "Notes" -->
                                <p class="text-xs text-slate-400 mt-1 truncate">
                                    <span>{{ txn.account?.name }}</span>
                                    <span v-if="txn.account?.person" :style="{ color: txn.account.person.color }"> ({{ txn.account.person.name }})</span>
                                    <span class="mx-1.5 text-slate-500">·</span>
                                    <span>{{ formatShortDate(txn.transaction_date) }}</span>
                                    <span v-if="txn.notes" class="text-slate-500">
                                        <span class="mx-1.5">·</span>
                                        <span class="italic text-[11px] text-slate-400">"{{ txn.notes }}"</span>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Amount -->
                        <span :class="['text-sm font-semibold shrink-0 pl-3', txn.type === 'income' ? 'text-[#10B981]' : txn.type === 'transfer' ? 'text-[#6366F1]' : 'text-[#F43F5E]']">
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
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span v-if="rec.account" class="flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full shrink-0" :style="{ backgroundColor: rec.account.color || '#94A3B8' }" />
                                        <span class="text-xs font-semibold text-slate-400">
                                            {{ rec.account.name }}
                                            <span v-if="rec.account.person" :style="{ color: rec.account.person.color }" class="font-bold opacity-90"> ({{ rec.account.person.name }})</span>
                                        </span>
                                    </span>
                                    <span class="text-slate-600 text-xs" v-if="rec.account">·</span>
                                    <p class="text-xs text-slate-400">{{ formatRelative(rec.next_due_date) }}</p>
                                </div>
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

<style scoped>
.account-card {
    contain: layout style paint;
}
</style>
