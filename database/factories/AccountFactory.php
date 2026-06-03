<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_type_id' => AccountType::factory(),
            'person_id' => Person::factory(),
            'name' => fake()->word().' Account',
            'description' => fake()->sentence(),
            'initial_balance' => 0.00,
            'current_balance' => 0.00,
            'color' => fake()->hexColor(),
            'is_active' => true,
        ];
    }
}
