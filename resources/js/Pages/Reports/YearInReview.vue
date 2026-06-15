<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { usePageTitle } from '@/composables/usePageTitle';

const { setPageTitle } = usePageTitle();
setPageTitle('Year in Review');
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps<{
    data: Record<string, any>;
    filters: Record<string, any>;
}>();

const { formatPeso } = useCurrency();
const year = ref(props.filters.year || new Date().getFullYear());
const yearOptions = Array.from({ length: 6 }, (_, i) => ({ value: 2023 + i, label: String(2023 + i) }));

const filter = () => {
    router.get('/reports/year-in-review', { year: year.value }, { preserveState: true });
};
</script>

<template>
    <Head title="Year in Review" />
    <div>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-slate-100">{{ year }} Year in Review</h2>
            <AppSelect v-model="year" :options="yearOptions" @change="filter" class="w-32" />
        </div>

        <!-- High Level Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <AppCard class="bg-gradient-to-br from-indigo-900/40 to-slate-900 border-indigo-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <AppIcon name="TrendingUp" size="64" />
                </div>
                <div class="p-6">
                    <p class="text-sm font-medium text-indigo-300 mb-2 uppercase tracking-wider">Total Income</p>
                    <h3 class="text-3xl font-bold text-white">{{ formatPeso(data.total_income) }}</h3>
                </div>
            </AppCard>

            <AppCard class="bg-gradient-to-br from-rose-900/40 to-slate-900 border-rose-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <AppIcon name="TrendingDown" size="64" />
                </div>
                <div class="p-6">
                    <p class="text-sm font-medium text-rose-300 mb-2 uppercase tracking-wider">Total Expense</p>
                    <h3 class="text-3xl font-bold text-white">{{ formatPeso(data.total_expense) }}</h3>
                </div>
            </AppCard>

            <AppCard class="bg-gradient-to-br from-emerald-900/40 to-slate-900 border-emerald-500/20 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <AppIcon name="PiggyBank" size="64" />
                </div>
                <div class="p-6">
                    <p class="text-sm font-medium text-emerald-300 mb-2 uppercase tracking-wider">Net Savings</p>
                    <h3 class="text-3xl font-bold text-white">{{ formatPeso(data.net_savings) }}</h3>
                </div>
            </AppCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Categories -->
            <AppCard>
                <div class="p-6">
                    <div class="flex items-center gap-2 mb-6">
                        <AppIcon name="Award" class="text-amber-400" size="24" />
                        <h3 class="text-lg font-bold text-slate-100">Top 5 Spending Categories</h3>
                    </div>

                    <div v-if="data.top_categories.length === 0" class="text-slate-400 text-sm py-4">
                        No expenses recorded this year.
                    </div>

                    <div v-else class="space-y-4">
                        <div v-for="(cat, index) in data.top_categories" :key="index" class="relative">
                            <div class="flex justify-between items-end mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-slate-400 font-mono text-xs w-4">{{ Number(index) + 1 }}.</span>
                                    <AppIcon :name="cat.category_icon" size="14" :style="{ color: cat.category_color }" />
                                    <span class="text-sm font-medium text-slate-200">{{ cat.category_name }}</span>
                                </div>
                                <span class="text-sm font-bold text-white">{{ formatPeso(cat.amount) }}</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full" 
                                     :style="{ 
                                         width: `${(cat.amount / data.top_categories[0].amount) * 100}%`,
                                         backgroundColor: cat.category_color 
                                     }">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AppCard>

            <!-- Highlights -->
            <div class="space-y-6">
                <AppCard class="bg-gradient-to-r from-slate-800 to-slate-900 border-l-4 border-l-amber-500">
                    <div class="p-6">
                        <p class="text-xs font-medium text-slate-400 uppercase tracking-widest mb-2">Busiest Month</p>
                        <div class="flex justify-between items-center">
                            <h3 class="text-2xl font-bold text-slate-100">{{ data.busiest_month.name }}</h3>
                            <span class="text-lg font-semibold text-expense">{{ formatPeso(data.busiest_month.amount) }}</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">The month you spent the most money.</p>
                    </div>
                </AppCard>
            </div>
        </div>

    </div>
</template>
