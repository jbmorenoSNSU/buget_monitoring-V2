<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

/**
 * Seed default account types for financial accounts.
 */
class AccountTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Cash', 'icon' => 'banknotes'],
            ['name' => 'Bank Account', 'icon' => 'building-library'],
            ['name' => 'E-Wallet', 'icon' => 'device-phone-mobile'],
            ['name' => 'Credit Card', 'icon' => 'credit-card'],
            ['name' => 'Savings Account', 'icon' => 'currency-dollar'],
        ];

        foreach ($types as $type) {
            AccountType::create($type);
        }
    }
}
