<?php

declare(strict_types=1);

namespace App\Actions\Debt;

use App\DTOs\Debts\DebtDTO;
use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;

/**
 * Action to update an existing debt tracker.
 */
class UpdateDebtAction
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {}

    /**
     * Execute the action.
     */
    public function execute(Debt $debt, DebtDTO $dto): Debt
    {
        return $this->debtRepository->update($debt, (array) $dto);
    }
}
