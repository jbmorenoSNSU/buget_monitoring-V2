<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Account;

/**
 * Handles Account model events.
 * Cache busting is handled by InvalidateDashboardCacheObserver.
 */
class AccountObserver
{
    // ponytail: no-op observer kept as extension point for future per-model event hooks.
    // Dashboard cache is invalidated by InvalidateDashboardCacheObserver registered in AppServiceProvider.
}
