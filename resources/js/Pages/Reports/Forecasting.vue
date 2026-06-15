<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('180-Day Cashflow Forecasting');
import { computed } from 'vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import LineChart from '@/Components/Charts/LineChart.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
    data: {
        starting_balance: number;
        ending_balance: number;
        projected_high: number;
        projected_low: number;
        net_change: number;
        daily_growth_rate: number;
        daily_points: Array<{ date: string; balance: number }>;
        milestones: Array<{
            date: string;
            description: string;
            amount: number;
            type: string;
            projected_balance: number;
        }>;
    };
}>();

const { formatPeso } = useCurrency();

// Date formatting helper
const formatDate = (dateStr: string) => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// Check if balance drops below zero
const negativeBalanceEvent = computed(() => {
    return props.data.daily_points.find(pt => pt.balance < 0);
});

// Configure chart data
const chartData = computed(() => {
    const labels = props.data.daily_points.map(pt => formatDate(pt.date));
    const balances = props.data.daily_points.map(pt => pt.balance);
    const isNetPositive = props.data.net_change >= 0;

    return {
        labels,
        datasets: [
            {
                label: 'Projected Balance',
                data: balances,
                borderColor: isNetPositive ? '#10B981' : '#6366F1',
                backgroundColor: isNetPositive ? 'rgba(16, 185, 129, 0.05)' : 'rgba(99, 102, 241, 0.05)',
                fill: true,
                tension: 0.3,
                pointRadius: 0,
                pointHoverRadius: 6,
                borderWidth: 3,
            },
        ],
    };
});

// Configure chart options
const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            backgroundColor: '#0F172A',
            titleColor: '#94A3B8',
            bodyColor: '#F1F5F9',
            borderColor: '#334155',
            borderWidth: 1,
            padding: 12,
            titleFont: {
                family: 'Outfit, ui-sans-serif, system-ui, sans-serif',
                size: 11,
                weight: '500',
            },
            bodyFont: {
                family: 'Outfit, ui-sans-serif, system-ui, sans-serif',
                size: 13,
                weight: '700',
            },
            callbacks: {
                label: (context: any) => {
                    return `Balance: ₱${context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                },
            },
        },
    },
    scales: {
        x: {
            grid: {
                color: '#1E293B',
                drawBorder: false,
            },
            ticks: {
                color: '#64748B',
                font: {
                    family: 'Outfit, sans-serif',
                    size: 10,
                },
                maxTicksLimit: 8,
            },
        },
        y: {
            grid: {
                color: '#1E293B',
                drawBorder: false,
            },
            ticks: {
                color: '#64748B',
                font: {
                    family: 'Outfit, sans-serif',
                    size: 10,
                },
                callback: (value: any) => {
                    return `₱${value.toLocaleString()}`;
                },
            },
        },
    },
};

// Milestone table columns
const milestoneColumns = [
    { key: 'date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'amount', label: 'Amount', class: 'text-right', cellClass: 'text-right' },
    { key: 'projected_balance', label: 'Proj. Balance', class: 'text-right', cellClass: 'text-right' },
];
</script>

<template>
    <Head title="180-Day Cashflow Forecasting" />
    <div>
        <!-- Overview Banner -->
        <div class="bg-card-bg border border-border p-5 rounded-xl mb-6 relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 opacity-[0.02] text-slate-100 pointer-events-none">
                <AppIcon name="TrendingUp" size="120" />
            </div>
            <h3 class="text-base font-bold text-slate-100 mb-1">About Cashflow Projection</h3>
            <p class="text-xs text-slate-400 leading-relaxed max-w-4xl">
                This predictive model projects your daily cash balance over the next 180 days. It factors in your
                <strong>average daily net growth rate ({{ formatPeso(data.daily_growth_rate) }}/day)</strong> based on non-recurring transactions over the last 90 days,
                and schedules future occurrences of your active <strong>Recurring Transactions</strong>.
            </p>
        </div>

        <!-- Negative Balance Warning -->
        <div v-if="negativeBalanceEvent" class="bg-rose-500/10 border border-rose-500/20 text-rose-300 p-4 rounded-xl flex items-start gap-3 mb-6 shadow-md">
            <AppIcon name="AlertTriangle" class="shrink-0 mt-0.5 text-rose-400" size="18" />
            <div>
                <h4 class="font-bold text-sm">Negative Balance Risk Detected</h4>
                <p class="text-xs text-rose-300/80 mt-1">
                    Your balance is projected to drop below zero on <strong>{{ formatDate(negativeBalanceEvent.date) }}</strong> (Projected: {{ formatPeso(negativeBalanceEvent.balance) }}).
                    Please review upcoming recurring bills, defer large expenses, or deposit additional funds.
                </p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <StatCard
                title="Starting Cash"
                :value="formatPeso(data.starting_balance)"
                icon="CreditCard"
                color="text-slate-300"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Projected Ending"
                :value="formatPeso(data.ending_balance)"
                icon="Calendar"
                :color="data.net_change >= 0 ? 'text-income' : 'text-expense'"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Net Projected Change"
                :value="(data.net_change >= 0 ? '+' : '') + formatPeso(data.net_change)"
                icon="ArrowUpDown"
                :color="data.net_change >= 0 ? 'text-income' : 'text-expense'"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Projected High"
                :value="formatPeso(data.projected_high)"
                icon="TrendingUp"
                color="text-emerald-400"
                class="hover:-translate-y-1 transition-all duration-300"
            />
            <StatCard
                title="Projected Low"
                :value="formatPeso(data.projected_low)"
                icon="TrendingDown"
                :color="data.projected_low >= 0 ? 'text-slate-300' : 'text-rose-400'"
                class="hover:-translate-y-1 transition-all duration-300"
            />
        </div>

        <!-- Projection Chart Card -->
        <div class="bg-card-bg border border-border rounded-xl shadow-sm mb-6 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-border flex justify-between items-center">
                <h3 class="font-bold text-slate-100 text-sm">Cashflow Forecast Trend (180 Days)</h3>
                <span class="text-xs font-semibold px-2 py-1 rounded bg-sidebar border border-border text-slate-400">
                    Daily Interval
                </span>
            </div>
            <div class="p-6">
                <LineChart :chartData="chartData" :options="chartOptions" :height="360" />
            </div>
        </div>

        <!-- Milestones Section -->
        <div class="bg-card-bg border border-border rounded-xl overflow-hidden shadow-sm">
            <div class="p-5 border-b border-border flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-100 text-sm">Upcoming Key Milestones</h3>
                    <p class="text-xs text-slate-500 mt-1">Chronological listing of the next 20 scheduled recurring events and their projected balance impact.</p>
                </div>
            </div>
            <AppTable :columns="milestoneColumns" :rows="data.milestones">
                <template #cell-date="{ value }">
                    <span class="text-xs text-slate-400 font-medium">{{ formatDate(value) }}</span>
                </template>
                <template #cell-description="{ row }">
                    <div class="flex items-center gap-2">
                        <span :class="['w-1.5 h-1.5 rounded-full', row.type === 'income' ? 'bg-emerald-400' : 'bg-rose-400']"></span>
                        <span class="text-slate-200 font-medium text-xs">{{ row.description }}</span>
                    </div>
                </template>
                <template #cell-amount="{ row }">
                    <span :class="['font-semibold text-xs', row.type === 'income' ? 'text-income' : 'text-expense']">
                        {{ row.type === 'income' ? '+' : '-' }}{{ formatPeso(row.amount) }}
                    </span>
                </template>
                <template #cell-projected_balance="{ value }">
                    <span :class="['font-bold text-xs', value >= 0 ? 'text-slate-100' : 'text-rose-400']">
                        {{ formatPeso(value) }}
                    </span>
                </template>
            </AppTable>
            <div v-if="!data.milestones.length" class="py-12 text-center text-slate-500 text-xs">
                No active recurring transactions scheduled in the next 180 days.
            </div>
        </div>
    </div>
</template>
