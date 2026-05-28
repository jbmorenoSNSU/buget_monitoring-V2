<script setup lang="ts">
import { computed } from 'vue';
import StatCard from '@/Components/UI/StatCard.vue';
import { useCurrency } from '@/composables/useCurrency';

interface Stats {
    totalBalance: number;
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
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
</template>
