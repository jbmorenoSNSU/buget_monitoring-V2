import { defineStore } from 'pinia';
import axios from 'axios';

interface Account {
  id: number;
  name: string;
  is_active: boolean;
  current_balance: number;
  description?: string;
  initial_balance: number;
  person?: {
    id: number;
    name: string;
    color: string;
  };
  account_type?: {
    id: number;
    name: string;
  };
}

export const useAccountStore = defineStore('account', {
  state: () => ({
    accounts: [] as Account[],
    loading: false,
    error: null as string | null,
  }),

  getters: {
    totalBalance: (state) => {
      return state.accounts.reduce((sum, account) => sum + (account.is_active ? Number(account.current_balance) : 0), 0);
    },
    activeAccounts: (state) => {
      return state.accounts.filter(account => account.is_active);
    }
  },

  actions: {
    setAccounts(accounts: Account[]) {
      this.accounts = accounts;
    },

    async toggleAccount(id: number) {
      this.loading = true;
      this.error = null;
      try {
        await axios.patch(route('accounts.toggle', id));
        const account = this.accounts.find(a => a.id === id);
        if (account) {
          account.is_active = !account.is_active;
        }
      } catch (err: any) {
        this.error = err.message || 'Failed to toggle account';
        throw err;
      } finally {
        this.loading = false;
      }
    },

    $reset() {
      this.accounts = [];
      this.loading = false;
      this.error = null;
    }
  }
});
