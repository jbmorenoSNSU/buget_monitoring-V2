<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RecurringFrequency;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringTransaction>
 */
class RecurringTransactionFactory extends Factory
{
    protected $model = RecurringTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'type' => fake()->randomElement([TransactionType::Income, TransactionType::Expense]),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'description' => fake()->sentence(3),
            'frequency' => fake()->randomElement([RecurringFrequency::Monthly, RecurringFrequency::Weekly]),
            'start_date' => now()->subMonths(3),
            'end_date' => null,
            'next_due_date' => now()->addDays(fake()->numberBetween(1, 30)),
            'last_generated_date' => now()->subDays(fake()->numberBetween(1, 30)),
            'is_active' => true,
            'debt_id' => null,
        ];
    }

    /**
     * Set as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
