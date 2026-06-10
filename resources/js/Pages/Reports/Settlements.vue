<script setup>
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import AppCard from '@/Components/UI/AppCard.vue';
import AppInput from '@/Components/UI/AppInput.vue';
import AppTable from '@/Components/UI/AppTable.vue';
import AppIcon from '@/Components/UI/AppIcon.vue';
import AppModal from '@/Components/UI/AppModal.vue';
import AppButton from '@/Components/UI/AppButton.vue';
import QrcodeVue from 'qrcode.vue';
import { useCurrency } from '@/composables/useCurrency.js';

const props = defineProps({
    data: { type: Object, default: () => ({ settlements: [], transactions: [] }) },
    filters: { type: Object, default: () => ({}) },
});

const { formatPeso } = useCurrency();
const dateFrom = ref(props.filters.from || new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0]);
const dateTo = ref(props.filters.to || new Date().toISOString().split('T')[0]);

const filter = () => {
    router.get('/reports/settlements', {
        from: dateFrom.value || undefined,
        to: dateTo.value || undefined,
    }, { preserveState: true });
};

const columns = [
    { key: 'date', label: 'Date' },
    { key: 'description', label: 'Description' },
    { key: 'payer', label: 'Paid By' },
    { key: 'debtor', label: 'Split With' },
    { key: 'total_amount', label: 'Total Amount', class: 'text-right', cellClass: 'text-right' },
    { key: 'split_amount', label: 'Split Amount', class: 'text-right', cellClass: 'text-right' },
];

const showQRModal = ref(false);
const qrPayload = ref('');
const qrPersonName = ref('');
const qrAmount = ref(0);

const openQRModal = (settlement) => {
    qrPersonName.value = settlement.payer.name;
    qrAmount.value = settlement.amount;
    qrPayload.value = JSON.stringify({
        app: 'BudgetMonitor',
        type: 'expense',
        amount: settlement.amount,
        description: `Settlement to ${settlement.payer.name}`
    });
    showQRModal.value = true;
};
</script>

<template>
    <AppLayout title="Debt & Settlements Report">
        <div class="flex flex-wrap items-end gap-3 mb-6">
            <AppInput v-model="dateFrom" type="date" label="From Date" @change="filter" />
            <AppInput v-model="dateTo" type="date" label="To Date" @change="filter" />
        </div>

        <div class="bg-indigo-900/30 border border-indigo-500/30 rounded-xl p-4 mb-6 flex items-start gap-3 shadow-inner">
            <AppIcon name="Info" size="20" class="text-indigo-400 shrink-0 mt-0.5" />
            <div class="text-sm text-indigo-100/90 leading-relaxed">
                <p class="font-semibold text-indigo-100 mb-1">What is this report for?</p>
                <p>
                    This report is specifically for tracking <strong>Split Bills</strong> between people in your household (e.g. if you paid for dinner and split the cost). It calculates who owes who so you can settle up. 
                    If you are looking to track external loans, credit cards, or device installments, please use the <strong><Link href="/debts" class="text-indigo-300 hover:text-indigo-200 underline">Debts</Link></strong> page instead.
                </p>
            </div>
        </div>

        <div class="mb-8">
            <h3 class="text-lg font-semibold text-slate-100 mb-4">Settlement Plan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="(settlement, index) in data.settlements" :key="index"
                     class="bg-card-bg border border-border rounded-xl p-5 shadow-sm flex items-center justify-between">
                    <div class="flex flex-col items-center gap-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                             :style="{ backgroundColor: settlement.debtor.color + '30', color: settlement.debtor.color }">
                            {{ settlement.debtor.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-xs font-medium text-slate-300">{{ settlement.debtor.name }}</span>
                    </div>

                    <div class="flex flex-col items-center flex-1 px-4">
                        <span class="text-[10px] text-slate-400 uppercase tracking-wider mb-1">Owes</span>
                        <div class="flex items-center w-full">
                            <div class="h-px bg-border flex-1"></div>
                            <AppIcon name="ArrowRight" size="14" class="text-slate-500 mx-2" />
                            <div class="h-px bg-border flex-1"></div>
                        </div>
                        <span class="text-base font-bold text-expense mt-1">{{ formatPeso(settlement.amount) }}</span>
                        <button @click="openQRModal(settlement)" class="mt-2 text-[10px] bg-slate-800 hover:bg-slate-700 text-slate-300 px-2 py-1 rounded flex items-center gap-1 transition-colors cursor-pointer">
                            <AppIcon name="QrCode" size="10" /> Pay QR
                        </button>
                    </div>

                    <div class="flex flex-col items-center gap-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm"
                             :style="{ backgroundColor: settlement.payer.color + '30', color: settlement.payer.color }">
                            {{ settlement.payer.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="text-xs font-medium text-slate-300">{{ settlement.payer.name }}</span>
                    </div>
                </div>

                <div v-if="data.settlements.length === 0" class="col-span-full py-8 text-center text-slate-400 border border-dashed border-border rounded-xl">
                    No debts or settlements needed for this period. Everyone is square!
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-slate-100 mb-4">Split Transactions History</h3>
            <AppCard class="overflow-hidden">
                <AppTable :columns="columns" :rows="data.transactions">
                    <template #cell-payer="{ value }">
                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300">{{ value }}</span>
                    </template>
                    <template #cell-debtor="{ value }">
                        <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-800 text-slate-300">{{ value }}</span>
                    </template>
                    <template #cell-total_amount="{ value }"><span class="font-medium">{{ formatPeso(value) }}</span></template>
                    <template #cell-split_amount="{ value }"><span class="font-semibold text-expense">{{ formatPeso(value) }}</span></template>
                </AppTable>
            </AppCard>
        </div>

        <AppModal :show="showQRModal" title="Settlement Payment QR" @close="showQRModal = false">
            <div class="flex flex-col items-center py-6">
                <div class="bg-white p-4 rounded-xl shadow-sm mb-4">
                    <qrcode-vue :value="qrPayload" :size="200" level="M" />
                </div>
                <p class="text-slate-100 font-medium text-center mb-1 text-lg">Pay {{ qrPersonName }}</p>
                <p class="text-expense font-bold text-2xl mb-2">{{ formatPeso(qrAmount) }}</p>
                <p class="text-slate-400 text-sm text-center max-w-sm">Scan this code from the <strong>Transactions</strong> page to quickly log this settlement payment.</p>
            </div>
            <template #footer>
                <AppButton variant="secondary" @click="showQRModal = false" class="w-full">Close</AppButton>
            </template>
        </AppModal>
    </AppLayout>
</template>
