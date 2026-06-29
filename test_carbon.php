<?php

require 'vendor/autoload.php';
$today = \Carbon\Carbon::parse('2023-01-01');
$future = \Carbon\Carbon::parse('2023-01-05');
echo "today->diffInDays(future, false) = " . $today->diffInDays($future, false) . "\n";
echo "future->diffInDays(today, false) = " . $future->diffInDays($today, false) . "\n";
