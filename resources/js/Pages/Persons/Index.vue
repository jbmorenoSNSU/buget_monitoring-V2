<script setup>
import { computed, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps({
    persons: { type: Object, default: () => ({ data: [] }) },
});

const { formatPeso } = useCurrency();
const items = computed(() => props.persons?.data || []);
const deleteTarget = ref(null);
const showDeleteModal = ref(false);

const confirmDelete = (person) => { deleteTarget.value = person; showDeleteModal.value = true; };
const doDelete = () => {
    router.delete(`/persons/${deleteTarget.value.id}`, { onSuccess: () => { showDeleteModal.value = false; } });
};
</script>

<template>
    <AppLayout title="Persons">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-slate-100">Manage Persons</h2>
            <Link href="/persons/create"><AppButton>+ Add Person</AppButton></Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="person in items" :key="person.id"
                class="bg-[#161B26] border border-[#232936] rounded-xl shadow-sm overflow-hidden hover:shadow-md transition-shadow group">
                <div class="h-2" :style="{ backgroundColor: person.color }" />
                <div class="p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0 shadow-lg"
                                :style="{ backgroundColor: person.color + '30', color: person.color }">
                                {{ person.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <h3 class="font-semibold text-slate-100">{{ person.name }}</h3>
                                <p class="text-xs text-slate-400">{{ person.accounts_count }} active account{{ person.accounts_count !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-[#0F111A]/50 rounded-lg p-3 mb-4 space-y-3">
                        <div>
                            <p class="text-xs text-slate-400 mb-1">Total Balance</p>
                            <p :class="['text-xl font-bold', person.total_balance >= 0 ? 'text-[#F8FAFC]' : 'text-[#F43F5E]']">
                                {{ formatPeso(person.total_balance) }}
                            </p>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-[#232936]/50">
                            <div>
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-0.5">Income This Month</p>
                                <p class="text-sm font-semibold text-[#10B981]">{{ formatPeso(person.income_this_month) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400 font-medium uppercase tracking-wider mb-0.5">Expense This Month</p>
                                <p class="text-sm font-semibold text-[#F43F5E]">{{ formatPeso(person.expense_this_month) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-[#232936]">
                        <Link :href="`/persons/${person.id}/edit`"><AppButton variant="secondary" size="sm">Edit</AppButton></Link>
                        <AppButton variant="danger" size="sm" @click="confirmDelete(person)">Delete</AppButton>
                    </div>
                </div>
            </div>
        </div>

        <p v-if="!items.length" class="text-center text-slate-400 py-12">No persons added yet. Create your first person to start tracking account ownership!</p>

        <AppModal :show="showDeleteModal" title="Delete Person" @close="showDeleteModal = false">
            <p class="text-slate-400">Are you sure you want to delete <strong>{{ deleteTarget?.name }}</strong>? You must reassign their accounts first.</p>
            <template #footer>
                <AppButton variant="secondary" @click="showDeleteModal = false">Cancel</AppButton>
                <AppButton variant="danger" @click="doDelete">Delete</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
