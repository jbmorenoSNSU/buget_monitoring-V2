<?php

use App\Services\AccountService;
use App\Services\RecurringTransactionService;
use Carbon\Carbon;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$person_id = 1; // Andrew
$month = now()->month;
$year = now()->year;

$totalBalance = app(AccountService::class)->get_total_balance($person_id);

$recurringService = app(RecurringTransactionService::class);

$forecast = [];
$projectedBalance = $totalBalance;
$today = now()->startOfDay();
$dailyHits = array_fill(0, 31, 0);
$allRecurring = $recurringService->get_all();

foreach ($allRecurring as $rec) {
    if (! $rec->is_active) {
        continue;
    }
    if ($person_id && $rec->account && $rec->account->person_id !== $person_id) {
        continue;
    }

    $nextDue = Carbon::parse($rec->next_due_date)->startOfDay();
    $endDate = $rec->end_date ? Carbon::parse($rec->end_date)->startOfDay() : null;
    $amount = (float) $rec->amount;
    $type = $rec->type->value ?? $rec->type;
    $amount = $type === 'income' ? $amount : -$amount;

    $hitDate = $nextDue->copy();
    $freq = $rec->frequency->value ?? $rec->frequency;

    while ($hitDate->diffInDays($today, false) >= -30) {
        $daysFromNow = max(0, (int) $today->diffInDays($hitDate, false));

        if ($daysFromNow <= 30 && (! $endDate || $hitDate->lte($endDate))) {
            $dailyHits[$daysFromNow] += $amount;
        }

        match ($freq) {
            'daily' => $hitDate->addDay(),
            'weekly' => $hitDate->addWeek(),
            'monthly' => $hitDate->addMonth(),
            'yearly' => $hitDate->addYear(),
        };
    }
}

for ($i = 0; $i <= 30; $i++) {
    $projectedBalance += $dailyHits[$i];
    $forecast[] = [
        'date' => now()->addDays($i)->format('M d'),
        'balance' => $projectedBalance,
    ];
}

echo "Andrew Forecast:\n";
print_r($forecast);
