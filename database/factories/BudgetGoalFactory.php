<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BudgetGoal;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BudgetGoal>
 */
class BudgetGoalFactory extends Factory
{
    protected $model = BudgetGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'person_id' => null,
            'month' => now()->month,
            'year' => now()->year,
            'limit_amount' => fake()->randomFloat(2, 1000, 20000),
            'is_rollover_enabled' => false,
        ];
    }

    /**
     * Enable rollover for this goal.
     */
    public function withRollover(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_rollover_enabled' => true,
        ]);
    }
}
