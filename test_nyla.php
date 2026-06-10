<?php

use App\Services\AccountService;
use App\Services\BudgetGoalService;
use App\Services\RecurringTransactionService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$person_id = 3;
$month = now()->month;
$year = now()->year;

$balance = app(AccountService::class)->get_total_balance($person_id);
$recurring = app(RecurringTransactionService::class)->get_upcoming($month, $year, $person_id);
$goals = app(BudgetGoalService::class)->get_for_month($month, $year, $person_id);
$upcomingAmount = $recurring->sum('amount');
$remainingBudgets = $goals->sum('remaining');

echo json_encode([
    'balance' => $balance,
    'upcoming' => $upcomingAmount,
    'remainingBudgets' => $remainingBudgets,
    'safe' => $balance - $upcomingAmount - $remainingBudgets,
    'goals' => $goals->toArray(),
], JSON_PRETTY_PRINT);
