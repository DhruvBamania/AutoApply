<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = app(\App\Services\InterviewPrepService::class);
echo "Calling generatePrep...\n";
$res = $svc->generatePrep('Senior Laravel Developer', '3-5 years (Mid-Level)', 'PHP, Laravel, MySQL, Redis, Docker');
echo "Result summary: " . ($res['summary'] ?? '') . "\n";
echo "Total questions: " . ($res['total_questions'] ?? 0) . "\n";
echo "Topics count: " . count($res['topics'] ?? []) . "\n";
foreach ($res['topics'] ?? [] as $t) {
    echo " - Topic: " . $t['topic_name'] . " (" . count($t['questions']) . " Qs)\n";
    if (!empty($t['questions'][0])) {
        echo "   e.g.: " . $t['questions'][0]['question'] . "\n";
    }
}
