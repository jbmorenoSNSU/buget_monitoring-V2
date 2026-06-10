<script setup lang="ts">
import { computed } from 'vue';
import AppCard from '@/Components/UI/AppCard.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import { useCurrency } from '@/composables/useCurrency';
import { useDate } from '@/composables/useDate';

interface Category {
    name: string;
    color: string;
}

interface Person {
    name: string;
    color: string;
}

interface Account {
    name: string;
    color?: string;
    person?: Person;
}

interface Transaction {
    id: number;
    type: 'income' | 'expense' | 'transfer';
    amount: number;
    transaction_date: string;
    description: string;
    notes?: string;
    category?: Category;
    account?: Account;
}

interface BudgetGoal {
    id: number;
    spent: number;
    limit_amount: number;
    percent: number;
    category?: Category;
    person?: Person;
}

interface UpcomingRecurring {
    id: number;
    description: string;
    next_due_date: string;
    amount: number;
    type: 'income' | 'expense' | 'transfer';
    account?: Account;
}

interface ChartsAndGoals {
    budgetGoals?: { data: BudgetGoal[] } | BudgetGoal[];
    upcomingRecurring?: UpcomingRecurring[];
}

/**
 * DashboardActivity Component
 * Manages rendering of recent ledger activity, goal tracking gauges, and scheduled recurring accounts.
 */
const props = withDefaults(
    defineProps<{
        recentTransactions: { data: Transaction[] } | Transaction[];
        chartsAndGoals: ChartsAndGoals;
    }>(),
    {
        recentTransactions: () => ({ data: [] }),
        chartsAndGoals: () => ({ budgetGoals: { data: [] }, upcomingRecurring: [] }),
    }
);

const { formatPeso } = useCurrency();
const { formatShortDate, formatRelative } = useDate();

const recentTxns = computed<Transaction[]>(() => {
    return Array.isArray(props.recentTransactions)
        ? props.recentTransactions
        : props.recentTransactions?.data || [];
});

const goals = computed<BudgetGoal[]>(() => {
    const bg = props.chartsAndGoals?.budgetGoals;
    if (!bg) return [];
    return Array.isArray(bg) ? bg : bg.data || [];
});

const upcomingRecurring = computed<UpcomingRecurring[]>(() => {
    return props.chartsAndGoals?.upcomingRecurring || [];
});
</script>

<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Recent Transactions -->
        <AppCard class="lg:col-span-2">
            <h3 class="text-sm font-semibold text-slate-100 mb-4">Recent Transactions</h3>
            <div class="divide-y divide-border/40">
                <div v-for="txn in recentTxns" :key="txn.id" class="flex items-center justify-between py-2.5 hover:bg-page-bg/30 transition-colors px-1 first:pt-0 last:pb-0">
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

                            <!-- Second Line: Account & Owner · Date · Notes -->
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
                    <span :class="['text-sm font-semibold shrink-0 pl-3', txn.type === 'income' ? 'text-income' : txn.type === 'transfer' ? 'text-transfer' : 'text-expense']">
                        {{ txn.type === 'income' ? '+' : txn.type === 'transfer' ? '' : '-' }}{{ formatPeso(txn.amount) }}
                    </span>
                </div>
                <p v-if="!recentTxns.length" class="text-sm text-slate-400 text-center py-4">No transactions yet</p>
            </div>
        </AppCard>

        <!-- Sidebar Activity Widgets -->
        <div class="space-y-4">
            <!-- Budget Goals -->
            <AppCard>
                <h3 class="text-sm font-semibold text-slate-100 mb-4">Budget Goals</h3>
                <div class="space-y-4">
                    <div v-for="goal in goals" :key="goal.id">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-sm text-slate-400">
                                {{ goal.category?.name }}
                                <span v-if="goal.person" class="text-xs font-medium ml-1" :style="{ color: goal.person.color || '#94A3B8' }">({{ goal.person.name }})</span>
                                <span v-else class="text-xs text-slate-500 opacity-80 ml-1">(Shared)</span>
                            </span>
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
                    <div v-for="rec in upcomingRecurring" :key="rec.id" class="flex justify-between items-center p-2 rounded-lg bg-page-bg">
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
                        <span :class="['text-sm font-semibold', rec.type === 'income' ? 'text-income' : rec.type === 'transfer' ? 'text-transfer' : 'text-expense']">
                            {{ rec.type === 'income' ? '+' : rec.type === 'transfer' ? '' : '-' }}{{ formatPeso(rec.amount) }}
                        </span>
                    </div>
                    <p v-if="!upcomingRecurring.length" class="text-sm text-slate-400 text-center py-4">Nothing upcoming</p>
                </div>
            </AppCard>
        </div>
    </div>
</template>
