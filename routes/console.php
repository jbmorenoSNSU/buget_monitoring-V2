<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('recurring:generate')->daily();
Schedule::command('budget:rollover')->monthlyOn(1, '00:05');
