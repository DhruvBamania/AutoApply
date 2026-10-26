<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
echo "Resume JSON length: " . strlen($user->resume_json) . " bytes\n";
echo "Preview:\n";
echo substr($user->resume_json, 0, 1000);
