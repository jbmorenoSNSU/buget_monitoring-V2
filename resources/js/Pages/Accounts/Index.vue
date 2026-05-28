<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppBadge from '@/Components/UI/AppBadge.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppSelect from '@/Components/UI/AppSelect.vue';
import StatCard from '@/Components/UI/StatCard.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps({
    accounts: { type: Object, default: () => ({ data: [] }) },
    totalBalance: { type: Number, default: 0 },
});

const { formatPeso } = useCurrency();
const items = computed(() => props.accounts?.data || []);
const deleteTarget = ref(null);
const showDeleteModal = ref(false);
const selectedPerson = ref('');

// Extract unique persons from accounts for the filter dropdown
const personOptions = computed(() => {
    const persons = [];
    const seen = new Set();
    for (const acc of items.value) {
        if (acc.person && !seen.has(acc.person.id)) {
            seen.add(acc.person.id);
            persons.push({ value: acc.person.id.toString(), label: acc.person.name });
        }
    }
    persons.sort((a, b) => a.label.localeCompare(b.label));
    return [{ value: '', label: 'All Owners' }, ...persons];
});

const filteredItems = computed(() => {
    if (!selectedPerson.value) return items.value;
    return items.value.filter(acc => acc.person?.id?.toString() === selectedPerson.value);
});

const displayBalance = computed(() => {
    if (!selectedPerson.value) return props.totalBalance;
    return filteredItems.value
        .filter(acc => acc.is_active)
        .reduce((sum, acc) => sum + acc.current_balance, 0);
});

const confirmDelete = (acc) => { deleteTarget.value = acc; showDeleteModal.value = true; };
const doDelete = () => {
    router.delete(`/accounts/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } });
};
const toggle = (acc) => router.patch(`/accounts/${acc.id}/toggle`);

// Dropdown state
const activeDropdownId = ref(null);
const toggleDropdown = (id, event) => {
    event.stopPropagation();
    activeDropdownId.value = activeDropdownId.value === id ? null : id;
};
const closeDropdown = () => {
    activeDropdownId.value = null;
};
onMounted(() => {
    window.addEventListener('click', closeDropdown);
});
onUnmounted(() => {
    window.removeEventListener('click', closeDropdown);
});
</script>

<template>
    <AppLayout title="Accounts">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                <StatCard label="Total Balance" :value="formatPeso(displayBalance)" accentColor="#6366F1" class="flex-1 max-w-xs" />
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Filter by Owner:</span>
                    <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" />
                </div>
            </div>
            <Link href="/accounts/create">
                <AppButton>+ Add Account</AppButton>
            </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div v-for="acc in filteredItems" :key="acc.id"
                class="bg-card-bg border border-border rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow relative">
                <!-- Color bar (Compact Option 1: h-1 instead of h-2) -->
                <div class="h-1" :style="{ backgroundColor: acc.color }" />
                
                <div class="p-4">
                    <!-- Top section with dropdown (Option 2) -->
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex items-center gap-2 max-w-[80%]">
                            <AppIcon :name="acc.account_type?.icon || 'Wallet'" size="20" class="text-slate-400 shrink-0" />
                            <div class="min-w-0">
                                <h3 class="font-semibold text-slate-100 text-sm truncate leading-snug" :title="acc.name">{{ acc.name }}</h3>
                                <p class="text-[11px] text-slate-400 truncate leading-none mt-0.5">{{ acc.account_type?.name }}</p>
                            </div>
                        </div>
                        
                        <!-- Dropdown Menu trigger -->
                        <div class="relative shrink-0">
                            <button @click="toggleDropdown(acc.id, $event)" 
                                class="p-1 rounded hover:bg-border text-slate-400 hover:text-slate-200 transition-colors cursor-pointer focus:outline-none">
                                <AppIcon name="MoreVertical" size="16" />
                            </button>
                            
                            <!-- Dropdown List -->
                            <div v-if="activeDropdownId === acc.id" 
                                class="absolute right-0 top-7 w-32 bg-sidebar border border-border rounded-lg shadow-xl py-1 z-10"
                                @click.stop>
                                <Link :href="`/accounts/${acc.id}/edit`" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left">
                                    <AppIcon name="Edit2" size="12" /> Edit
                                </Link>
                                <button @click="toggle(acc); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-slate-300 hover:bg-border hover:text-slate-100 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon :name="acc.is_active ? 'EyeOff' : 'Eye'" size="12" />
                                    {{ acc.is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <div class="border-t border-border my-1"></div>
                                <button @click="confirmDelete(acc); activeDropdownId = null" 
                                    class="flex items-center gap-2 px-3 py-1.5 text-xs text-rose-400 hover:bg-border hover:text-rose-300 transition-colors w-full text-left cursor-pointer">
                                    <AppIcon name="Trash2" size="12" /> Delete
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Owner and Badge -->
                    <div class="flex items-center justify-between mb-2">
                        <div v-if="acc.person" class="flex items-center gap-1.5 min-w-0">
                            <div class="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-bold shrink-0"
                                :style="{ backgroundColor: acc.person.color + '30', color: acc.person.color }">
                                {{ acc.person.name.charAt(0).toUpperCase() }}
                            </div>
                            <span class="text-[11px] font-medium truncate" :style="{ color: acc.person.color }">{{ acc.person.name }}</span>
                        </div>
                        <div v-else class="h-4"></div>
                        <AppBadge :type="acc.is_active ? 'active' : 'inactive'" :label="acc.is_active ? 'Active' : 'Inactive'" class="scale-75 origin-right shrink-0" />
                    </div>

                    <!-- Description -->
                    <p v-if="acc.description" class="text-[11px] text-slate-400 mb-2 line-clamp-2 h-8" :title="acc.description">
                        {{ acc.description }}
                    </p>
                    <div v-else class="h-8"></div>

                    <!-- Balance -->
                    <p :class="['text-xl font-bold mt-1', acc.current_balance >= 0 ? 'text-slate-50' : 'text-expense']">
                        {{ formatPeso(acc.current_balance) }}
                    </p>
                </div>
            </div>
        </div>

        <p v-if="!filteredItems.length" class="text-center text-slate-400 py-12">
            {{ selectedPerson ? 'No accounts found for this person.' : 'No accounts yet. Create your first account!' }}
        </p>

        <AppModal :show="showDeleteModal" title="Delete Account" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>?</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
