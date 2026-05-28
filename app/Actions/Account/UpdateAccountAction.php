<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\DTOs\AccountDTO;
use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;

/**
 * Single-purpose action for updating a financial account.
 */
class UpdateAccountAction
{
    /**
     * @param AccountRepositoryInterface $accountRepository
     */
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Execute the account update.
     *
     * @param Account $account
     * @param AccountDTO $dto
     * @return Account
     */
    public function execute(Account $account, AccountDTO $dto): Account
    {
        return $this->accountRepository->update($account, $dto->toArray());
    }
}
