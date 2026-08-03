<?php

declare(strict_types=1);

namespace App\Observers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class InvalidateDashboardCacheObserver
{
    /**
     * Clear all possible dashboard stat caches.
     * Since stats are cached per month, year, and person_id, we can either
     * clear specifically or clear using a tag.
     * Since Cache::tags() is not supported by standard file/database cache,
     * we will clear the specific current month's cache.
     */
    protected function clearCache($model): void
    {
        $now = now();

        // ponytail: Cache::increment() on the database driver silently fails when the
        // key doesn't exist, leaving version at the default and serving stale data.
        // A microtime stamp guarantees uniqueness on every mutation.
        Cache::forever('reports_cache_version', (string) microtime(true));

        // Always clear current month's stats (most common case)
        $this->forgetKey($now->month, $now->year, $model);

        // Also clear the month the transaction itself belongs to (handles backdated/future entries)
        // ponytail: duck-type — only transactions have transaction_date, others fall through silently
        if (isset($model->transaction_date) && $model->transaction_date) {
            $txDate = Carbon::parse($model->transaction_date);
            if ($txDate->month !== $now->month || $txDate->year !== $now->year) {
                $this->forgetKey($txDate->month, $txDate->year, $model);
            }
        }
    }

    private function forgetKey(int $month, int $year, $model): void
    {
        Cache::forget("dashboard:stats:{$month}:{$year}:all");

        if (isset($model->person_id) && $model->person_id) {
            Cache::forget("dashboard:stats:{$month}:{$year}:{$model->person_id}");
        } elseif (isset($model->account_id) && $model->account_id) {
            // Load account on-demand so person_id is always available,
            // even when the Transaction was created without eager-loading.
            $account = $model->relationLoaded('account')
                ? $model->account
                : $model->load('account')->account;
            if ($account && $account->person_id) {
                Cache::forget("dashboard:stats:{$month}:{$year}:{$account->person_id}");
            }
        }
    }

    public function created($model): void
    {
        $this->clearCache($model);
    }

    public function updated($model): void
    {
        $this->clearCache($model);
    }

    public function deleted($model): void
    {
        $this->clearCache($model);
    }

    public function restored($model): void
    {
        $this->clearCache($model);
    }

    public function forceDeleted($model): void
    {
        $this->clearCache($model);
    }
}
