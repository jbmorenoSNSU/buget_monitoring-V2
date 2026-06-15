<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;
use Illuminate\Support\Facades\Cache;

class AccountObserver
{
    /**
     * Handle the Account "created" event.
     */
    public function created(Account $account): void
    {
        Cache::increment('reports_cache_version');
    }

    /**
     * Handle the Account "updated" event.
     */
    public function updated(Account $account): void
    {
        Cache::increment('reports_cache_version');
    }

    /**
     * Handle the Account "deleted" event.
     */
    public function deleted(Account $account): void
    {
        Cache::increment('reports_cache_version');
    }

    /**
     * Handle the Account "restored" event.
     */
    public function restored(Account $account): void
    {
        Cache::increment('reports_cache_version');
    }

    /**
     * Handle the Account "force deleted" event.
     */
    public function forceDeleted(Account $account): void
    {
        Cache::increment('reports_cache_version');
    }
}
