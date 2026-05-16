<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Seed income and expense categories with icons and colors.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Income categories
            ['name' => 'Salary', 'type' => 'income', 'icon' => 'banknotes', 'color' => '#16A34A'],
            ['name' => 'Freelance', 'type' => 'income', 'icon' => 'computer-desktop', 'color' => '#0D9488'],
            ['name' => 'Business Income', 'type' => 'income', 'icon' => 'briefcase', 'color' => '#7C3AED'],
            ['name' => 'Investment Returns', 'type' => 'income', 'icon' => 'chart-bar', 'color' => '#2563EB'],
            ['name' => 'Other Income', 'type' => 'income', 'icon' => 'plus-circle', 'color' => '#64748B'],

            // Expense categories
            ['name' => 'Food & Dining', 'type' => 'expense', 'icon' => 'cake', 'color' => '#F97316'],
            ['name' => 'Transportation', 'type' => 'expense', 'icon' => 'truck', 'color' => '#EAB308'],
            ['name' => 'Utilities', 'type' => 'expense', 'icon' => 'bolt', 'color' => '#EF4444'],
            ['name' => 'Rent', 'type' => 'expense', 'icon' => 'home', 'color' => '#8B5CF6'],
            ['name' => 'Shopping', 'type' => 'expense', 'icon' => 'shopping-bag', 'color' => '#EC4899'],
            ['name' => 'Health & Medical', 'type' => 'expense', 'icon' => 'heart', 'color' => '#EF4444'],
            ['name' => 'Entertainment', 'type' => 'expense', 'icon' => 'film', 'color' => '#A855F7'],
            ['name' => 'Education', 'type' => 'expense', 'icon' => 'academic-cap', 'color' => '#3B82F6'],
            ['name' => 'Groceries', 'type' => 'expense', 'icon' => 'shopping-cart', 'color' => '#22C55E'],
            ['name' => 'Personal Care', 'type' => 'expense', 'icon' => 'sparkles', 'color' => '#F472B6'],
            ['name' => 'Savings Transfer', 'type' => 'expense', 'icon' => 'arrow-path', 'color' => '#0EA5E9'],
            ['name' => 'Other Expenses', 'type' => 'expense', 'icon' => 'ellipsis-horizontal', 'color' => '#94A3B8'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
