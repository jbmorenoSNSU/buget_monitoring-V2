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
     *
     * @param AccountRepositoryInterface $accountRepository
     * @param TransactionRepositoryInterface $transactionRepository
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
     * @param int|null $person_id
     * @return Collection<int, Account>
     */
    public function get_active(?int $person_id = null): Collection
    {
        return $this->accountRepository->all_active($person_id);
    }

    /**
     * Create a new financial account.
     *
     * @param array<string, mixed> $data
     * @return Account
     */
    public function create(array $data): Account
    {
        return $this->accountRepository->create($data);
    }

    /**
     * Update an existing financial account.
     *
     * @param Account $account
     * @param array<string, mixed> $data
     * @return Account
     */
    public function update(Account $account, array $data): Account
    {
        return $this->accountRepository->update($account, $data);
    }

    /**
     * Toggle the active status of an account.
     *
     * @param Account $account
     * @return Account
     */
    public function toggle(Account $account): Account
    {
        return $this->accountRepository->update($account, ['is_active' => !$account->is_active]);
    }

    /**
     * Determine if an account can be safely deleted.
     *
     * @param Account $account
     * @return bool
     */
    public function can_delete(Account $account): bool
    {
        return !$this->accountRepository->has_transactions($account);
    }

    /**
     * Delete a financial account.
     *
     * @param Account $account
     * @return bool
     */
    public function delete(Account $account): bool
    {
        if (!$this->can_delete($account)) {
            return false;
        }
        return $this->accountRepository->delete($account);
    }

    /**
     * Recalculate and update the current balance of an account.
     *
     * @param Account $account
     * @return Account
     */
    public function recalculate_balance(Account $account): Account
    {
        $income = $this->transactionRepository->sum_by_account_and_type($account->id, 'income');
        $expense = $this->transactionRepository->sum_by_account_and_type($account->id, 'expense');
        $transfers_out = $this->transactionRepository->sum_by_account_and_type($account->id, 'transfer');
        $transfers_in = $this->transactionRepository->sum_by_account_and_type($account->id, 'transfer', true);

        $new_balance = (float)$account->initial_balance + $income - $expense - $transfers_out + $transfers_in;
        return $this->accountRepository->update($account, ['current_balance' => $new_balance]);
    }

    /**
     * Get the aggregated balance of all accounts, optionally filtered by person.
     *
     * @param int|null $person_id
     * @return float
     */
    public function get_total_balance(?int $person_id = null): float
    {
        return $this->accountRepository->total_balance($person_id);
    }
}
