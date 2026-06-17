<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/transactions', 'GET');
$request->headers->set('X-Inertia', 'true');
// Login as a user, assuming user ID 1 exists.
$user = App\Models\User::first();
if ($user) {
    Auth::login($user);
}

$response = $kernel->handle($request);
echo $response->getContent();
