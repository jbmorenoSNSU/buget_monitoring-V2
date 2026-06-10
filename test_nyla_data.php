<?php

use App\Models\Account;
use App\Models\BudgetGoal;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$person_id = 3;

$accounts = Account::where('person_id', $person_id)->get();
echo "Nyla Accounts:\n";
print_r($accounts->toArray());

$transactions = Transaction::whereHas('account', function ($q) use ($person_id) {
    $q->where('person_id', $person_id);
})->get();
echo "\nNyla Transactions:\n";
print_r($transactions->toArray());

$recurring = RecurringTransaction::whereHas('account', function ($q) use ($person_id) {
    $q->where('person_id', $person_id);
})->get();
echo "\nNyla Recurring Transactions:\n";
print_r($recurring->toArray());

$budgetGoals = BudgetGoal::where('person_id', $person_id)->get();
echo "\nNyla Budget Goals:\n";
print_r($budgetGoals->toArray());
