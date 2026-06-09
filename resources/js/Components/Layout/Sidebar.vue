<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'toggle']);
const page = usePage();
const currentRoute = computed(() => page.url);

const navItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'Home', href: '/dashboard' },
    { name: 'Persons', route: 'persons', icon: 'Users', href: '/persons' },
    { name: 'Accounts', route: 'accounts', icon: 'CreditCard', href: '/accounts' },
    { name: 'Transactions', route: 'transactions', icon: 'ArrowUpDown', href: '/transactions' },
    { name: 'Recurring', route: 'recurring', icon: 'RefreshCw', href: '/recurring' },
    { name: 'Budget Goals', route: 'budget-goals', icon: 'Target', href: '/budget-goals' },
    { name: 'Categories', route: 'categories', icon: 'Folder', href: '/categories' },
    { name: 'Debts', route: 'debts', icon: 'TrendingDown', href: '/debts' },
    { name: 'Reports', route: 'reports', icon: 'PieChart', href: '/reports' },
];

const isActive = (item) => {
    return currentRoute.value.startsWith(item.href);
};
</script>

<template>
    <aside
        class="fixed top-0 left-0 h-full bg-sidebar border-r border-border z-40 flex flex-col overflow-hidden transition-all duration-300 ease-in-out flex-shrink-0"
        :class="collapsed ? 'w-16' : 'w-60'"
    >
        <!-- Logo / Toggle -->
        <div
            @click="emit('toggle')"
            class="flex items-center gap-3 border-b border-border px-4 cursor-pointer h-[65px] transition-all duration-300"
        >
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg shadow-indigo-500/20 transition-all duration-300">₱</div>
            <Transition name="fade">
                <span v-if="!collapsed" class="text-white font-semibold text-sm whitespace-nowrap">Budget Monitor</span>
            </Transition>
        </div>

        <!-- Nav -->
        <nav class="flex-1 py-4 space-y-1 px-2 overflow-hidden">
            <Link
                v-for="item in navItems"
                :key="item.route"
                :href="item.href"
                @click="emit('close')"
                :class="[
                    'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm group transition-all duration-300',
                    isActive(item)
                        ? 'bg-card-bg text-white font-medium border border-border'
                        : 'text-slate-400 hover:text-white hover:bg-card-bg/50',
                ]"
            >
                <AppIcon :name="item.icon" size="20" class="shrink-0" />
                <Transition name="fade">
                    <span v-if="!collapsed" class="whitespace-nowrap">{{ item.name }}</span>
                </Transition>
            </Link>
        </nav>

        <!-- Footer -->
        <Transition name="fade">
            <div v-if="!collapsed" class="p-4 border-t border-border">
                <p class="text-xs text-slate-500 text-center">Personal Finance</p>
            </div>
        </Transition>
    </aside>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity 150ms ease;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
