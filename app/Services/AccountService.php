<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AccountService
{
    public function getAll()
    {
        return Account::with('accountType')->orderBy('name')->get();
    }

    public function getActive()
    {
        return Account::with('accountType')->active()->orderBy('name')->get();
    }

    public function create(array $data): Account
    {
        $data['current_balance'] = $data['initial_balance'] ?? 0;
        return Account::create($data);
    }

    public function update(Account $account, array $data): Account
    {
        unset($data['initial_balance']);
        $account->update($data);
        return $account->fresh();
    }

    public function toggle(Account $account): Account
    {
        $account->update(['is_active' => !$account->is_active]);
        return $account;
    }

    public function canDelete(Account $account): bool
    {
        return $account->transactions()->count() === 0;
    }

    public function delete(Account $account): bool
    {
        if (!$this->canDelete($account)) {
            return false;
        }
        $account->delete();
        return true;
    }

    public function recalculateBalance(Account $account): Account
    {
        $income = Transaction::where('account_id', $account->id)
            ->where('type', 'income')->whereNull('deleted_at')->sum('amount');
        $expense = Transaction::where('account_id', $account->id)
            ->where('type', 'expense')->whereNull('deleted_at')->sum('amount');
        $transfersOut = Transaction::where('account_id', $account->id)
            ->where('type', 'transfer')->whereNull('deleted_at')->sum('amount');
        $transfersIn = Transaction::where('transfer_to_account_id', $account->id)
            ->where('type', 'transfer')->whereNull('deleted_at')->sum('amount');

        $account->current_balance = (float)$account->initial_balance + $income - $expense - $transfersOut + $transfersIn;
        $account->save();
        return $account;
    }

    public function getTotalBalance(): float
    {
        return (float) Account::active()->sum('current_balance');
    }
}
