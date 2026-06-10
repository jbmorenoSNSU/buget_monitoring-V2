<?php

declare(strict_types=1);

namespace App\Services;

use App\Actions\SavingsGoal\CreateSavingsGoalAction;
use App\Actions\SavingsGoal\DeleteSavingsGoalAction;
use App\Actions\SavingsGoal\UpdateSavingsGoalAction;
use App\DTOs\SavingsGoal\SavingsGoalDTO;
use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SavingsGoalService
{
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepository,
        private CreateSavingsGoalAction $createAction,
        private UpdateSavingsGoalAction $updateAction,
        private DeleteSavingsGoalAction $deleteAction
    ) {}

    /**
     * Get all savings goals with computed metrics.
     *
     * @return Collection<int, SavingsGoal>
     */
    public function all(?int $person_id = null): Collection
    {
        $goals = $this->savingsGoalRepository->all($person_id);

        return $goals->map(function (SavingsGoal $goal) {
            $target = (float) $goal->target_amount;
            $current = (float) $goal->current_amount;

            $goal->percent = $target > 0 ? round(($current / $target) * 100, 1) : 0;
            $goal->remaining_amount = max(0.0, $target - $current);
            $goal->is_completed = $current >= $target;

            if ($goal->target_date) {
                $targetDate = Carbon::parse($goal->target_date)->startOfDay();
                $today = now()->startOfDay();

                if ($targetDate->lt($today)) {
                    $goal->days_remaining = -$today->diffInDays($targetDate);
                } else {
                    $goal->days_remaining = $today->diffInDays($targetDate);
                }
            } else {
                $goal->days_remaining = null;
            }

            return $goal;
        });
    }

    /**
     * Find a savings goal.
     */
    public function find(int $id): ?SavingsGoal
    {
        return $this->savingsGoalRepository->find($id);
    }

    /**
     * Create a new savings goal.
     */
    public function create(SavingsGoalDTO $dto): SavingsGoal
    {
        return $this->createAction->execute($dto);
    }

    /**
     * Update an existing savings goal.
     */
    public function update(SavingsGoal $goal, SavingsGoalDTO $dto): SavingsGoal
    {
        return $this->updateAction->execute($goal, $dto);
    }

    /**
     * Delete a savings goal.
     */
    public function delete(SavingsGoal $goal): void
    {
        $this->deleteAction->execute($goal);
    }
}
