<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\DTOs\TransactionDTO;
use App\Interfaces\TransactionRepositoryInterface;
use App\Models\Transaction;

/**
 * Single-purpose action for recording a new financial transaction.
 */
class CreateTransactionAction
{
    public function __construct(
        private TransactionRepositoryInterface $transactionRepository
    ) {}

    /**
     * Execute the transaction creation.
     */
    public function execute(TransactionDTO $dto): Transaction
    {
        return $this->transactionRepository->create($dto->toArray());
    }
}
