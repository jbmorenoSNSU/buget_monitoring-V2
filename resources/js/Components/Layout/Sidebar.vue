<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AppIcon from '@/Components/UI/AppIcon.vue';

const props = defineProps({
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'toggle']);
const page = usePage();
const currentRoute = computed(() => page.url);
const sidebarEl = ref(null);

const navItems = [
    { name: 'Dashboard', route: 'dashboard', icon: 'Home', href: '/dashboard' },
    { name: 'Persons', route: 'persons', icon: 'Users', href: '/persons' },
    { name: 'Accounts', route: 'accounts', icon: 'CreditCard', href: '/accounts' },
    { name: 'Transactions', route: 'transactions', icon: 'ArrowUpDown', href: '/transactions' },
    { name: 'Recurring', route: 'recurring', icon: 'RefreshCw', href: '/recurring' },
    { name: 'Budget Goals', route: 'budget-goals', icon: 'Target', href: '/budget-goals' },
    { name: 'Categories', route: 'categories', icon: 'Folder', href: '/categories' },
    { name: 'Reports', route: 'reports', icon: 'PieChart', href: '/reports' },
];

const isActive = (item) => {
    return currentRoute.value.startsWith(item.href);
};

const animateSidebar = (el, toWidth) => {
    if (!el) return;
    el.style.transition = 'width 350ms cubic-bezier(0.34, 1.56, 0.64, 1)';
    el.style.width = toWidth;
};

watch(() => props.collapsed, (newValue) => {
    const targetWidth = newValue ? '60px' : '260px';
    animateSidebar(sidebarEl.value, targetWidth);
});

const onItemEnter = (el) => {
    return new Promise(resolve => {
        el.style.opacity = '0';
        el.style.transform = 'translateX(-10px)';
        const itemIndex = Array.from(el.parentElement.children).indexOf(el);
        setTimeout(() => {
            el.style.transition = 'opacity 300ms ease-out, transform 300ms cubic-bezier(0.34, 1.56, 0.64, 1)';
            el.offsetHeight;
            el.style.opacity = '1';
            el.style.transform = 'translateX(0)';
            setTimeout(resolve, 300);
        }, itemIndex * 30);
    });
};

const onItemLeave = (el) => {
    return new Promise(resolve => {
        el.style.transition = 'opacity 200ms ease-in, transform 200ms ease-in';
        el.offsetHeight;
        el.style.opacity = '0';
        el.style.transform = 'translateX(-10px)';
        setTimeout(resolve, 200);
    });
};
</script>

<template>
    <aside
        ref="sidebarEl"
        :class="[
            'fixed top-0 left-0 h-full bg-[#090A0F] border-r border-[#232936] z-40 flex flex-col overflow-hidden',
        ]"
        :style="{ width: collapsed ? '60px' : '260px' }"
    >
        <!-- Logo / Toggle -->
        <div
            @click="emit('toggle')"
            :class="[
                'flex items-center border-b border-[#232936] px-4 cursor-pointer h-[65px]',
                collapsed ? 'justify-center' : 'gap-3'
            ]"
            style="transition: background-color 150ms cubic-bezier(0.34, 1.56, 0.64, 1);"
            @mouseenter="$event.currentTarget.style.backgroundColor = 'rgba(22, 27, 38, 0.5)'"
            @mouseleave="$event.currentTarget.style.backgroundColor = ''"
        >
            <div class="w-8 h-8 rounded-lg bg-[#6366F1] flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg shadow-indigo-500/20">₱</div>
            <Transition
                name="fade-smooth"
                mode="out-in"
            >
                <span v-if="!collapsed" key="text" class="text-white font-semibold text-sm whitespace-nowrap">Budget Monitor</span>
            </Transition>
        </div>

        <!-- Nav -->
        <nav class="flex-1 py-4 space-y-1 px-2 overflow-hidden">
            <TransitionGroup
                tag="div"
                name="nav-item"
                @enter="onItemEnter"
                @leave="onItemLeave"
                :css="false"
            >
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="item.href"
                    @click="emit('close')"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm group block',
                        isActive(item)
                            ? 'bg-[#161B26] text-white font-medium border border-[#232936]'
                            : 'text-slate-400 hover:text-white hover:bg-[#161B26]/50',
                        collapsed ? 'justify-center' : '',
                    ]"
                    style="transition: color 200ms ease-out, background-color 200ms ease-out, border-color 200ms ease-out;"
                >
                    <AppIcon :name="item.icon" size="20" class="shrink-0" />
                    <Transition name="fade-smooth" mode="out-in">
                        <span v-if="!collapsed" :key="`${item.route}-label`" class="whitespace-nowrap">{{ item.name }}</span>
                    </Transition>
                </Link>
            </TransitionGroup>
        </nav>

        <!-- Footer -->
        <Transition name="fade-smooth" mode="out-in">
            <div v-if="!collapsed" :key="'footer'" class="p-4 border-t border-[#232936]">
                <p class="text-xs text-slate-500 text-center">Personal Finance</p>
            </div>
        </Transition>
    </aside>
</template>

<style scoped>
.fade-smooth-enter-active, .fade-smooth-leave-active {
    transition: opacity 200ms cubic-bezier(0.34, 1.56, 0.64, 1);
}
.fade-smooth-enter-from, .fade-smooth-leave-to {
    opacity: 0;
}

.nav-item-enter-active, .nav-item-leave-active {
    transition: all 300ms cubic-bezier(0.34, 1.56, 0.64, 1);
}
.nav-item-enter-from, .nav-item-leave-to {
    opacity: 0;
    transform: translateX(-10px);
}
</style>
