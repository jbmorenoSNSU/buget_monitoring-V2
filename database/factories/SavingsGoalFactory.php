<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Person;
use App\Models\SavingsGoal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsGoal>
 */
class SavingsGoalFactory extends Factory
{
    protected $model = SavingsGoal::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'person_id' => null,
            'name' => fake()->words(3, true),
            'target_amount' => 5000.00,
            'current_amount' => 1000.00,
            'target_date' => now()->addDays(90)->toDateString(),
        ];
    }
}
