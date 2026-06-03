<?php

declare(strict_types=1);

namespace App\Actions\Account;

use App\DTOs\AccountDTO;
use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;

/**
 * Single-purpose action for creating a financial account.
 */
class CreateAccountAction
{
    public function __construct(
        private AccountRepositoryInterface $accountRepository
    ) {}

    /**
     * Execute the account creation.
     */
    public function execute(AccountDTO $dto): Account
    {
        return $this->accountRepository->create($dto->toArray());
    }
}
