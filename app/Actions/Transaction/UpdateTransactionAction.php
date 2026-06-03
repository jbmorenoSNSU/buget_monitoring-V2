<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\DTOs\TransactionDTO;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;

/**
 * Single-purpose action for updating an existing financial transaction.
 */
class UpdateTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Execute the transaction update.
     */
    public function execute(Transaction $transaction, TransactionDTO $dto): Transaction
    {
        return $this->transactionRepository->update($transaction, $dto->toArray());
    }
}
