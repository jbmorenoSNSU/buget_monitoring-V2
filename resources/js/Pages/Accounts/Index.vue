<script setup>
import { computed, ref } from 'vue';
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
</script>

<template>
    <AppLayout title="Accounts">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-1">
                <StatCard label="Total Balance" :value="formatPeso(displayBalance)" accentColor="#1E40AF" class="flex-1 max-w-xs" />
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400 font-medium whitespace-nowrap">Filter by Owner:</span>
                    <AppSelect v-model="selectedPerson" :options="personOptions" class="w-44" />
                </div>
            </div>
            <Link href="/accounts/create">
                <AppButton>+ Add Account</AppButton>
            </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="acc in filteredItems" :key="acc.id"
                class="bg-[#161B26] border border-[#232936] rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                <div class="h-2" :style="{ backgroundColor: acc.color }" />
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <AppIcon :name="acc.account_type?.icon || 'Wallet'" size="24" class="text-slate-400" />
                            <div>
                                <h3 class="font-semibold text-slate-100">{{ acc.name }}</h3>
                                <p class="text-xs text-slate-400">{{ acc.account_type?.name }}</p>
                            </div>
                        </div>
                        <AppBadge :type="acc.is_active ? 'active' : 'inactive'" :label="acc.is_active ? 'Active' : 'Inactive'" />
                    </div>
                    <div v-if="acc.person" class="flex items-center gap-1.5 mb-3">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center text-[8px] font-bold shrink-0"
                            :style="{ backgroundColor: acc.person.color + '30', color: acc.person.color }">
                            {{ acc.person.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-xs font-medium" :style="{ color: acc.person.color }">{{ acc.person.name }}</span>
                    </div>
                    <p v-if="acc.description" class="text-sm text-slate-400 mb-3">{{ acc.description }}</p>
                    <p :class="['text-2xl font-bold', acc.current_balance >= 0 ? 'text-[#F8FAFC]' : 'text-[#F43F5E]']">
                        {{ formatPeso(acc.current_balance) }}
                    </p>
                    <div class="flex gap-2 mt-4 pt-4 border-t border-[#232936]">
                        <Link :href="`/accounts/${acc.id}/edit`"><AppButton variant="secondary" size="sm">Edit</AppButton></Link>
                        <AppButton variant="ghost" size="sm" @click="toggle(acc)">{{ acc.is_active ? 'Deactivate' : 'Activate' }}</AppButton>
                        <AppButton variant="danger" size="sm" @click="confirmDelete(acc)">Delete</AppButton>
                    </div>
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
