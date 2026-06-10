<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\Debts\DebtDTO;
use App\Interfaces\DebtRepositoryInterface;
use App\Models\Debt;
use Illuminate\Contracts\Pagination\CursorPaginator;

class DebtService
{
    public function __construct(
        private DebtRepositoryInterface $debtRepository
    ) {}

    public function paginate(?int $person_id = null): CursorPaginator
    {
        $paginator = $this->debtRepository->paginate($person_id);

        $paginator->getCollection()->transform(function ($debt) {
            $debt->payoff_projection = $this->calculate_payoff($debt);

            return $debt;
        });

        return $paginator;
    }

    public function create(DebtDTO $dto): Debt
    {
        return $this->debtRepository->create((array) $dto);
    }

    public function update(Debt $debt, DebtDTO $dto): Debt
    {
        return $this->debtRepository->update($debt, (array) $dto);
    }

    public function delete(Debt $debt): void
    {
        $this->debtRepository->delete($debt);
    }

    /**
     * Calculates the payoff date and total interest paid based on minimum payment.
     */
    private function calculate_payoff(Debt $debt): array
    {
        $balance = $debt->principal_amount;
        $monthly_interest_rate = ($debt->interest_rate / 100) / 12;
        $payment = $debt->minimum_payment;

        if ($balance <= 0) {
            return ['months' => 0, 'payoff_date' => now()->format('M Y'), 'total_interest' => 0, 'is_possible' => true];
        }

        if ($payment <= 0 || ($balance * $monthly_interest_rate >= $payment)) {
            return ['months' => -1, 'payoff_date' => 'Never', 'total_interest' => 0, 'is_possible' => false];
        }

        $months = 0;
        $total_interest = 0;
        $max_months = 1200; // 100 years safeguard

        while ($balance > 0 && $months < $max_months) {
            $interest = $balance * $monthly_interest_rate;
            $total_interest += $interest;

            $balance = $balance + $interest - $payment;
            $months++;
        }

        $payoff_date = now()->addMonths($months);

        return [
            'months' => $months,
            'payoff_date' => $payoff_date->format('M Y'),
            'total_interest' => round($total_interest, 2),
            'is_possible' => true,
        ];
    }
}
