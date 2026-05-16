<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Seeder;

/**
 * Seed sample financial accounts with realistic Philippine data.
 */
class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $cash = AccountType::where('name', 'Cash')->first();
        $bank = AccountType::where('name', 'Bank Account')->first();
        $ewallet = AccountType::where('name', 'E-Wallet')->first();
        $credit = AccountType::where('name', 'Credit Card')->first();

        $accounts = [
            [
                'account_type_id' => $cash->id,
                'name' => 'Cash on Hand',
                'description' => 'Physical cash wallet',
                'initial_balance' => 5000.00,
                'current_balance' => 5000.00,
                'color' => '#1E40AF',
                'is_active' => true,
            ],
            [
                'account_type_id' => $bank->id,
                'name' => 'BDO Savings',
                'description' => 'BDO primary savings account',
                'initial_balance' => 42500.00,
                'current_balance' => 42500.00,
                'color' => '#16A34A',
                'is_active' => true,
            ],
            [
                'account_type_id' => $ewallet->id,
                'name' => 'GCash',
                'description' => 'GCash e-wallet',
                'initial_balance' => 3200.00,
                'current_balance' => 3200.00,
                'color' => '#2563EB',
                'is_active' => true,
            ],
            [
                'account_type_id' => $credit->id,
                'name' => 'BDO Credit Card',
                'description' => 'BDO Visa credit card',
                'initial_balance' => -8500.00,
                'current_balance' => -8500.00,
                'color' => '#DC2626',
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            Account::create($account);
        }
    }
}
