<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Interfaces\AccountRepositoryInterface;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

class EloquentAccountRepository implements AccountRepositoryInterface
{
    public function all(): Collection
    {
        return Account::with(['accountType:id,name', 'person:id,name,color'])
            ->orderBy('name')
            ->get();
    }

    public function all_active(?int $person_id = null): Collection
    {
        $query = Account::with(['accountType:id,name', 'person:id,name,color'])
            ->active()
            ->orderBy('name');

        if ($person_id) {
            $query->where('person_id', $person_id);
        }

        return $query->get();
    }

    public function find(int $id): ?Account
    {
        return Account::with(['accountType:id,name', 'person:id,name,color'])->find($id);
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

    public function delete(Account $account): bool
    {
        return (bool) $account->delete();
    }

    public function total_balance(?int $person_id = null): float
    {
        $query = Account::active();
        if ($person_id) {
            $query->where('person_id', $person_id);
        }
        return (float) $query->sum('current_balance');
    }

    public function has_transactions(Account $account): bool
    {
        return $account->transactions()->exists();
    }
}
