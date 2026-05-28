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

export const useTransactionStore = defineStore('transaction', {
  state: () => ({
    transactions: [] as Transaction[],
    filters: {
      type: '',
      account_id: '',
      category_id: '',
      person_id: '',
      date_from: '',
      date_to: '',
      search: '',
      sort_by: 'transaction_date',
      sort_direction: 'desc',
      per_page: '15'
    } as Filters,
    loading: false,
    error: null as string | null,
  }),

  getters: {
    incomeTransactions: (state) => {
      return state.transactions.filter(t => t.type === 'income');
    },
    expenseTransactions: (state) => {
      return state.transactions.filter(t => t.type === 'expense');
    }
  },

  actions: {
    setTransactions(transactions: Transaction[]) {
      this.transactions = transactions;
    },

    updateFilters(newFilters: Partial<Filters>) {
      this.filters = { ...this.filters, ...newFilters };
    },

    resetFilters() {
      this.filters = {
        type: '',
        account_id: '',
        category_id: '',
        person_id: '',
        date_from: '',
        date_to: '',
        search: '',
        sort_by: 'transaction_date',
        sort_direction: 'desc',
        per_page: '15'
      };
    },

    $reset() {
      this.transactions = [];
      this.resetFilters();
      this.loading = false;
      this.error = null;
    }
  }
});
