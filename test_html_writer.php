<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
$data = json_decode($user->resume_json, true);
$t = new \App\Services\ResumeTemplates\Template1Minimal();

// The class returns a tempfile string, but we want the PhpWord object itself to test HTML.
// Wait, `Template1Minimal::generate` saves it and returns string path.
// I will temporarily rewrite the script to do this manually.

$phpWord = new \PhpOffice\PhpWord\PhpWord();
$section = $phpWord->addSection();
$section->addText('Test text', ['bold' => true, 'size' => 16]);
$table = $section->addTable(['borderSize' => 1, 'borderColor' => '000000']);
$table->addRow();
$table->addCell(5000)->addText('Cell 1');
$table->addCell(5000)->addText('Cell 2');

$htmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
$htmlWriter->save('test_preview.html');
echo "Saved test_preview.html\n";
