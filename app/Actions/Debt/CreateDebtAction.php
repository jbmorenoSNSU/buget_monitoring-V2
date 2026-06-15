<?php

declare(strict_types=1);

namespace App\Actions\Debt;

use App\DTOs\Debts\DebtDTO;
use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;

/**
 * Action to create a new debt tracker.
 */
class CreateDebtAction
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {}

    /**
     * Execute the action.
     */
    public function execute(DebtDTO $dto): Debt
    {
        return $this->debtRepository->create((array) $dto);
    }
}
