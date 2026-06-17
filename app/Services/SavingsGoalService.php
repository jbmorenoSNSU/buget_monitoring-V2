<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\SavingsGoalRepositoryInterface;
use App\Models\SavingsGoal;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class handling computed metrics for savings goals.
 */
class SavingsGoalService
{
    /**
     * Create a new SavingsGoalService instance.
     */
    public function __construct(
        private SavingsGoalRepositoryInterface $savingsGoalRepository
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
     * Find a savings goal by ID.
     */
    public function find(int $id): ?SavingsGoal
    {
        return $this->savingsGoalRepository->find($id);
    }
}
