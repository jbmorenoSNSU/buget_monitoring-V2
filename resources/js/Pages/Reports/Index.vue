<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const reports = [
    { name: 'Income vs Expense', description: 'Compare income and expenses over time', href: '/reports/income-expense', icon: 'BarChart' },
    { name: 'Expense by Category', description: 'See spending breakdown by category', href: '/reports/category-expense', icon: 'PieChart' },
    { name: 'Account Statement', description: 'View transaction history for an account', href: '/reports/account-statement', icon: 'ClipboardList' },
    { name: 'Budget Goals vs Actual', description: 'Track budget performance', href: '/reports/budget-goal', icon: 'Target' },
    { name: 'Financial Calendar', description: 'Visualize daily spending and income patterns', href: '/reports/calendar', icon: 'Calendar' },
];
</script>

<template>
    <AppLayout title="Reports">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <Link v-for="r in reports" :key="r.href" :href="r.href" class="group">
                <AppCard class="hover:shadow-md hover:border-[#6366F1]/50 transition-all duration-200">
                    <div class="flex items-start gap-4">
                        <AppIcon :name="r.icon" size="32" class="text-slate-400" />
                        <div>
                            <h3 class="font-semibold text-[#F8FAFC] group-hover:text-[#6366F1] transition-colors">{{ r.name }}</h3>
                            <p class="text-sm text-slate-400 mt-1">{{ r.description }}</p>
                        </div>
                    </div>
                </AppCard>
            </Link>
        </div>
    </AppLayout>
</template>
