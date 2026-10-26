<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
echo "User: " . $user->email . "\n";
echo "Has resume text? " . ($user->resume_text ? 'Yes' : 'No') . "\n";

$groq = new \App\Services\GroqAiService();
$prompt = "You are an expert AI resume parser. I will provide raw resume text. You MUST extract and structure the data into valid JSON. Do NOT summarize or shorten descriptions; extract bullet points exactly as written. Extract ALL projects and certifications.\n"
                . "Use exactly this JSON schema:\n"
                . "{\n"
                . "  \"name\": \"Full Name\",\n"
                . "  \"contact\": \"Email | Phone | Location | Links\",\n"
                . "  \"summary\": \"Full professional summary paragraph\",\n"
                . "  \"skills\": [\"skill1\", \"skill2\"],\n"
                . "  \"experience\": [{\"title\": \"\", \"company\": \"\", \"date\": \"\", \"description\": \"Extract all bullet points word-for-word here. Use newlines for each bullet.\" }],\n"
                . "  \"projects\": [{\"title\": \"\", \"date\": \"\", \"description\": \"Extract all details here.\" }],\n"
                . "  \"education\": [{\"degree\": \"\", \"institution\": \"\", \"date\": \"\"}],\n"
                . "  \"certifications\": [\"cert1\", \"cert2\"]\n"
                . "}\n"
                . "Respond ONLY with the raw JSON array. Do not include markdown formatting.\n\n"
                . "Resume Text:\n" . ($user->resume_text ?? '');

echo "Calling Groq API...\n";
$jsonStr = $groq->generate($prompt);

echo "Raw Response:\n";
var_dump($jsonStr);

if ($jsonStr) {
    $jsonStr = trim($jsonStr);
    if (preg_match('/\{[\s\S]*\}/', $jsonStr, $matches)) {
        $jsonStr = $matches[0];
    }
    
    echo "Extracted JSON:\n";
    var_dump($jsonStr);
    
    $decoded = json_decode($jsonStr, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Successfully decoded JSON!\n";
        
        $user->update([
            'resume_json' => $jsonStr
        ]);
        echo "Saved to database.\n";
    } else {
        echo "JSON Decode Error: " . json_last_error_msg() . "\n";
    }
}
