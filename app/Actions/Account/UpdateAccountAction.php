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
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Execute the account update.
     */
    public function execute(Account $account, AccountDTO $dto): Account
    {
        return $this->accountRepository->update($account, $dto->toArray());
    }
}
