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
        emit('toggle'); // Expand sidebar first if collapsed
        return;
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

/** Extracted helper so templates stay readable. */
const isOpen = (groupName) => openGroups.value.includes(groupName);

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
            class="flex items-center gap-3 border-b border-border px-4 cursor-pointer h-[65px] shrink-0"
        >
            <div class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg shadow-indigo-500/20">₱</div>
            <!-- Text fades out via opacity — width is already hidden by overflow-hidden on the aside -->
            <span
                class="text-white font-semibold text-sm whitespace-nowrap transition-opacity duration-200"
                :class="collapsed ? 'opacity-0' : 'opacity-100'"
            >Budget Monitor</span>
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
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition-all duration-200 outline-none',
                        isActive(item)
                            ? 'bg-card-bg text-white font-medium'
                            : 'text-slate-400 hover:text-white hover:bg-card-bg/50',
                    ]"
                >
                    <AppIcon :name="item.icon" size="20" class="shrink-0" />
                    <span
                        class="whitespace-nowrap transition-opacity duration-200"
                        :class="collapsed ? 'opacity-0' : 'opacity-100'"
                    >{{ item.name }}</span>
                </Link>

                <!-- Dropdown Group -->
                <div v-else class="space-y-1">
                    <button
                        @click="toggleGroup(item.name)"
                        :class="[
                            'w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm transition-all duration-200 cursor-pointer outline-none',
                            isActive(item) && !isOpen(item.name)
                                ? 'text-white font-medium'
                                : 'text-slate-400 hover:text-white hover:bg-card-bg/50',
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <AppIcon :name="item.icon" size="20" class="shrink-0" :class="isActive(item) ? 'text-primary' : ''" />
                            <span
                                class="whitespace-nowrap transition-opacity duration-200"
                                :class="[collapsed ? 'opacity-0' : 'opacity-100', isActive(item) ? 'text-white' : '']"
                            >{{ item.name }}</span>
                        </div>
                        <!-- Single chevron that rotates — no icon swap, no flicker -->
                        <AppIcon
                            v-show="!collapsed"
                            name="ChevronRight"
                            size="16"
                            class="shrink-0 text-slate-500 transition-transform duration-200"
                            :class="isOpen(item.name) ? 'rotate-90' : 'rotate-0'"
                        />
                    </button>

                    <!-- Children List: grid-rows-[0fr/1fr] trick gives smooth natural height animation -->
                    <div
                        class="grid transition-all duration-300 ease-in-out"
                        :class="isOpen(item.name) && !collapsed ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'"
                    >
                        <div class="overflow-hidden">
                            <div class="pl-10 pr-2 py-1 space-y-1">
                                <Link
                                    v-for="child in item.children"
                                    :key="child.route"
                                    :href="child.href"
                                    @click="emit('close')"
                                    :class="[
                                        'block px-3 py-2 rounded-md text-sm transition-colors relative outline-none',
                                        isActive(child)
                                            ? 'text-white bg-card-bg font-medium before:absolute before:-left-3 before:top-1/2 before:-translate-y-1/2 before:w-1.5 before:h-1.5 before:rounded-full before:bg-primary'
                                            : 'text-slate-400 hover:text-white hover:bg-card-bg/30 before:absolute before:-left-3 before:top-1/2 before:-translate-y-1/2 before:w-1 before:h-1 before:rounded-full before:bg-slate-600',
                                    ]"
                                >
                                    {{ child.name }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </nav>

        <!-- Footer -->
        <div
            class="p-4 border-t border-border mt-auto shrink-0 transition-opacity duration-200"
            :class="collapsed ? 'opacity-0 pointer-events-none' : 'opacity-100'"
        >
            <p class="text-xs text-slate-500 text-center">Personal Finance</p>
        </div>
    </aside>
</template>

<style scoped>
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
