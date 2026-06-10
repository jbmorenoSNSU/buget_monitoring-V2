<script setup lang="ts">
import { computed } from 'vue';
import StatCard from '@/Components/UI/StatCard.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency';

interface Stats {
    totalBalance: number;
    safeToSpend: number;
    safeToSpendDaily?: number;
    healthScore?: number;
    badges?: any[];
    monthlyIncome: number;
    monthlyExpense: number;
}

/**
 * DashboardStats Component
 * Renders the top summary metric cards for Total Balance, Monthly Income, Monthly Expense, and Net Savings.
 */
const props = withDefaults(
    defineProps<{
        stats: Stats;
    }>(),
    {
        stats: () => ({
            totalBalance: 0,
            safeToSpend: 0,
            monthlyIncome: 0,
            monthlyExpense: 0,
        }),
    }
);

const { formatPeso } = useCurrency();

const totalBalance = computed(() => props.stats?.totalBalance || 0);
const monthlyIncome = computed(() => props.stats?.monthlyIncome || 0);
const monthlyExpense = computed(() => props.stats?.monthlyExpense || 0);
const netSavings = computed(() => monthlyIncome.value - monthlyExpense.value);
</script>

<template>
    <div class="mb-6">
        <!-- Safe to Spend Hero -->
        <div class="bg-gradient-to-r from-emerald-500/10 to-teal-500/10 border border-emerald-500/20 rounded-2xl p-6 mb-6 flex flex-col md:flex-row items-center justify-between gap-4 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 opacity-5 pointer-events-none">
                <AppIcon name="ShieldCheck" :size="150" />
            </div>
            <div class="z-10">
                <h2 class="text-sm font-bold text-emerald-400 uppercase tracking-wider mb-1 flex items-center gap-2">
                    <AppIcon name="ShieldCheck" size="16" />
                    Safe to Spend
                </h2>
                <p class="text-xs text-slate-400 max-w-md">Your current balance minus all upcoming recurring bills and remaining budget limits for the month.</p>
            </div>
            <div class="text-right z-10">
                <div class="text-3xl md:text-4xl font-extrabold text-emerald-400 drop-shadow-md">
                    {{ formatPeso(stats.safeToSpend || 0) }}
                </div>
                <div class="text-sm font-medium text-emerald-500 mt-1 bg-emerald-900/30 inline-block px-3 py-1 rounded-full border border-emerald-500/20">
                    Daily Allowance: {{ formatPeso(stats.safeToSpendDaily || 0) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <StatCard
            label="Total Balance"
            :value="formatPeso(totalBalance)"
            accentColor="var(--color-primary)"
            icon="Wallet"
        />
        <StatCard
            label="Income This Month"
            :value="formatPeso(monthlyIncome)"
            accentColor="var(--color-income)"
            icon="TrendingUp"
        />
        <StatCard
            label="Expenses This Month"
            :value="formatPeso(monthlyExpense)"
            accentColor="var(--color-expense)"
            icon="TrendingDown"
        />
        <StatCard
            label="Net Savings"
            :value="formatPeso(netSavings)"
            :accentColor="netSavings >= 0 ? 'var(--color-income)' : 'var(--color-expense)'"
            icon="PiggyBank"
        />
        </div>
    </div>
</template>
