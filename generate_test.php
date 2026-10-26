<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$data = json_decode($user->resume_json, true);
$t = new \App\Services\ResumeTemplates\Template1Minimal();
$file = $t->generate($data);
copy($file, 'generated_resume.docx');
echo 'Saved to generated_resume.docx';
