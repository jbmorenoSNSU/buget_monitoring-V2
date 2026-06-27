<?php

declare(strict_types=1);

namespace App\Observers;

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
    protected function clearCache($model)
    {
        $now = now();
        $month = $now->month;
        $year = $now->year;

        // Clear for "all" persons
        Cache::forget("dashboard:stats:{$month}:{$year}:all");

        // Clear for specific person if applicable
        if (isset($model->person_id) && $model->person_id) {
            Cache::forget("dashboard:stats:{$month}:{$year}:{$model->person_id}");
        } elseif ($model->relationLoaded('account') && $model->account && $model->account->person_id) {
            Cache::forget("dashboard:stats:{$month}:{$year}:{$model->account->person_id}");
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
