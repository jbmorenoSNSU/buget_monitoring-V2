<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';
import { useFlash } from '@/composables/useFlash.js';

defineProps({
    title: { type: String, default: 'Dashboard' },
});

useFlash();

const mobileOpen = ref(false);
const sidebarCollapsed = ref(localStorage.getItem('sidebarCollapsed') === 'true');

const toggleSidebar = () => {
    if (window.innerWidth < 1024) {
        mobileOpen.value = !mobileOpen.value;
    } else {
        sidebarCollapsed.value = !sidebarCollapsed.value;
        localStorage.setItem('sidebarCollapsed', sidebarCollapsed.value);
    }
};

const checkWidth = () => {
    if (window.innerWidth >= 1024) mobileOpen.value = false;
};

onMounted(() => {
    checkWidth();
    window.addEventListener('resize', checkWidth);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkWidth);
});
</script>

<template>
    <div class="min-h-screen bg-[#0F111A]">
        <!-- Desktop Sidebar -->
        <div class="hidden lg:block">
            <Sidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
        </div>

        <!-- Mobile overlay sidebar -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="mobileOpen" class="lg:hidden fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false" />
                <Sidebar :collapsed="false" @close="mobileOpen = false" />
            </div>
        </Transition>

        <!-- Main content -->
        <div :class="['transition-all duration-300 ease-in-out', sidebarCollapsed ? 'lg:ml-[60px]' : 'lg:ml-[260px]']">
            <TopBar :title="title" @toggle-sidebar="toggleSidebar" />
            <main class="p-4 sm:p-6 max-w-7xl mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
