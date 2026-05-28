<script setup lang="ts">
import { computed } from 'vue';
import AppCard from '@/Components/UI/AppCard.vue';
import { useCurrency } from '@/composables/useCurrency';

interface AccountType {
    id: number;
    name: string;
}

interface Person {
    id: number;
    name: string;
    color: string;
}

interface Account {
    id: number;
    name: string;
    is_active: boolean;
    current_balance: number;
    color?: string;
    person?: Person;
    account_type?: AccountType;
}

interface AccountsProp {
    data?: Account[];
}

/**
 * DashboardAccounts Component
 * Displays individual financial account balances grouped by owner/person.
 */
const props = withDefaults(
    defineProps<{
        accounts: AccountsProp | Account[];
    }>(),
    {
        accounts: () => ({ data: [] }),
    }
);

const { formatPeso } = useCurrency();

interface GroupedAccount {
    name: string;
    color: string;
    accounts: Account[];
    total_balance: number;
}

const groupedAccounts = computed<GroupedAccount[]>(() => {
    const accs = Array.isArray(props.accounts)
        ? props.accounts
        : props.accounts?.data || [];
        
    if (!accs.length) return [];

    const groups = new Map<string, GroupedAccount>();
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

        const group = groups.get(personName)!;
        group.accounts.push(acc);
        group.total_balance += Number(acc.current_balance) || 0;
    }

    return Array.from(groups.values()).sort((a, b) => a.name.localeCompare(b.name));
});
</script>

<template>
    <div class="mb-6">
        <AppCard>
            <h3 class="text-sm font-semibold text-slate-100 mb-5">Account Balances</h3>
            <div v-for="group in groupedAccounts" :key="group.name" class="mb-6 last:mb-0">
                <!-- Group Header -->
                <div class="flex items-center justify-between mb-4 bg-page-bg/50 rounded-xl p-3 border border-border/50">
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

                <!-- Account Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div v-for="acc in group.accounts" :key="acc.id"
                         class="account-card group relative flex items-center justify-between p-4 rounded-xl bg-page-bg border border-border hover:border-transparent transition-all duration-300 hover:-translate-y-1 overflow-hidden">

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
                            <span :class="['text-[15px] font-bold tracking-tight', acc.current_balance >= 0 ? 'text-slate-100' : 'text-expense']">
                                <span class="text-slate-500 font-medium mr-0.5 text-sm">₱</span>{{ formatPeso(acc.current_balance).replace('₱', '').trim() }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <p v-if="!groupedAccounts.length" class="text-sm text-slate-400 text-center py-4">No accounts yet</p>
        </AppCard>
    </div>
</template>

<style scoped>
.account-card {
    contain: layout style paint;
}
</style>
