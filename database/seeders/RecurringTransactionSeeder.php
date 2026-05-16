<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Category;
use App\Models\RecurringTransaction;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RecurringTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $bdo = Account::where('name', 'BDO Savings')->first();
        $gcash = Account::where('name', 'GCash')->first();
        $cats = Category::all()->keyBy('name');
        $now = Carbon::now();

        $items = [
            [
                'account_id' => $bdo->id, 'category_id' => $cats['Salary']->id,
                'type' => 'income', 'amount' => 35000.00,
                'description' => 'Monthly Salary', 'frequency' => 'monthly',
                'start_date' => $now->copy()->subMonths(3)->startOfMonth(),
                'next_due_date' => $now->copy()->addMonth()->startOfMonth(),
            ],
            [
                'account_id' => $bdo->id, 'category_id' => $cats['Utilities']->id,
                'type' => 'expense', 'amount' => 1800.00,
                'description' => 'Meralco Bill', 'frequency' => 'monthly',
                'start_date' => $now->copy()->subMonths(3)->day(5),
                'next_due_date' => $now->copy()->addMonth()->day(5),
            ],
            [
                'account_id' => $bdo->id, 'category_id' => $cats['Utilities']->id,
                'type' => 'expense', 'amount' => 1299.00,
                'description' => 'Internet Bill', 'frequency' => 'monthly',
                'start_date' => $now->copy()->subMonths(3)->day(7),
                'next_due_date' => $now->copy()->addMonth()->day(7),
            ],
            [
                'account_id' => $gcash->id, 'category_id' => $cats['Entertainment']->id,
                'type' => 'expense', 'amount' => 299.00,
                'description' => 'Netflix Subscription', 'frequency' => 'monthly',
                'start_date' => $now->copy()->subMonths(3)->day(11),
                'next_due_date' => $now->copy()->addMonth()->day(11),
            ],
        ];

        foreach ($items as $item) {
            RecurringTransaction::create(array_merge($item, ['is_active' => true]));
        }
    }
}
