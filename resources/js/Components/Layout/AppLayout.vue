<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import Sidebar from './Sidebar.vue';
import TopBar from './TopBar.vue';
import TransactionFormModal from '@/Pages/Transactions/Components/TransactionFormModal.vue';
import { useFlash } from '@/composables/useFlash.js';
import { usePageTitle } from '@/composables/usePageTitle';

const { pageTitle } = usePageTitle();

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

// Quick Add State
const showQuickAdd = ref(false);
const quickAddData = ref({ accounts: [], categories: [], debts: [], persons: [] });
const quickAddForm = useForm({
    account_id: '',
    category_id: '',
    debt_id: '',
    amount: '',
    type: 'expense',
    transaction_date: new Date().toISOString().split('T')[0],
    description: '',
    notes: '',
    reference_number: '',
    transfer_to_account_id: '',
    split_bill: false,
    split_with_person_id: '',
    split_amount: '',
});

const openQuickAdd = async () => {
    showQuickAdd.value = true;
    try {
        const { data } = await axios.get('/api/v1/quick-add-data');
        quickAddData.value = data;
        
        // Auto-select first account if not set
        if (data.accounts.length > 0 && !quickAddForm.account_id) {
            quickAddForm.account_id = data.accounts[0].id;
        }
    } catch (e) {
        console.error('Failed to load quick add data', e);
    }
};

const handleQuickAddSubmit = () => {
    quickAddForm.post('/transactions', {
        preserveScroll: true,
        onSuccess: () => {
            showQuickAdd.value = false;
            quickAddForm.reset();
            quickAddForm.transaction_date = new Date().toISOString().split('T')[0];
        }
    });
};

const handleKeydown = (e) => {
    if ((e.ctrlKey || e.metaKey) && e.code === 'Space') {
        e.preventDefault();
        openQuickAdd();
    }
};

onMounted(() => {
    checkWidth();
    window.addEventListener('resize', checkWidth);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkWidth);
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <div class="min-h-screen bg-page-bg">
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

        <!-- TopBar - offset by sidebar width on desktop -->
        <div class="pt-[65px]">
            <TopBar :title="pageTitle" :sidebar-collapsed="sidebarCollapsed" @toggle-sidebar="toggleSidebar" @open-quick-add="openQuickAdd" :class="['fixed top-0 right-0 z-30 transition-all duration-300 ease-in-out', sidebarCollapsed ? 'left-0 lg:left-16' : 'left-0 lg:left-60']" />

            <!-- Main content with sidebar offset -->
            <div :class="['transition-all duration-300 ease-in-out', sidebarCollapsed ? 'lg:ml-16' : 'lg:ml-60']">
                <main class="p-4 sm:p-6 max-w-7xl mx-auto">
                    <slot />
                </main>
            </div>
        </div>

        <!-- Global Quick Add Modal -->
        <TransactionFormModal
            :show="showQuickAdd"
            :is-edit="false"
            :form="quickAddForm"
            :accounts="quickAddData.accounts"
            :categories="quickAddData.categories"
            :debts="quickAddData.debts"
            :persons="quickAddData.persons"
            @close="showQuickAdd = false"
            @submit="handleQuickAddSubmit"
        />
    </div>
</template>
