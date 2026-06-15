<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'toggle']);
const page = usePage();
const currentRoute = computed(() => page.url);

const navItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'Home', href: '/dashboard' },
    { name: 'Transactions', route: 'transactions', icon: 'ArrowUpDown', href: '/transactions' },
    { name: 'Accounts', route: 'accounts', icon: 'CreditCard', href: '/accounts' },
    { 
        name: 'Planning & Goals', 
        icon: 'Target',
        children: [
            { name: 'Budget Goals', route: 'budget-goals', href: '/budget-goals' },
            { name: 'Savings Goals', route: 'savings-goals', href: '/savings-goals' },
            { name: 'Debts', route: 'debts', href: '/debts' },
            { name: 'Recurring', route: 'recurring', href: '/recurring' },
        ]
    },
    { 
        name: 'Management', 
        icon: 'Folder',
        children: [
            { name: 'Categories', route: 'categories', href: '/categories' },
            { name: 'Persons', route: 'persons', href: '/persons' },
        ]
    },
    { 
        name: 'Analytics', 
        icon: 'PieChart',
        children: [
            { name: 'Reports', route: 'reports', href: '/reports' },
            { name: 'Downloads', route: 'downloads', href: '/downloads' },
        ]
    },
];

const openGroups = ref([]);

const toggleGroup = (groupName) => {
    if (props.collapsed) {
        emit('toggle'); // Expand sidebar if collapsed
    }
    
    if (openGroups.value.includes(groupName)) {
        openGroups.value = openGroups.value.filter(g => g !== groupName);
    } else {
        openGroups.value.push(groupName);
    }
};

const isActive = (item) => {
    if (item.href) {
        return currentRoute.value.startsWith(item.href);
    }
    if (item.children) {
        return item.children.some(child => currentRoute.value.startsWith(child.href));
    }
    return false;
};

onMounted(() => {
    navItems.forEach(item => {
        if (item.children && item.children.some(child => currentRoute.value.startsWith(child.href))) {
            openGroups.value.push(item.name);
        }
    });
});
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
        <nav class="flex-1 py-4 space-y-1 px-2 overflow-y-auto overflow-x-hidden custom-scrollbar">
            <template v-for="item in navItems" :key="item.name">
                
                <!-- Normal Link -->
                <Link
                    v-if="!item.children"
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

                <!-- Dropdown Group -->
                <div v-else class="space-y-1">
                    <button
                        @click="toggleGroup(item.name)"
                        :class="[
                            'w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm group transition-all duration-300 cursor-pointer',
                            isActive(item) && !openGroups.includes(item.name)
                                ? 'text-white font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-card-bg/50',
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <AppIcon :name="item.icon" size="20" class="shrink-0" :class="isActive(item) ? 'text-primary' : ''" />
                            <Transition name="fade">
                                <span v-if="!collapsed" class="whitespace-nowrap" :class="isActive(item) ? 'text-white' : ''">{{ item.name }}</span>
                            </Transition>
                        </div>
                        <Transition name="fade">
                            <AppIcon v-if="!collapsed" :name="openGroups.includes(item.name) ? 'ChevronDown' : 'ChevronRight'" size="16" class="shrink-0 text-slate-500" />
                        </Transition>
                    </button>

                    <!-- Children List -->
                    <Transition
                        enter-active-class="transition-all duration-300 ease-in-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-60"
                        leave-active-class="transition-all duration-300 ease-in-out"
                        leave-from-class="opacity-100 max-h-60"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div v-if="openGroups.includes(item.name) && !collapsed" class="pl-10 pr-2 py-1 space-y-1 overflow-hidden">
                            <Link
                                v-for="child in item.children"
                                :key="child.route"
                                :href="child.href"
                                @click="emit('close')"
                                :class="[
                                    'block px-3 py-2 rounded-md text-sm transition-colors relative',
                                    isActive(child)
                                        ? 'text-white bg-card-bg font-medium before:absolute before:-left-3 before:top-1/2 before:-translate-y-1/2 before:w-1.5 before:h-1.5 before:rounded-full before:bg-primary'
                                        : 'text-slate-400 hover:text-white hover:bg-card-bg/30 before:absolute before:-left-3 before:top-1/2 before:-translate-y-1/2 before:w-1 before:h-1 before:rounded-full before:bg-slate-600',
                                ]"
                            >
                                {{ child.name }}
                            </Link>
                        </div>
                    </Transition>
                </div>
            </template>
        </nav>

        <!-- Footer -->
        <Transition name="fade">
            <div v-if="!collapsed" class="p-4 border-t border-border mt-auto">
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

/* Custom Scrollbar for the nav area */
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: var(--color-border);
    border-radius: 10px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
    background-color: var(--color-slate-600);
}
</style>
