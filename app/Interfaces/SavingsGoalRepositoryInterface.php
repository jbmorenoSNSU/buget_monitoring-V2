<?php

declare(strict_types=1);

namespace App\Interfaces;

use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Collection;

interface SavingsGoalRepositoryInterface
{
    /**
     * Get all savings goals with eager loaded accounts and persons (owners).
     *
     * @return Collection<int, SavingsGoal>
     */
    public function all(?int $person_id = null): Collection;

    /**
     * Find a specific savings goal by ID.
     */
    public function find(int $id): ?SavingsGoal;

    /**
     * Create a new savings goal.
     */
    public function create(array $data): SavingsGoal;

    /**
     * Update an existing savings goal.
     */
    public function update(SavingsGoal $goal, array $data): SavingsGoal;

    /**
     * Delete a savings goal.
     */
    public function delete(SavingsGoal $goal): void;
}
