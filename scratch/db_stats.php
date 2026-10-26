<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$counts = [
    'users' => \App\Models\User::count(),
    'company_profiles' => \App\Models\CompanyProfile::count(),
    'job_postings' => \App\Models\JobPosting::count(),
    'job_inquiries' => \App\Models\JobInquiry::count(),
    'interview_preparations' => \App\Models\InterviewPreparation::count(),
    'tailored_resumes' => \App\Models\TailoredResume::count(),
    'connected_accounts' => \App\Models\ConnectedAccount::count(),
    'campaigns' => \App\Models\Campaign::count(),
    'leads' => \App\Models\Lead::count(),
    'email_drafts' => \App\Models\EmailDraft::count(),
    'social_posts' => \App\Models\SocialPost::count(),
];

echo json_encode($counts, JSON_PRETTY_PRINT);
