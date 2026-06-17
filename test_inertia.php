<?php

use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);

$request = Request::create('/transactions', 'GET');
$request->headers->set('X-Inertia', 'true');
// Login as a user, assuming user ID 1 exists.
$user = User::first();
if ($user) {
    Auth::login($user);
}

$response = $kernel->handle($request);
echo $response->getContent();
