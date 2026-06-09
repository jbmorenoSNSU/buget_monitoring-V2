<?php

use App\Services\RecurringTransactionService;

try {
    $service = app(RecurringTransactionService::class);
    $count = $service->generate_due();
    echo 'Generated: '.$count."\n";
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage()."\n";
}
