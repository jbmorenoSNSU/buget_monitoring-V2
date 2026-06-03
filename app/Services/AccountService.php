<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AccountRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class handling business logic for financial accounts.
 */
class AccountService
{
    /**
     * Create a new AccountService instance.
     */
    public function __construct(
        private AccountRepositoryInterface $accountRepository,
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Retrieve all financial accounts.
     *
     * @return Collection<int, Account>
     */
    public function get_all(): Collection
    {
        return $this->accountRepository->all();
    }

    /**
     * Retrieve active accounts, optionally filtered by person.
     *
     * @return Collection<int, Account>
     */
    public function get_active(?int $person_id = null): Collection
    {
        return $this->accountRepository->all_active($person_id);
    }

    /**
     * Toggle the active status of an account.
     */
    public function toggle(Account $account): Account
    {
        return $this->accountRepository->update($account, ['is_active' => ! $account->is_active]);
    }

    /**
     * Determine if an account can be safely deleted.
     */
    public function can_delete(Account $account): bool
    {
        return ! $this->accountRepository->has_transactions($account);
    }

    /**
     * Delete a financial account.
     */
    public function delete(Account $account): bool
    {
        if (! $this->can_delete($account)) {
            return false;
        }

        return $this->accountRepository->delete($account);
    }

    /**
     * Recalculate and update the current balance of an account.
     */
    public function recalculate_balance(Account $account): Account
    {
        $income = $this->transactionRepository->sum_by_account_and_type($account->id, 'income');
        $expense = $this->transactionRepository->sum_by_account_and_type($account->id, 'expense');
        $transfers_out = $this->transactionRepository->sum_by_account_and_type($account->id, 'transfer');
        $transfers_in = $this->transactionRepository->sum_by_account_and_type($account->id, 'transfer', true);

        $new_balance = (float) $account->initial_balance + $income - $expense - $transfers_out + $transfers_in;

        return $this->accountRepository->update($account, ['current_balance' => $new_balance]);
    }

    /**
     * Get the aggregated balance of all accounts, optionally filtered by person.
     */
    public function get_total_balance(?int $person_id = null): float
    {
        return $this->accountRepository->total_balance($person_id);
    }
}
