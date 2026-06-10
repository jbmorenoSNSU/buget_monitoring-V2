<?php

use App\Interfaces\TransactionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$current_start = now()->subWeeks(11)->startOfWeek();
$current_end = now()->endOfWeek();
if ($current_end->gt(now())) {
    $current_end = now();
}
$prev_start = $current_start->copy()->subWeeks(12)->startOfWeek();
$prev_end = $current_start->copy()->subWeeks(1)->endOfWeek();

$repo = app(TransactionRepositoryInterface::class);
$transactions = $repo->expense_by_date_range(
    $prev_start->format('Y-m-d'),
    $current_end->format('Y-m-d'),
    null
);

$current_buckets = array_fill(0, 12, 0);
$prev_buckets = array_fill(0, 12, 0);

foreach ($transactions as $t) {
    $date = Carbon::parse($t->transaction_date);

    if ($date->between($current_start, $current_end)) {
        $diff_in_weeks = (int) floor($current_start->diffInWeeks($date));
        if (isset($current_buckets[$diff_in_weeks])) {
            $current_buckets[$diff_in_weeks] += (float) $t->amount;
        }
    } elseif ($date->between($prev_start, $prev_end)) {
        $diff_in_weeks = (int) floor($prev_start->diffInWeeks($date));
        if (isset($prev_buckets[$diff_in_weeks])) {
            $prev_buckets[$diff_in_weeks] += (float) $t->amount;
        }
    }
}

print_r($current_buckets);
print_r($prev_buckets);
