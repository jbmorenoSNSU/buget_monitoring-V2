<script setup lang="ts">
import { ref, watch } from 'vue';
import axios from 'axios';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import { useCurrency } from '@/composables/useCurrency';

const props = defineProps<{
    show: boolean;
    debt: any | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { formatPeso } = useCurrency();

const transactions = ref<any[]>([]);
const isLoading = ref(false);

const fetchHistory = async () => {
    if (!props.debt) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/v1/debts/${props.debt.id}/transactions`);
        transactions.value = response.data;
    } catch (error) {
        console.error('Failed to fetch debt history:', error);
    } finally {
        isLoading.value = false;
    }
};

watch(() => props.show, (newVal) => {
    if (newVal && props.debt) {
        fetchHistory();
    } else {
        transactions.value = [];
    }
});
</script>

<template>
    <AppModal 
        :show="show" 
        :title="`Transaction History: ${debt?.name}`" 
        @close="emit('close')"
        maxWidth="2xl"
    >
        <div class="space-y-4">
            <!-- Loading State -->
            <div v-if="isLoading" class="flex flex-col items-center justify-center py-12 space-y-4 text-slate-400">
                <AppIcon name="Loader" class="animate-spin" size="32" />
                <p>Loading transaction history...</p>
            </div>

            <!-- Empty State -->
            <div v-else-if="!transactions.length" class="text-center py-12 border-2 border-dashed border-border rounded-xl">
                <AppIcon name="History" size="48" class="mx-auto text-slate-500 mb-4" />
                <h3 class="text-lg font-medium text-slate-300">No Transactions Found</h3>
                <p class="text-slate-500 mt-1">There are no recorded payments or deductions for this debt.</p>
            </div>

            <!-- History List -->
            <div v-else class="space-y-3">
                <div 
                    v-for="transaction in transactions" 
                    :key="transaction.id"
                    class="flex items-center justify-between p-3 bg-sidebar/50 rounded-lg border border-border/30"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0"
                             :style="{ backgroundColor: (transaction.category?.color || '#6366F1') + '20', color: transaction.category?.color || '#6366F1' }">
                            <AppIcon :name="transaction.category?.icon || 'DollarSign'" size="20" />
                        </div>
                        <div>
                            <p class="font-medium text-slate-200 text-sm">{{ transaction.description || transaction.category?.name || 'Payment' }}</p>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-slate-400">
                                <span>{{ transaction.transaction_date }}</span>
                                <span class="text-slate-600">•</span>
                                <span>From: {{ transaction.account?.name || 'Unknown Account' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-bold text-emerald-400">
                            {{ formatPeso(transaction.amount) }}
                        </p>
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider">Paid</p>
                    </div>
                </div>
            </div>
        </div>

        <template #footer>
            <AppButton variant="secondary" @click="emit('close')">Close</AppButton>
        </template>
    </AppModal>
</template>
