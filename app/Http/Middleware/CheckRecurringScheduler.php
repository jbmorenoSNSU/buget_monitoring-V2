<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CheckRecurringScheduler
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // For local WAMP servers without cron, check if recurring logic ran today
        $today = date('Y-m-d');

        if (! Cache::has("recurring_run_{$today}")) {
            // It's possible to dispatch the command silently.
            // Using a queued job or dispatchAfterResponse is preferred,
            // but Artisan::queue() works if queue worker is running.
            // For simple setups, Artisan::call is used but can delay response.
            // To prevent blocking, we just set cache immediately to prevent concurrent triggers.
            Cache::put("recurring_run_{$today}", true, now()->endOfDay());

            // Dispatch the command quietly
            Artisan::queue('recurring:generate');
        }

        return $next($request);
    }
}
