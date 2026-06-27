import { computed, ref } from 'vue';
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

/**
 * Store for managing financial account state and toggle actions.
 */
export const useAccountStore = defineStore('account', () => {
    const accounts = ref<Account[]>([]);
    const loading = ref(false);
    const error = ref<string | null>(null);

    const totalBalance = computed(() =>
        accounts.value.reduce(
            (sum, account) => sum + (account.is_active ? Number(account.current_balance) : 0),
            0
        )
    );

    const activeAccounts = computed(() =>
        accounts.value.filter((account) => account.is_active)
    );

    function setAccounts(data: Account[]): void {
        accounts.value = data;
    }

    async function toggleAccount(id: number): Promise<void> {
        loading.value = true;
        error.value = null;
        try {
            await axios.patch(route('accounts.toggle', id));
            const account = accounts.value.find((a) => a.id === id);
            if (account) {
                account.is_active = !account.is_active;
            }
        } catch (err: unknown) {
            error.value = err instanceof Error ? err.message : 'Failed to toggle account';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    function $reset(): void {
        accounts.value = [];
        loading.value = false;
        error.value = null;
    }

    return { accounts, loading, error, totalBalance, activeAccounts, setAccounts, toggleAccount, $reset };
});
