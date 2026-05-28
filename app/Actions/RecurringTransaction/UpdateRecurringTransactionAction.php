<?php

declare(strict_types=1);

namespace App\Actions\RecurringTransaction;

use App\DTOs\RecurringTransactionDTO;
use App\Interfaces\RecurringTransactionRepositoryInterface;
use App\Models\RecurringTransaction;

/**
 * Single-purpose action for updating an existing recurring transaction.
 */
class UpdateRecurringTransactionAction
{
    /**
     * @param RecurringTransactionRepositoryInterface $recurringRepository
     */
    public function __construct(
        private RecurringTransactionRepositoryInterface $recurringRepository
    ) {}

    /**
     * Execute the recurring transaction update.
     *
     * @param RecurringTransaction $recurring
     * @param RecurringTransactionDTO $dto
     * @return RecurringTransaction
     */
    public function execute(RecurringTransaction $recurring, RecurringTransactionDTO $dto): RecurringTransaction
    {
        return $this->recurringRepository->update($recurring, $dto->toArray());
    }
}
