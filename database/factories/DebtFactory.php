<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Debt;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Debt>
 */
class DebtFactory extends Factory
{
    protected $model = Debt::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'person_id' => Person::factory(),
            'name' => fake()->randomElement(['Car Loan', 'Student Loan', 'Credit Card', 'Medical Bill', 'Personal Loan']),
            'principal_amount' => fake()->randomFloat(2, 5000, 100000),
            'interest_rate' => fake()->randomFloat(2, 1, 24),
            'minimum_payment' => fake()->randomFloat(2, 500, 5000),
            'due_date_day' => fake()->numberBetween(1, 28),
            'status' => 'active',
        ];
    }

    /**
     * Set the debt as paid off.
     */
    public function paidOff(): static
    {
        return $this->state(fn (array $attributes) => [
            'principal_amount' => 0,
            'status' => 'paid_off',
        ]);
    }

    /**
     * Create without a person.
     */
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'person_id' => null,
        ]);
    }
}
