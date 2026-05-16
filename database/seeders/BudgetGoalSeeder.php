<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\BudgetGoal;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BudgetGoalSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $cats = Category::all()->keyBy('name');

        $goals = [
            ['category_id' => $cats['Food & Dining']->id, 'limit_amount' => 8000.00],
            ['category_id' => $cats['Transportation']->id, 'limit_amount' => 3000.00],
            ['category_id' => $cats['Entertainment']->id, 'limit_amount' => 2000.00],
            ['category_id' => $cats['Groceries']->id, 'limit_amount' => 6000.00],
            ['category_id' => $cats['Shopping']->id, 'limit_amount' => 4000.00],
        ];

        foreach ($goals as $goal) {
            BudgetGoal::create(array_merge($goal, [
                'month' => $now->month,
                'year' => $now->year,
            ]));
        }
    }
}
