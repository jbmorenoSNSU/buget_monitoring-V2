import { computed, ref } from 'vue';
import { defineStore } from 'pinia';

interface Transaction {
    id: number;
    account_id: number;
    category_id?: number;
    type: 'income' | 'expense' | 'transfer';
    amount: number;
    transaction_date: string;
    description: string;
    notes?: string;
    reference_number?: string;
    transfer_to_account_id?: number;
}

interface Filters {
    type: string;
    account_id: string;
    category_id: string;
    person_id: string;
    date_from: string;
    date_to: string;
    search: string;
    sort_by: string;
    sort_direction: string;
    per_page: string;
}

const DEFAULT_FILTERS: Filters = {
    type: '',
    account_id: '',
    category_id: '',
    person_id: '',
    date_from: '',
    date_to: '',
    search: '',
    sort_by: 'transaction_date',
    sort_direction: 'desc',
    per_page: '15',
};

/**
 * Store for managing transaction list state and filter state.
 */
export const useTransactionStore = defineStore('transaction', () => {
    const transactions = ref<Transaction[]>([]);
    const filters = ref<Filters>({ ...DEFAULT_FILTERS });
    const loading = ref(false);
    const error = ref<string | null>(null);

    const incomeTransactions = computed(() =>
        transactions.value.filter((t) => t.type === 'income')
    );

    const expenseTransactions = computed(() =>
        transactions.value.filter((t) => t.type === 'expense')
    );

    function setTransactions(data: Transaction[]): void {
        transactions.value = data;
    }

    function updateFilters(newFilters: Partial<Filters>): void {
        filters.value = { ...filters.value, ...newFilters };
    }

    function resetFilters(): void {
        filters.value = { ...DEFAULT_FILTERS };
    }

    function $reset(): void {
        transactions.value = [];
        resetFilters();
        loading.value = false;
        error.value = null;
    }

    return {
        transactions,
        filters,
        loading,
        error,
        incomeTransactions,
        expenseTransactions,
        setTransactions,
        updateFilters,
        resetFilters,
        $reset,
    };
});
