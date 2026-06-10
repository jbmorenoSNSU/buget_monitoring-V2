<?php

use App\Models\RecurringTransaction;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$recurring = RecurringTransaction::with('account.person')->get();

foreach ($recurring as $r) {
    echo "Recurring ID: {$r->id}\n";
    echo "  Desc: {$r->description}\n";
    echo "  Amount: {$r->amount}\n";
    echo "  Account ID: {$r->account_id}\n";
    if ($r->account) {
        echo "  Account Name: {$r->account->name}\n";
        echo '  Account Person ID: '.($r->account->person_id ?? 'null')."\n";
        if ($r->account->person) {
            echo "  Account Person Name: {$r->account->person->name}\n";
        } else {
            echo "  Account Person Name: Shared / None\n";
        }
    } else {
        echo "  No account linked!\n";
    }
    echo "--------------------------\n";
}
