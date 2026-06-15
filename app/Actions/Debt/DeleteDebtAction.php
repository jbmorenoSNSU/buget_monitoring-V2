<?php

declare(strict_types=1);

namespace App\Actions\Debt;

use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;

/**
 * Action to delete a debt tracker.
 */
class DeleteDebtAction
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {}

    /**
     * Execute the action.
     */
    public function execute(Debt $debt): void
    {
        $this->debtRepository->delete($debt);
    }
}
