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
const isTablet = ref(false);

const checkWidth = () => {
    isTablet.value = window.innerWidth >= 640 && window.innerWidth < 1024;
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
        <!-- Desktop/Tablet Sidebar -->
        <div class="hidden sm:block">
            <Sidebar :collapsed="isTablet" />
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
            <div v-if="mobileOpen" class="sm:hidden fixed inset-0 z-50">
                <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false" />
                <Sidebar :collapsed="false" @close="mobileOpen = false" />
            </div>
        </Transition>

        <!-- Main content -->
        <div :class="['transition-all duration-300', isTablet ? 'sm:ml-[60px]' : 'lg:ml-[260px]']">
            <TopBar :title="title" @toggle-sidebar="mobileOpen = !mobileOpen" />
            <main class="p-4 sm:p-6 max-w-7xl mx-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
