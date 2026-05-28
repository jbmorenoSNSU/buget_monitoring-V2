<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import AppCard from '@/Components/UI/AppCard.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import { useCurrency } from '@/composables/useCurrency';

interface SixMonthData {
    label: string;
    income: number;
    expense: number;
    net: number;
}

interface CategoryExpenseData {
    category_name: string;
    amount: number;
    category_color: string;
}

interface SpendingTrendPoint {
    day?: number;
    label: string;
    current_amount: number;
    previous_amount: number;
}

interface SpendingTrend {
    daily?: SpendingTrendPoint[];
    weekly?: SpendingTrendPoint[];
    monthly?: SpendingTrendPoint[];
    [key: string]: SpendingTrendPoint[] | undefined;
}

interface ChartData {
    sixMonths?: SixMonthData[];
    categoryExpense?: CategoryExpenseData[];
    spendingTrend?: SpendingTrend;
}

interface ChartsAndGoals {
    chartData?: ChartData;
}

/**
 * DashboardCharts Component
 * Renders primary visualization panels (income/expense comparison, category distribution, daily trends)
 * with lazy viewport observer and dynamic theme variable bindings.
 */
const props = withDefaults(
    defineProps<{
        chartsAndGoals: ChartsAndGoals;
        monthlyExpense: number;
    }>(),
    {
        chartsAndGoals: () => ({ chartData: {} }),
        monthlyExpense: 0,
    }
);

const { formatPeso } = useCurrency();
const selectedTrendInterval = ref<'daily' | 'weekly' | 'monthly'>('daily');

interface ChartsVisible {
    bar: boolean;
    doughnut: boolean;
    line: boolean;
    [key: string]: boolean;
}

const chartsVisible = ref<ChartsVisible>({
    bar: false,
    doughnut: false,
    line: false,
});

onMounted(() => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = entry.target as HTMLElement;
                const id = target.dataset.chartId;
                if (id && id in chartsVisible.value) {
                    chartsVisible.value[id] = true;
                }
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

// Dynamic theme resolution from computed document styles
const colors = computed(() => {
    if (typeof window === 'undefined') {
        return {
            primary: '#6366F1',
            income: '#10B981',
            expense: '#F43F5E',
            border: '#232936',
            cardBg: '#161B26'
        };
    }
    const style = getComputedStyle(document.documentElement);
    return {
        primary: style.getPropertyValue('--color-primary').trim() || '#6366F1',
        income: style.getPropertyValue('--color-income').trim() || '#10B981',
        expense: style.getPropertyValue('--color-expense').trim() || '#F43F5E',
        border: style.getPropertyValue('--color-border').trim() || '#232936',
        cardBg: style.getPropertyValue('--color-card-bg').trim() || '#161B26'
    };
});

const barChartData = computed(() => {
    const data = props.chartsAndGoals?.chartData?.sixMonths || [];
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
                type: 'line' as const,
                label: 'Net Savings',
                data: netData,
                borderColor: colors.value.primary,
                backgroundColor: 'transparent',
                borderWidth: 2,
                tension: 0.4,
                pointBackgroundColor: colors.value.primary,
                order: 1
            },
            { label: 'Income', data: incomeData, backgroundColor: colors.value.income, borderRadius: 6, order: 2 },
            { label: 'Expense', data: expenseData, backgroundColor: colors.value.expense, borderRadius: 6, order: 3 },
        ],
    };
});

const doughnutData = computed(() => {
    const data = props.chartsAndGoals?.chartData?.categoryExpense || [];
    if (!data.length) return { labels: [], datasets: [] };

    const labels = new Array(data.length);
    const amounts = new Array(data.length);
    const itemColors = new Array(data.length);

    for (let i = 0; i < data.length; i++) {
        labels[i] = data[i].category_name;
        amounts[i] = data[i].amount;
        itemColors[i] = data[i].category_color;
    }

    return {
        labels,
        datasets: [{
            data: amounts,
            backgroundColor: itemColors,
            borderWidth: 2,
            borderColor: '#0F111A', // aligned with --color-page-bg
        }],
    };
});

const lineChartData = computed(() => {
    const trendType = selectedTrendInterval.value;
    const trendData = props.chartsAndGoals?.chartData?.spendingTrend || {};
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
</script>

<template>
    <div>
        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <AppCard data-chart-id="bar">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Income vs Expenses (Last 6 Months)</h3>
                <BarChart v-if="chartsVisible.bar" :chartData="barChartData" :height="280" />
                <div v-else class="h-[280px] bg-page-bg rounded-lg animate-pulse" />
            </AppCard>
            <AppCard data-chart-id="doughnut">
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Expenses by Category (This Month)</h3>
                <DoughnutChart v-if="chartsVisible.doughnut" :chartData="doughnutData" :height="280" :centerText="formatPeso(monthlyExpense)" />
                <div v-else class="h-[280px] bg-page-bg rounded-lg animate-pulse" />
            </AppCard>
        </div>

        <!-- Daily Spending Trend -->
        <div class="mb-6">
            <AppCard data-chart-id="line">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                    <h3 class="text-sm font-semibold text-slate-100">
                        {{ selectedTrendInterval === 'weekly' ? 'Weekly Spending Trend' : selectedTrendInterval === 'monthly' ? 'Monthly Spending Trend' : 'Daily Spending Trend' }}
                    </h3>
                    <div class="flex bg-page-bg rounded-lg p-0.5 border border-border">
                        <button
                            v-for="interval in ['daily', 'weekly', 'monthly'] as const"
                            :key="interval"
                            @click="selectedTrendInterval = interval"
                            :class="[
                                'px-3 py-1 text-xs font-medium rounded-md transition-all cursor-pointer capitalize border border-transparent',
                                selectedTrendInterval === interval
                                    ? 'bg-card-bg text-slate-100 shadow-sm border-border/40'
                                    : 'text-slate-400 hover:text-slate-200'
                            ]"
                        >
                            {{ interval }}
                        </button>
                    </div>
                </div>
                <LineChart v-if="chartsVisible.line" :chartData="lineChartData" :height="240" />
                <div v-else class="h-[240px] bg-page-bg rounded-lg animate-pulse" />
            </AppCard>
        </div>
    </div>
</template>
