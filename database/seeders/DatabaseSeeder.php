<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with sample personal finance data.
     */
    public function run(): void
    {
        $this->call([
            AccountTypeSeeder::class,
            AccountSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            RecurringTransactionSeeder::class,
            BudgetGoalSeeder::class,
        ]);
    }
}
