<?php

use App\Services\RecurringTransactionService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$person_id = 3;

$allRecurring = app(RecurringTransactionService::class)->get_all();
echo 'All recurring count: '.count($allRecurring)."\n";
foreach ($allRecurring as $r) {
    echo "ID: {$r->id}, Account Person ID: ".($r->account->person_id ?? 'null')."\n";
}

$today = now()->startOfDay();
$dailyHits = array_fill(0, 31, 0);

foreach ($allRecurring as $rec) {
    if (! $rec->is_active) {
        continue;
    }
    if ($person_id && $rec->account && $rec->account->person_id !== $person_id) {
        continue;
    }

    echo 'Processing recurring ID: '.$rec->id."\n";
}
