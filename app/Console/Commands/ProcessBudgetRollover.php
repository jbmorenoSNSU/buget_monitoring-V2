<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\BudgetGoal;
use App\Services\BudgetGoalService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ProcessBudgetRollover extends Command
{
    protected $signature = 'budget:rollover';

    protected $description = 'Process budget rollovers from the previous month to the current month';

    public function handle(BudgetGoalService $budgetGoalService)
    {
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $lastMonthDate = $now->copy()->subMonth();
        $lastMonth = $lastMonthDate->month;
        $lastYear = $lastMonthDate->year;

        $this->info("Processing budget rollovers from {$lastMonth}/{$lastYear} to {$currentMonth}/{$currentYear}...");

        $lastMonthGoals = BudgetGoal::where('month', $lastMonth)
            ->where('year', $lastYear)
            ->where('is_rollover_enabled', true)
            ->get();

        $processedCount = 0;

        foreach ($lastMonthGoals as $oldGoal) {
            $cacheKey = "rollover_processed_{$oldGoal->id}";

            if (Cache::has($cacheKey)) {
                continue; // Already rolled over
            }

            $spent = $budgetGoalService->get_spent_amount($oldGoal->category_id, $lastMonth, $lastYear, $oldGoal->person_id);
            $remaining = (float) $oldGoal->limit_amount - $spent;

            if ($remaining > 0) {
                $currentGoal = BudgetGoal::firstOrNew([
                    'category_id' => $oldGoal->category_id,
                    'person_id' => $oldGoal->person_id,
                    'month' => $currentMonth,
                    'year' => $currentYear,
                ]);

                if (! $currentGoal->exists) {
                    // New goal: base limit is the old limit + remaining
                    $currentGoal->limit_amount = (float) $oldGoal->limit_amount + $remaining;
                    $currentGoal->is_rollover_enabled = true;
                } else {
                    // Existing goal: just add the remaining to whatever is there
                    $currentGoal->limit_amount = (float) $currentGoal->limit_amount + $remaining;
                }

                $currentGoal->save();
                Cache::put($cacheKey, true, now()->addDays(40));

                $this->line('Rolled over '.number_format($remaining, 2)." for category ID {$oldGoal->category_id}");
                $processedCount++;
            } else {
                Cache::put($cacheKey, true, now()->addDays(40)); // mark processed even if no remaining
            }
        }

        $this->info("Completed. Processed {$processedCount} goals with leftover funds.");
    }
}
