<?php

use App\Services\ReportService;
use App\Services\TransactionService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$person_id = 3;
$month = now()->month;
$year = now()->year;

$reportService = app(ReportService::class);

echo "Daily Trend:\n";
print_r($reportService->daily_spending_trend($month, $year, $person_id));

echo "\nWeekly Trend:\n";
print_r($reportService->weekly_spending_trend($person_id));

echo "\nCategory Expense:\n";
print_r($reportService->category_expense($month, $year, $person_id));

echo "\nMonthly Income/Expense:\n";
$ts = app(TransactionService::class);
echo 'Income: '.$ts->get_monthly_income($month, $year, $person_id)."\n";
echo 'Expense: '.$ts->get_monthly_expense($month, $year, $person_id)."\n";
