<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransaction;

use App\DTOs\RecurringTransactionDTO;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;

/**
 * Single-purpose action for scheduling a new recurring transaction.
 */
class CreateRecurringTransactionAction
{
    public function __construct(
        private RecurringTransactionRepositoryInterface $recurringRepository
    ) {}

    /**
     * Execute the recurring transaction creation.
     */
    public function execute(RecurringTransactionDTO $dto): RecurringTransaction
    {
        return $this->recurringRepository->create($dto->toArray());
    }
}
